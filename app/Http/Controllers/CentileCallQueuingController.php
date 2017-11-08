<?php

namespace Provisioning\Http\Controllers;

use Illuminate\Http\Request;
use Provisioning\ComptaPrestation as Prestation;
use Provisioning\Centile\Service;
use Provisioning\Helpers\LibClient;
use Provisioning\CentilePrestationTypes;
use Provisioning\Http\Requests\CentileCallQueuingStore;
use DB;
use CentileENT;

class CentileCallQueuingController extends Controller
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Prestation $prestation)
    {
        $callQueuing = null;

        DB::transaction(function () use ($prestation, $request, &$callQueuing) {
            $context = $prestation->getCentileContext();

            //create conference prestation in provisioning DB
            Prestation::centileFactory(
                CentilePrestationTypes::CENTREX_CALLQUEUING,
                date('Y-m-d'),
                $prestation->getClientId(),
                $prestation->getGroupId(),
                $prestation->getCentileResellerContext(),
                $context,
                Service::ISTRA_SERVICE_CALL_QUEUING_NAME,
                null,
                Prestation::STATUS_COMPLETION,
                $prestation->getId()
            );

            //create conference prestation in Centile
            $callQueuing = CentileENT::createService(
                $context,
                Service::ISTRA_SERVICE_CALL_QUEUING_LABEL,
                Service::ISTRA_SERVICE_CALL_QUEUING_NAME
            );
        });

        return response()->json(['data' => $callQueuing], 201);
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
    public function update(Request $request, Prestation $prestation)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Prestation $prestation)
    {
        $context = $prestation->getCentileContext();

        if (!$callQueuing = CentileENT::getCallQueuing($context))
            return response(null, 204);

        if (!CentileENT::deleteService($context, Service::ISTRA_SERVICE_CALL_QUEUING_LABEL))
            return response(null, 500);

        $callQueuing->terminatePrestation($prestation);

        return response(null, 204);
    }
}
