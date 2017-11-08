<?php

namespace Provisioning\Http\Controllers;

use Illuminate\Http\Request;
use Provisioning\Http\Requests\CentileTrunkStore;
use Provisioning\Http\Requests\CentileTrunkUpdate;
use Provisioning\Client;
use Provisioning\Centile\PBXTrunking;
use Provisioning\ComptaPrestation as Prestation;
use Provisioning\CentilePrestationTypes;
use CentileTRK;
use CentileENT;
use Provisioning\Centile\Centile;
use Provisioning\Firewall;
use Provisioning\Centile\PSTNRange;
use DB;

class CentileTrunkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CentileTrunkStore $request, Client $client)
    {
        $trunk = null;
        DB::transaction(function () use (&$trunk, $request, $client) {
            $prestation = Prestation::centileFactory(
                CentilePrestationTypes::TRUNK,
                date('Y-m-d'),
                $client->getId(),
                $client->getGroupId(),
                $serviceProviderContext = PBXTrunking::getDefaultResellerContext($client->getResellerId()),
                null,
                $request->input('defaultPstn'),
                $request->input('label'),
                Prestation::STATUS_COMPLETION
            );

            $name = PBXTrunking::generateTrunkName($client->getId(), $prestation->getId());
            $label = $request->input('label') ?: $name;

            $params = [
                'label' => $label,
                'maxCalls' => $request->input('maxChannels'),
                'location' => Centile::INSEE_PREFIX . $request->input('areaCode'),
                'name' => $name,
                'authPassword' => PBXTrunking::generatePassword(),
                'authUsername' => PBXTrunking::generateUsername($client->getId(), $prestation->getId()),
                'defaultPstn' => $request->input('defaultPstn'),
                'callPolicy' => '1',
                'countryCode' => '33',
                'operatorPrefix' => '0',
                'rtpRedirection' => 'false',
                'requestAuth' => 'false',
                'language' => 'fr',
                'checkPolicyOnAssertedCallerID' => $request->input('defaultPstn') ? '1' : '0',
                'registration' => 'true',
            ];

            $trunk = CentileTRK::createTrunk($serviceProviderContext, $params);

            if ($request->input('unreachable'))
                CentileTRK::createForwarding($serviceProviderContext, $name, 'UR', $request->input('unreachable'));

            $pstns = $request->input('PstnNumbers') ?: [];

            if (count($pstns)) {
                $pstnsToAssign = [];
                foreach ($pstns as $pstn) {
                    if (CentileENT::getPstnNumber($pstn))
                        $pstnsToAssign[] = $pstn;
                }

                $pstnsToCreate = array_diff($pstns, $pstnsToAssign);

                $ranges = PSTNRange::createRanges($pstnsToCreate);
                foreach ($ranges as $range)
                    $pstnRanges[] = CentileTRK::createPstnRange(
                        $range['start'],
                        $range['end'],
                        $client->getId(),
                        $prestation->getCentileResellerContext(),
                        $prestation->getCentileContext()
                    );

                foreach ($pstnsToAssign as $pstnToAssign)
                    CentileTRK::assignPstnToTrunk(
                        $prestation->getCentileResellerContext(),
                        $pstnToAssign,
                        $prestation->getCentileContext()
                    );

                foreach ($pstns as $pstn) {
                    Prestation::centileFactory(
                        CentilePrestationTypes::TRUNK_PSTN,
                        date('Y-m-d'),
                        $client->getId(),
                        $client->getGroupId(),
                        $serviceProviderContext,
                        $name,
                        $pstn,
                        'PSTN for ' . $name,
                        Prestation::STATUS_COMPLETION,
                        $prestation->getId()
                    );
                }

                if ($request->input('defaultPstn')) {
                    CentileTRK::setDefaultPstn(
                        $prestation->getCentileResellerContext(),
                        $prestation->getCentileContext(),
                        $request->input('defaultPstn')
                    );
                }
            }

            if ($request->input('callBarrings')) {
                //TODO: create call barrings
            }

            // push allowed ip address to be inserted in firewalls
            if ($request->has('allowedPublicIPAddress'))
                Firewall::registerTrunkIpAddress($prestation->getCentileContext(), $request->input('allowedPublicIPAddress'));

            $trunk = $trunk->withPrestationId($prestation->getId())
                ->changeLocationAttribute();
        });

        return response()->json(['data' => $trunk], 201);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Prestation $prestation)
    {
        $context = $prestation->getCentileContext();
        $resellerContext = $prestation->getCentileResellerContext();
        if ($trunk = CentileTRK::getTrunk($resellerContext, $context))
            $trunk = $trunk->changeLocationAttribute()->withForwardings($resellerContext)->withFirewall($context);
        return response()->json(['data' => $trunk]);
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
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CentileTrunkUpdate $request, Prestation $prestation)
    {
        $context = $prestation->getCentileResellerContext();
        $trunkName = $prestation->getCentileContext();

        if ($request->exists('defaultPstn')) {
            CentileTRK::setDefaultPstn($context, $trunkName, $request->input('defaultPstn'));
            $prestation->valeur = $request->input('defaultPstn');
            $prestation->save();
        }

        if ($request->exists('callBarrings')) {
            foreach ($request->input('callBarrings') as $callBarring)
                CentileTRK::assignCallBarringToTrunk($context, $callBarring, $trunkName);
        }

        if ($request->exists('unreachable')) {
            CentileTRK::deleteAllForwardings($context, $trunkName);
            if ($request->input('unreachable'))
                CentileTRK::createForwarding($context, $trunkName, 'UR', $request->input('unreachable'));
        }

        if ($request->exists('maxChannels')) {
            //workaround with DB because Centile broken API
            $this->updateMaxTrunkCalls($trunkName, $request->input('maxChannels'));
            // $params['maxCalls'] = $request->input('maxChannels');
        }

        $params = [];
        if ($request->exists('areaCode'))
            $params['location'] = Centile::INSEE_PREFIX . $request->input('areaCode');
        if ($request->exists('label')) {
            $prestation->description = $request->input('label');
            $prestation->save();
            $params['label'] = $request->input('label') ?: null;
        }

        if (count($params))
            $trunk = CentileTRK::updateTrunk($context, $trunkName, $params);

        if (!isset($trunk))
            $trunk = CentileTRK::getTrunk($context, $trunkName);

        return response()->json(['data' => $trunk]);
    }

    protected function updateMaxTrunkCalls($trunk, $maxCalls)
    {
        DB::connection('istra')->table('PBXTRUNKING')->where('NAME', $trunk)->update(['MAXCALLS' => $maxCalls]);
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
                $context = $prestation->getCentileContext();
                $resellerContext = $prestation->getCentileResellerContext();
                CentileTRK::deleteTrunk($resellerContext, $context);

                foreach ($prestation->dependentPrestations as $depPrestation)
                    $depPrestation->terminate();

                $prestation->terminate();
            });
        }

        return response(null, 204);
    }
}
