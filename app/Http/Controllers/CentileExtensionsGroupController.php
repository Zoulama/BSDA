<?php

namespace Provisioning\Http\Controllers;

use Illuminate\Http\Request;
use Provisioning\ComptaPrestation as Prestation;
use Provisioning\Centile\Extension;
use Provisioning\Centile\ExtensionsGroup;
use Provisioning\CentilePrestationTypes;
use Provisioning\Http\Requests\CentileExtensionsGroupStore;
use Provisioning\Http\Requests\CentileExtensionsGroupUpdate;
use DB;
use CentileENT;

class CentileExtensionsGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Prestation $prestation)
    {
        $context = $prestation->getCentileContext();

        if (!$extensionsGroups = CentileENT::getExtensionGroups($context))
            return response()->json(['data' => []]);

        $extensionsToMap = [];
        foreach ($extensionsGroups as &$group) {
            $group->extensions = CentileENT::listExtensionsInGroupAddress($context, $group->extension);
            foreach ($group->extensions as $extension)
                $extensionsToMap[] = $extension;
        }

        $extensionDetails = Extension::mapLinkedObjectsToExtensions($context, $extensionsToMap, true);

        foreach ($extensionsGroups as &$group) {
            foreach ($group->extensions as &$extension) {
                foreach ($extensionDetails as $extensionDetail) {
                    if ($extension == $extensionDetail['extension']) {
                        $extension = $extensionDetail;
                        break;
                    }
                }
            }
        }

        return response()->json(['data' => $extensionsGroups]);
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
    public function store(CentileExtensionsGroupStore $request, Prestation $prestation)
    {
        $context = $prestation->getCentileContext();
        $extensionsGroup = null;

        DB::transaction(function () use ($request, &$extensionsGroup, $prestation, $context) {
            // create Prestation in database
            $prestation = Prestation::centileFactory(
                CentilePrestationTypes::CENTREX_EXT_GROUP,
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

            // create extensions group in Centile
            $extensionsGroup = CentileENT::createExtensionGroup(
                $context,
                $request->input('extension'),
                $request->input('extensions') ? $request->input('extensions') : null,
                $request->input('label') ? $request->input('label') : null
            );
        });

        return response()->json(['data' => !$extensionsGroup ?  false : $extensionsGroup], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Prestation $prestation, ExtensionsGroup $extensionsGroup)
    {
        $extensionsGroup->extensions = CentileENT::listExtensionsInGroupAddress($prestation->getCentileContext(), $extensionsGroup->extension);

        return response()->json(['data' => $extensionsGroup]);
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
    public function update(CentileExtensionsGroupUpdate $request, Prestation $prestation, ExtensionsGroup $extensionsGroup)
    {
        $context = $prestation->getCentileContext();
        $params = [];

        if ($request->has('extension'))
            $params['extension'] = $request->input('extension');

        if ($request->exists('label'))
            $params['label'] = $request->input('label');

        if ($request->has('extension'))
            $extGroupPrestation = $extensionsGroup->findPrestation($prestation);

        if ($params)
            $extensionsGroup = CentileENT::updateExtensionGroup(
                $context,
                $extensionsGroup->extension,
                $params
            );

        if ($request->has('extension')) {
            $extGroupPrestation->valeur = $request->input('extension');
            $extGroupPrestation->save();
        }

        if ($request->exists('extensions')) {
            CentileENT::setExtensionsInExtensionGroup(
                $context,
                $request->has('extension') ? $request->input('extension') : $extensionsGroup->extension,
                $request->input('extensions') ? $request->input('extensions') : null
            );
        }

        return response()->json(['data' => $extensionsGroup->withExtensions($context)]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Prestation $prestation, ExtensionsGroup $extensionsGroup)
    {
        CentileENT::deleteExtensionGroup($prestation->getCentileContext(), $extensionsGroup->extension);

        $extensionsGroup->terminatePrestation($prestation);

        return response(null, 204);
    }
}
