<?php

namespace Provisioning\Http\Controllers;

use Illuminate\Http\Request;
use Provisioning\ComptaPrestation as Prestation;
use CentileENT;

class CentileServicesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Prestation $prestation)
    {
        $context = $prestation->getCentileContext();
        $voicemail = CentileENT::getVoicemail($context);
        $conferenceBridge = CentileENT::getConferenceBridge($context);
        $callQueuing = CentileENT::getCallQueuing($context);
        $extensionsGroups = CentileENT::getExtensionGroups($context);
        $speedDials = CentileENT::getSpeedDials($context);
        return response()->json(['data' => [
            'voicemail' => $voicemail,
            'conferenceBridge' => $conferenceBridge,
            'extensionsGroups' => $extensionsGroups,
            'speedDials' => $speedDials,
            'callQueuing' => $callQueuing,
        ]]);
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
    public function show($id)
    {
        //
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
