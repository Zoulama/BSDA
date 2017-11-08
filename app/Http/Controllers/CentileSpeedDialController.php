<?php

namespace Provisioning\Http\Controllers;

use Illuminate\Http\Request;
use Provisioning\ComptaPrestation as Prestation;
use Provisioning\CentilePrestationTypes;
use Provisioning\Centile\SpeedDial;
use Provisioning\Http\Requests\CentileSpeedDialUpdate;
use Provisioning\Http\Requests\CentileSpeedDialStore;
use DB;
use CentileENT;

class CentileSpeedDialController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Prestation $prestation)
    {
        $speedDials = CentileENT::getSpeedDials($prestation->getCentileContext());

        sort($speedDials);
        return response()->json(['data' => $speedDials]);
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
    public function store(CentileSpeedDialStore $request, Prestation $prestation)
    {
        $context = $prestation->getCentileContext();
        $speedDial = null;

        DB::transaction(function () use ($prestation, $request, &$speedDial, $context) {
            $prestation = Prestation::centileFactory(
                CentilePrestationTypes::CENTREX_SPEED_DIAL,
                date('Y-m-d'),
                $prestation->getClientId(),
                $prestation->getGroupId(),
                $prestation->getCentileResellerContext(),
                $context,
                $request->input('extension'),
                null,
                Prestation::STATUS_COMPLETION,
                $prestation->getId()
            );

            $speedDial = CentileENT::createSpeedDial(
                $context,
                $request->input('extension'),
                $request->input('pstn'),
                $request->has('label') ? $request->input('label') : null
            );
        });

        return response()->json(['data' => $speedDial], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Prestation $prestation, SpeedDial $speedDial)
    {
        return response()->json(['data' => $speedDial]);
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
    public function update(CentileSpeedDialUpdate $request, Prestation $prestation, SpeedDial $speedDial)
    {
        if ($request->exists('label'))
            $label = $request->has('label') ? $request->input('label') : null;

        if ($request->has('extension'))
            $speedDialPrestation = $speedDial->findPrestation($prestation);

        $speedDial = CentileENT::updateSpeedDial(
            $prestation->getCentileContext(),
            $speedDial->extension,
            $request->has('extension') ? $request->input('extension') : $speedDial->extension,
            $request->has('pstn') ? $request->input('pstn') : $speedDial->externalDestination,
            $request->exists('label') ? $label : $speedDial->label
        );

        if ($request->has('extension')) {
            $speedDialPrestation->valeur = $request->input('extension');
            $speedDialPrestation->save();
        }

        return response()->json(['data' => $speedDial]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Prestation $prestation, SpeedDial $speedDial)
    {
        CentileENT::deleteSpeedDial($prestation->getCentileContext(), $speedDial->extension);

        $speedDial->terminatePrestation($prestation);

        return response(null, 204);
    }
}
