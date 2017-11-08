<?php

namespace Provisioning\Http\Controllers;

use Illuminate\Http\Request;
use Provisioning\ComptaPrestation as Prestation;
use Provisioning\Centile\Extension;
use Provisioning\Centile\DialPlan;
use Provisioning\Centile\UserExtension;
use CentileENT;

class CentileExtensionController extends Controller
{
    const MAX_SUGGESTIONS = 20;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function unassigned(Prestation $prestation)
    {
        $context = $prestation->getCentileContext();
        $enterprise = CentileENT::getEnterprise($context);

        $dialPlan = new DialPlan($enterprise->getDialPlan());
        $extensions = $dialPlan->filterAvailableExtensions();
        $unavailables = Extension::getAssignedExtensions($context);
        foreach ($unavailables as $extension) {
            if(($key = array_search($extension, $extensions)) !== false)
                array_splice($extensions, $key, 1);
        }

        sort($extensions);
        //convert all values to string
        $extensions = array_map(function($item) {
            return (string) $item;
        }, $extensions);

        return response()->json(['data' => Extension::mapLinkedObjectsToExtensions($context, $extensions, false, false)]);
    }

    public function assigned(Prestation $prestation)
    {
        $context = $prestation->getCentileContext();
        $extensions = Extension::getAssignedExtensions($context);
        sort($extensions);
        return response()->json(['data' => Extension::mapLinkedObjectsToExtensions($context, $extensions, true, true)]);
    }

    public function notAssignedToUser(Prestation $prestation)
    {
        $all = Extension::all($prestation->getCentileContext());
        $assignedToUser = UserExtension::getAssignedExtensions($prestation->getCentileContext());

        $extensions = [];
        foreach ($all as $ext) {
            if (!in_array($ext, $assignedToUser))
                $extensions[] = (string) $ext;
        }

        return response()->json(['data' => Extension::mapLinkedObjectsToExtensions($context, $extensions, false, true)]);
    }

    public function monitoring(Prestation $prestation)
    {
        $context = $prestation->getCentileContext();
        $users = CentileENT::getUsers($context);
        $extGroupsExtensions = CentileENT::getExtensionGroups($context);
        $ret = [];
        foreach ($users as $user)
            $ret[] = ['extension' => $user->extension, 'linked' => ['type' => 'USER', 'label' => $user->firstName . ' ' . $user->lastName]];
        foreach ($extGroupsExtensions as $groupExt)
            $ret[] = ['extension' => $groupExt->extension, 'linked' => ['type' => 'EXTENSIONS_GROUP', 'label' => $groupExt->label]];

        return response()->json(['data' => $ret]);
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
