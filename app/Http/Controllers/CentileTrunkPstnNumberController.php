<?php

namespace Provisioning\Http\Controllers;

use Illuminate\Http\Request;
use Provisioning\Http\Requests\CentileTrunkPstnNumberStore;
use Provisioning\Centile\PSTNRange;
use Provisioning\Centile\PSTNNumber;
use Provisioning\ComptaPrestation as Prestation;
use Provisioning\CentilePrestationTypes;
use DB;
use CentileTRK;

class CentileTrunkPstnNumberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Prestation $prestation)
    {
        $context = $prestation->getCentileResellerContext();
        $trunk = $prestation->getCentileContext();

        $pstns = [];
        foreach (CentileTRK::getPstnNumbers($context) as $pstn)
            if ($pstn->pbxTrunking == $trunk)
                $pstns[] = $pstn;

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
    public function store(CentileTrunkPstnNumberStore $request, Prestation $prestation)
    {
        $toAssign = [];
        $toCreate = [];

        $pstns = $request->input('PstnNumbers') ?: [];

        DB::transaction(function () use ($prestation, $toAssign, $toCreate, $pstns) {

            foreach ($pstns as $number) {
                if (CentileTRK::getPstnNumber($number))
                    $toAssign[] = $number;
                else
                    $toCreate[] = $number;

                $pstn = new PSTNNumber(['number' => $number]);
                $pstn->newPrestation($prestation, CentilePrestationTypes::TRUNK_PSTN);
            }

            if (count($toCreate)) {
                $ranges = PSTNRange::createRanges($toCreate);
                foreach ($ranges as $range) {
                    $pstnRanges[] = CentileTRK::createPstnRange(
                        $range['start'],
                        $range['end'],
                        $prestation->getClientId(),
                        $prestation->getCentileResellerContext(),
                        $prestation->getCentileContext()
                    );
                }
            }

            foreach ($toAssign as $number) {
                CentileTRK::assignPstnToTrunk(
                    $prestation->getCentileResellerContext(),
                    $number,
                    $prestation->getCentileContext()
                );
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
    public function update(Request $request, $id)
    {
        //
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
        CentileTRK::assignPstnToAdmtiveDomain($pstn->number, 'Top-Level');
        $pstn->terminatePrestation($prestation);

        return response(null, 204);
    }
}
