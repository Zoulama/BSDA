<?php

namespace Provisioning\Http\Controllers;

use Provisioning\ComptaPrestation as Prestation;
use Provisioning\Centile\LogicalTerminal;
use Provisioning\Http\Requests\CentileLogicalTerminalStore;
use Provisioning\Http\Requests\CentileLogicalTerminalUpdate;
use CentileENT;

class CentileLogicalTerminalController extends Controller
{
    const USER_HOME = 'Home number';
    const USER_MOBILE = 'Mobile number';
    const EXT_PNS = 'EXT_PNS';
    const EXT_PSTNS = 'Assigned PSTN number';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Prestation $prestation)
    {
        $logicalTerminals = CentileENT::getLogicalTerminals($prestation->getCentileContext());

        return response()->json(['data' => $logicalTerminals]);
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
     * @return \Illuminate\Http\Response
     */
    public function store(CentileLogicalTerminalStore $request, Prestation $prestation)
    {
        $context = $prestation->getCentileContext();

        $logicalTerminal = CentileENT::createLogicalTerminal(
            $context,
            config('centile.default_gateway'),
            $request->input('logicalIDs'),
            $request->input('extension'),
            $request->input('extension') . '-' . $request->input('logicalIDs')
        );

        $logicalTerminal->newPrestation($prestation);

        return response()->json(['data' => $logicalTerminal], 201);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Prestation $prestation, LogicalTerminal $logicalTerminal)
    {
        return response()->json(['data' => $logicalTerminal]);
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
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(CentileLogicalTerminalUpdate $request, Prestation $prestation, LogicalTerminal $logicalTerminal)
    {
        $params = [];

        if ($request->exists('logicalIDs'))
            $params['logicalIDs'] = $request->has('logicalIDs') ? $request->input('logicalIDs') : null;

        $logicalTerminal = CentileENT::updateLogicalTerminal(
            $prestation->getCentileContext(),
            $logicalTerminal->physicalID,
            $params
        );

        return response()->json(['data' => $logicalTerminal]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Prestation $prestation, LogicalTerminal $logicalTerminal)
    {
        CentileENT::deleteLogicalTerminal(
            $prestation->getCentileContext(),
            $logicalTerminal->physicalID
        );

        return response(null, 204);
    }
}
