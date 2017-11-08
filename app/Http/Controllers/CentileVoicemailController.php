<?php

namespace Provisioning\Http\Controllers;

use Illuminate\Http\Request;
use Provisioning\ComptaPrestation as Prestation;
use Provisioning\Centile\IVRService;
use Provisioning\Centile\DialPlan;
use Provisioning\Centile\Extension;
use Provisioning\Helpers\LibClient;
use Provisioning\CentilePrestationTypes;
use Provisioning\Http\Requests\CentileVoicemailStore;
use Provisioning\Http\Requests\CentileVoicemailUpdate;
use DB;
use CentileENT;

class CentileVoicemailController extends Controller
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
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(CentileVoicemailStore $request, Prestation $prestation)
    {
        $context = $prestation->getCentileContext();
        $voicemail = null;

        DB::transaction(function () use ($prestation, $request, &$voicemail, $context) {

            //create voicemail prestation in provisioning DB
            $prestation = Prestation::centileFactory(
                CentilePrestationTypes::CENTREX_VOICEMAIL,
                (new \Datetime())->format('Y-m-d'),
                $prestation->getClientId(),
                $prestation->getGroupId(),
                $prestation->getCentileResellerContext(),
                $context,
                $request->input('extension'),
                null,
                Prestation::STATUS_COMPLETION,
                $prestation->getId()
            );

            //create voicemail prestation in Centile
            $voicemail = CentileENT::createIVRService(
                $context,
                $request->input('extension'),
                IVRService::getDefaultVoicemailLabel(),
                IVRService::ISTRA_IVR_VOICEMAIL_LABEL
            );
        });

        return response()->json(['data' => $voicemail], 201);
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
    public function update(CentileVoicemailUpdate $request, Prestation $prestation)
    {
        if ($request->has('extension') && $request->input('extension')) {
            $context = $prestation->getCentileContext();

            if ($voicemail = CentileENT::getVoicemail($context))
                $voicemail = CentileENT::updateIVRService($context, $voicemail->extension, $request->input('extension'));
            else
                $voicemail = CentileENT::createIVRService(
                    $context,
                    $request->input('extension'),
                    IVRService::getDefaultVoicemailLabel(),
                    IVRService::ISTRA_IVR_VOICEMAIL_LABEL
                );

            if ($voicemailPrestation = $voicemail->findPrestation($prestation)) {
                $voicemailPrestation->valeur = $request->input('extension');
                $voicemailPrestation->save();
            }

            return response()->json(['data' => $voicemail]);
        }
        else
            $this->destroy($prestation);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Prestation $prestation)
    {
        $parentContext = $prestation->getCentileContext();

        if (!$voicemail = CentileENT::getVoicemail($parentContext))
            return response(null, 204);

        if (!CentileENT::deleteIVRService($parentContext, $voicemail->extension))
            return response(null,500);

        $voicemail->terminatePrestation($prestation);

        return response(null, 204);
    }
}
