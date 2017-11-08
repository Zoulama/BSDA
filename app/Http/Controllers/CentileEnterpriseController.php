<?php

namespace Provisioning\Http\Controllers;

use Provisioning\Centile\Centile;
use Provisioning\Centile\Enterprise;
use Provisioning\Centile\Site;
use Provisioning\CentilePrestationTypes;
use Provisioning\ComptaPrestation as Prestation;
use Provisioning\Http\Requests\CentileEnterpriseStore;
use Provisioning\Http\Requests\CentileEnterpriseUpdate;
use Provisioning\Client;
use Provisioning\Firewall;
use Provisioning\Centile\PSTNRange;
use DB;
use CentileENT;

class CentileEnterpriseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     *
     */
    public function store(CentileEnterpriseStore $request, Client $client)
    {
        $enterprise = null;
        $pstns = $request->input('PSTNNumbers');
        $ranges = PSTNRange::createRanges($pstns);
        $defaultPSTNNumber = $request->input('defaultPSTNNumber') ? $request->input('defaultPSTNNumber') : null;

        DB::transaction(function () use ($client, $request, &$enterprise, $pstns, $ranges, $defaultPSTNNumber) {

            //create centrex prestation in provisioning DB
            $prestation = Prestation::centileFactory(
                CentilePrestationTypes::CENTREX,
                date('Y-m-d'),
                $client->getId(),
                $client->getGroupId(),
                $serviceProviderContext = Enterprise::getDefaultResellerContext($client->getResellerId()),
                null,
                $defaultPSTNNumber,
                $request->input('label'),
                Prestation::STATUS_COMPLETION
            );

            $clientAdministrativeContext = $prestation->getCentileContext();

            // create centrex in Centile
            $enterprise = CentileENT::createEnterprise(
                $serviceProviderContext,
                $clientAdministrativeContext,
                $request->input('label'),
                $prestation->getId(),
                $client->getId(),
                $request->input('maxChannels'),
                $request->input('dialplan')
            );

            //create PSTN ranges in Centile
            foreach ($ranges as $range)
                $pstnRanges[] = CentileENT::createPstnRange($range['start'], $range['end'], $client->getId(), $clientAdministrativeContext);

            //create PSTNs prestation in provisioning DB
            foreach ($pstns as $pstn) {
                Prestation::centileFactory(
                    CentilePrestationTypes::CENTREX_PSTN,
                    date('Y-m-d'),
                    $client->getId(),
                    $client->getGroupId(),
                    $serviceProviderContext,
                    $clientAdministrativeContext,
                    $pstn,
                    'PSTN for ' . $clientAdministrativeContext,
                    Prestation::STATUS_COMPLETION,
                    $prestation->getId()
                );
            }

            // as we can't set the default PSTN (pilotNumber) on enterprise creation, we have to update it afterwards
            $enterprise = CentileENT::updateEnterprise(
                $clientAdministrativeContext,
                [
                    'pilotNumber' => $defaultPSTNNumber,
                ]
            );

            // create default site for enterprise
            CentileENT::createSite(
              $clientAdministrativeContext,
              Site::DEFAULT_NAME,
              Site::AREA_CODE_PREFIX.$request->input('areaCode')
            );

            // create dial prefix (with prefix or set to null if none)
            if ($request->has('outsideLinePrefixDigit')) {
                CentileENT::createDialPrefix(
                    $clientAdministrativeContext,
                    Centile::DEFAULT_DIAL_PREFIX_NAME,
                    $request->input('outsideLinePrefixDigit'),
                    config('centile.default_gateway')
                );
            } else {
                CentileENT::createDialPrefix(
                    $clientAdministrativeContext,
                    Centile::DEFAULT_DIAL_PREFIX_NAME,
                    '',
                    config('centile.default_gateway')
                );
            }

            // restrict enterprise if needed
            if ($request->exists('restriction'))
                CentileENT::restrictEnterprise($clientAdministrativeContext, $request->input('restriction'));

            // push allowed ip address to be inserted in firewalls
            if ($request->has('allowedPublicIPAddress'))
                Firewall::registerCentrexIpAddress($clientAdministrativeContext, $request->input('allowedPublicIPAddress'));

            $enterprise = $enterprise->linkToPrestationId($prestation->getId());
        });

        return response()->json(['data' => $enterprise], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Prestation $prestation)
    {
        if ($prestation->isActive()) {
            $context = $prestation->getCentileContext();
            $enterprise = CentileENT::getEnterprise($context);

            if ($enterprise)
                $enterprise = $enterprise
                    ->withUsedDialPlanMasks()
                    ->withRestriction()
                    ->withDefaultSite()
                    ->withFirewall($context);

            return response()->json([
                'data' => $enterprise
            ]);
        } else
            abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(CentileEnterpriseUpdate $request, Prestation $prestation)
    {
        $context = $prestation->getCentileResellerContext();
        $clientAdministrativeContext = $prestation->getCentileContext();

        $params = [];
        if ($request->has('label'))
            $params['fullName'] = $request->input('label');

        if ($maxChannels = $request->input('maxChannels')) {
            $params['maxExternalConnections'] = $request->input('maxChannels');
            $params['maxIVRConnections'] = $request->input('maxChannels') * 2;
        }

        if ($dialplan = $request->input('dialplan'))
            $params['internalDialplan'] = $dialplan;

        if ($request->exists('defaultPSTNNumber')) {
            if (empty($request->input('defaultPSTNNumber')))
                $params['pilotNumber'] = null;
            else
                $params['pilotNumber'] = toE164($request->input('defaultPSTNNumber'));
        }

        if ($params) {
            $enterprise = CentileENT::updateEnterprise(
                $clientAdministrativeContext,
                $params
            );
        }

        if (array_key_exists('pilotNumber', $params)) {
            $prestation->valeur = $params['pilotNumber'];
            $prestation->save();
        }

        if ($request->has('label')) {
            $prestation->description = $request->input('label');
            $prestation->save();
        }

        if ($request->exists('areaCode')) {
            if ($request->input('areaCode'))
                $areaCode = Site::AREA_CODE_PREFIX . $request->input('areaCode');
            else
                $areaCode = null;
            CentileENT::updateSite(
                $clientAdministrativeContext,
                Site::DEFAULT_NAME,
                ['location' => $areaCode]
            );
        }

        if ($request->exists('restriction'))
            CentileENT::restrictEnterprise($clientAdministrativeContext, $request->input('restriction'));

        if (empty($enterprise))
            $enterprise = CentileENT::getEnterprise($clientAdministrativeContext);

        return response()->json([
            'data' => $enterprise
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Prestation $prestation)
    {
        if ($prestation->isActive()) {
            DB::transaction(function () use ($prestation) {
                $name = $prestation->getCentileContext();

                CentileENT::deleteEnterprise($name);

                $prestation->terminate();
            });
        }

        return response(null, 204);
    }
}
