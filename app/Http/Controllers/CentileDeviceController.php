<?php

namespace Provisioning\Http\Controllers;

use Illuminate\Http\Request;
use Provisioning\ComptaPrestation as Prestation;
use Provisioning\Helpers\LibClient;
use Provisioning\Centile\Extension;
use Provisioning\Centile\Device;
use Provisioning\Centile\DeviceModel;
use Provisioning\Centile\Terminal;
use Provisioning\CentilePrestationTypes;
use Provisioning\Centile\Enterprise;
use Provisioning\Exceptions\UserExtensionNotFoundException;
use Provisioning\Http\Requests\CentileDeviceStore;
use Provisioning\Http\Requests\CentileDeviceUpdate;
use DB;
use CentileENT;

class CentileDeviceController extends Controller
{
    const DEFAULT_CODEC = 'G711';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Prestation $prestation)
    {
        $context = $prestation->getCentileContext();
        $devices = CentileENT::getDevices($context);
        foreach ($devices as &$device)
            $device = $device->withDeviceModel();

        return ['data' => $devices];
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
    public function store(CentileDeviceStore $request, Prestation $prestation)
    {
        $device = null;

        DB::transaction(function () use ($prestation, $request, &$device) {
            $context = $prestation->getCentileContext();

            //create device prestation in provisioning DB
            Prestation::centileFactory(
                CentilePrestationTypes::CENTREX_DEVICE,
                date('Y-m-d'),
                $prestation->getClientId(),
                $prestation->getGroupId(),
                $prestation->getCentileResellerContext(),
                $context,
                $request->input('physicalID'),
                DeviceModel::getLabelFromName($request->input('model')),
                Prestation::STATUS_COMPLETION,
                $prestation->getId()
            );

            $device = CentileENT::createDevice(
                $context,
                str_replace(':','',$request->input('physicalID')),
                $request->input('model'),
                $request->input('label'),
                $request->input('extension') ? $request->input('extension') : null,
                self::DEFAULT_CODEC,
                $request->input('secret') ? $request->input('secret') : null
            );
        });

        return response()->json(['data' => $device], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Prestation $prestation, Device $device)
    {
        $context = $prestation->getCentileContext();

        $device = $device->withDeviceModel()->withTerminals($context)->withLines($context)->withDeviceManufacturerStatus($context);

        $pstnsToMap = [];
        foreach ($device->terminals as $terminal) {
            if ($terminal->extension)
                $pstnsToMap[] = $terminal->extension;
        }

        foreach ($device->lines as $line) {
            if ($line->extension)
                $pstnsToMap[] = $line->extension;
        }

        $extensionDetails = Extension::mapLinkedObjectsToExtensions($context, $pstnsToMap, true, true);

        foreach ($device->terminals as &$terminal) {
            foreach ($extensionDetails as $extension) {
                if ($terminal->extension == $extension['extension']) {
                    $terminal->extension = $extension;
                    break;
                }
            }
        }

        foreach ($device->lines as &$line) {
            foreach ($extensionDetails as $extension) {
                if ($line->extension == $extension['extension'])
                    $line->extension = $extension;
            }
        }

        return ['data' => $device];
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
    public function update(CentileDeviceUpdate $request, Prestation $prestation, Device $device)
    {
        $context = $prestation->getCentileContext();
        $params = [];

        if ($request->input('physicalID'))
            $params['physicalID'] = str_replace(':', '', $request->input('physicalID'));

        if ($request->exists('label'))
            $params['label'] = $request->input('label') ? $request->input('label') : null;

        if ($request->exists('secret'))
            $params['secret'] = $request->input('secret') ? $request->input('secret') : null;

        if ($request->input('physicalID'))
            $devicePrestation = $device->findPrestation($prestation);

        if ($params) {
            $device = CentileENT::updateDevice(
                $context,
                $device->physicalID,
                $params
            );
        }

        if ($request->input('physicalID')) {
            $devicePrestation->valeur = $request->input('physicalID');
            $devicePrestation->save();
        }

        return response()->json(['data' => $device->withDeviceModel()->withTerminals($context)->withLines($context)]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Prestation $prestation, Device $device)
    {
        if (!CentileENT::deleteDevice($prestation->getCentileContext(), $device->physicalID))
            return response()->json(['error' => ['message' => 'Unable to delete device']]);

        $device->terminatePrestation($prestation);

        return response(null, 204);
    }
}
