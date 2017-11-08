<?php

namespace Provisioning\Http\Controllers;

use Illuminate\Http\Request;
use Provisioning\ComptaPrestation as Prestation;
use Provisioning\Centile\Forwarding;
use Provisioning\Centile\UserExtension;
use Provisioning\Centile\ExtensionsGroup;
use Provisioning\Centile\SpeedDial;
use Provisioning\Http\Requests\CentileForwardingStore;
use Provisioning\Http\Requests\CentileForwardingUpdate;
use DB;
use CentileENT;

class CentileForwardingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Prestation $prestation)
    {
        $context = $prestation->getCentileContext();

        $forwardings = CentileENT::getForwardings($context);
        return response()->json(['data' => $forwardings]);
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
    public function store(CentileForwardingStore $request, Prestation $prestation)
    {
        $context = $prestation->getCentileContext();

        $forwarding = CentileENT::createForwarding(
            $context,
            $request->input('assignedTo'),
            $request->input('type'),
            $request->input('destination'),
            $request->input('label')
        );

        return response()->json(['data' => $forwarding]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Prestation $prestation, Forwarding $forwarding)
    {
        return response()->json(['data' => $forwarding]);
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
    public function update(CentileForwardingUpdate $request, Prestation $prestation, Forwarding $forwarding)
    {
        $context = $prestation->getCentileContext();

        if ($request->exists('activated'))
            $params['activated'] = $request->input('activated');

        if ($request->exists('type'))
            $params['type'] = $request->input('type');

        if ($request->exists('destination')) {
            $destination = $request->input('destination');
            if (in_array($destination, ['USER_MOBILE', 'USER_HOME', 'ENT_VM', 'ENT_RCPT', 'REJECTION']))
            {
              $params['labelledDestination'] = $destination;
              $params['externalDestination'] = null;
              $params['internalDestination'] = null;
            }
            elseif (isE164format($destination))
                $params['externalDestination'] = $destination;
            elseif (UserExtension::exists($context, $destination))
                $params['internalDestination'] = $destination;
            elseif (ExtensionsGroup::exists($context, $destination))
                $params['internalDestination'] = $destination;
            elseif (SpeedDial::exists($context, $destination))
                $params['internalDestination'] = $destination;
            else
                throw new \Exception('Unable to determine forwarding destination');
        }

        if ($request->exists('label'))
            $params['label'] = $request->has('label') ? $request->input('label') : null;

        if ($params) {
            $params['timeFilter'] = "";
            $forwarding = CentileENT::updateForwarding(
                $context,
                $forwarding->forwardingID,
                $params
            );
        }

        return response()->json(['data' => $forwarding]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Prestation $prestation, Forwarding $forwarding)
    {
        CentileENT::deleteForwarding($prestation->getCentileContext(), $forwarding->forwardingID);

        return response(null, 204);
    }
}
