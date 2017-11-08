<?php

namespace Provisioning\Http\Controllers;

use Illuminate\Http\Request;
use Provisioning\Http\Requests\CentilePstnNumberStore;
use Provisioning\Http\Requests\CentilePstnNumberUpdate;
use Provisioning\Centile\PSTNRange;
use Provisioning\Centile\PSTNNumber;
use Provisioning\Centile\Extension;
use Provisioning\ComptaPrestation as Prestation;
use Provisioning\CentilePrestationTypes;
use DB;
use CentileENT;

class CentilePSTNNumberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Prestation $prestation)
    {
        $context = $prestation->getCentileContext();
        $pstns = CentileENT::getPstnNumbers($context);
        $enterprise = CentileENT::getEnterprise($context);

        $pstnsToMap = [];
        foreach ($pstns as &$pstn) {
            $pstn = $pstn->withPilotNumber($enterprise);
            if ($pstn->numberExtension)
                $pstnsToMap[] = $pstn->numberExtension;
        }

        $extensionDetails = Extension::mapLinkedObjectsToExtensions($context, $pstnsToMap, true, true);

        foreach ($pstns as &$pstn) {
            if ($pstn->numberExtension) {
                $pstn->extension = null;
                foreach ($extensionDetails as $extension) {
                    if ($pstn->numberExtension == $extension['extension']) {
                        $pstn->extension = $extension;
                        break;
                    }
                }
            }
        }

        return response()->json(['data' => $pstns]);
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
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(CentilePstnNumberStore $request, Prestation $prestation)
    {
        $toAssign = [];
        $toCreate = [];
        $pstns = array_map('toE164', $request->input('PSTNNumbers'));

        foreach ($pstns as $number) {
            if (CentileENT::getPstnNumber($number))
                $toAssign[] = $number;
            else
                $toCreate[] = $number;
        }

        DB::transaction(function () use ($prestation, $toAssign, $toCreate) {
            if (count($toCreate)) {
                $ranges = PSTNRange::createRanges($toCreate);
                foreach ($ranges as $range) {
                    $pstnRanges[] = CentileENT::createPstnRange(
                        $range['start'],
                        $range['end'],
                        $prestation->getClientId(),
                        $prestation->getCentileContext()
                    );
                }
            }

            foreach (array_merge($toCreate, $toAssign) as $number) {
                $pstn = new PSTNNumber(['number' => $number]);
                $pstn->newPrestation($prestation, CentilePrestationTypes::CENTREX_PSTN);
                CentileENT::assignPstnToAdmtiveDomain($number, $prestation->getCentileContext());
            }
        });

        return response()->json(['data' => $pstns], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Prestation $prestation, PSTNNumber $pstn)
    {
        return response()->json(['data' => $pstn]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(CentilePstnNumberUpdate $request, Prestation $prestation, PSTNNumber $pstn)
    {
        if ($request->exists('extension')) {
            $pstn = CentileENT::updatePstnNumber(
                $prestation->getCentileContext(),
                $pstn->number,
                ['numberExtension' => $request->input('extension') ?: null]
            );
        }

        return response()->json(['data' => $pstn]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Prestation $prestation, PSTNNumber $pstn)
    {
        CentileENT::assignPstnToAdmtiveDomain($pstn->number, 'Top-Level');
        $pstn->terminatePrestation($prestation);

        return response(null, 204);
    }
}
