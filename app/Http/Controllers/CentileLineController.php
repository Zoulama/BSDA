<?php

namespace Provisioning\Http\Controllers;

use Illuminate\Http\Request;
use Provisioning\ComptaPrestation as Prestation;
use Provisioning\Centile\Line;
use Provisioning\Centile\Device;
use Provisioning\Http\Requests\CentileLineStore;
use Provisioning\Http\Requests\CentileLineUpdate;
use CentileENT;

class CentileLineController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Prestation $prestation, Device $device)
    {
        $lines = CentileENT::getLines($prestation->getCentileContext(), $device->physicalID);

        return response()->json(['data' => $lines]);
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
    public function store(CentileLineStore $request, Prestation $prestation, Device $device)
    {
        $context = $prestation->getCentileContext();

        if ($request->input('line') == 1 || $request->input('type') == Line::TYPE_LINE)
            $line = CentileENT::updateLineLine(
                $context,
                $device->physicalID,
                $request->input('line'),
                $request->input('label') ? $request->input('label') : null
            );
        elseif ($request->input('type') == Line::TYPE_MONITORING)
            $line = CentileENT::updateLineMonitoring(
                $context,
                $device->physicalID,
                $request->input('line'),
                $request->input('label') ? $request->input('label') : null,
                $request->input('linkedTo')
            );
        elseif ($request->input('type') == Line::TYPE_SPEED_DIAL)
            $line = CentileENT::updateLineSpeedDial(
                $context,
                $device->physicalID,
                $request->input('line'),
                $request->input('label') ? $request->input('label') : null,
                $request->input('linkedTo')
            );

        return response()->json(['data' => $line], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
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
    public function update(CentileLineUpdate $request, Prestation $prestation, Device $device, Line $line)
    {
        $context = $prestation->getCentileContext();
        $lineId = $line->lineNumber;

        // to change a button number
        if ($request->input('line') && $request->input('line') != 1 && $line->lineNumber != 1) {
            CentileENT::deleteLine($context, $device->physicalID, $line->lineNumber);
            $lineId = $request->input('line');
        }

        if ($lineId == 1 || $request->input('type') == Line::TYPE_LINE)
            $line = CentileENT::updateLineLine(
                $context,
                $device->physicalID,
                $lineId,
                $request->input('label') ? $request->input('label') : null
            );
        elseif ($request->input('type') == Line::TYPE_MONITORING)
            $line = CentileENT::updateLineMonitoring(
                $context,
                $device->physicalID,
                $lineId,
                $request->input('label') ? $request->input('label') : null,
                $request->input('linkedTo')
            );
        elseif ($request->input('type') == Line::TYPE_SPEED_DIAL)
            $line = CentileENT::updateLineSpeedDial(
                $context,
                $device->physicalID,
                $lineId,
                $request->input('label') ? $request->input('label') : null,
                $request->input('linkedTo')
            );

        return response()->json(['data' => $line]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Prestation $prestation, Device $device, Line $line)
    {
        $context = $prestation->getCentileContext();

        $ret = CentileENT::deleteLine(
            $context,
            $device->physicalID,
            $line->lineNumber
        );

        return response(null, 204);
    }
}
