<?php

namespace Provisioning\Http\Controllers;

use Illuminate\Http\Request;
use Provisioning\ComptaPrestation as Prestation;
use Provisioning\Centile\IVRService;
use Provisioning\Centile\DialPlan;
use Provisioning\Centile\Extension;
use Provisioning\Helpers\LibClient;
use Provisioning\CentilePrestationTypes;
use Provisioning\Http\Requests\CentileConferenceBridgeStore;
use Provisioning\Http\Requests\CentileConferenceBridgeUpdate;
use DB;
use CentileENT;

class CentileConferenceBridgeController extends Controller
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
    public function store(CentileConferenceBridgeStore $request, Prestation $prestation)
    {
        $conference = null;

        DB::transaction(function () use ($prestation, $request, &$conference) {
            $context = $prestation->getCentileContext();

            //create conference prestation in provisioning DB
            Prestation::centileFactory(
                CentilePrestationTypes::CENTREX_CONFERENCE,
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

            //create conference prestation in Centile
            $conference = CentileENT::createIVRService(
                $context,
                $request->input('extension'),
                IVRService::getDefaultConferenceLabel(),
                IVRService::ISTRA_IVR_CONFERENCE_LABEL
            );
        });

        return response()->json(['data' => $conference], 201);
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
    public function update(CentileConferenceBridgeUpdate $request, Prestation $prestation)
    {
        if ($request->has('extension') && $request->input('extension')) {
            $context = $prestation->getCentileContext();

            if ($conference = CentileENT::getConferenceBridge($context))
                $conference = CentileENT::updateIVRService($context, $conference->extension, $request->input('extension'));
            else
                $conference = CentileENT::createIVRService(
                    $context,
                    $request->input('extension'),
                    IVRService::getDefaultConferenceLabel(),
                    IVRService::ISTRA_IVR_CONFERENCE_LABEL
                );

            if ($conferencePrestation = $conference->findPrestation($prestation)) {
                $conferencePrestation->valeur = $request->input('extension');
                $conferencePrestation->save();
            }

            return response()->json(['data' => $conference]);
        }
        else
            $this->destroy($prestation);
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

        if (!$conference = CentileENT::getConferenceBridge($context))
            return response(null, 204);

        if (!CentileENT::deleteIVRService($context, $conference->extension))
            return response(null, 500);

        $conference->terminatePrestation($prestation);

        return response(null, 204);
    }
}
