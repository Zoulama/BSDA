<?php

namespace Provisioning\Http\Controllers;

use Illuminate\Http\Request;
use Provisioning\ComptaPrestation as Prestation;
use Provisioning\Centile\Device;
use Provisioning\Centile\Terminal;
use Provisioning\Http\Requests\CentileTerminalUpdate;
use CentileENT;

class CentileTerminalController extends Controller
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Prestation $prestation, Device $device, Terminal $terminal)
    {
        $context = $prestation->getCentileContext();
        $terminal = $terminal->withLines($context);

        return response()->json(['data' => $terminal]);
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
    public function update(CentileTerminalUpdate $request, Prestation $prestation, Device $device, Terminal $terminal)
    {
        $context = $prestation->getCentileContext();

        $params = [];
        if ($request->exists('extension'))
            $params['extension'] = $request->input('extension') ? $request->input('extension') : null;

        if ($request->exists('label'))
            $params['label'] = $request->input('label') ? $request->input('label') : null;

        $terminal = CentileENT::updateTerminal(
            $context,
            $device->physicalID,
            $terminal->devicePort,
            $params
        );

        return response()->json(['data' => $terminal]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Prestation $prestation, Device $device, Terminal $terminal)
    {
        CentileENT::deleteTerminal($prestation->getCentileContext(), $device->physicalID, $terminal->devicePort);

        return response(null, 204);
    }
}
