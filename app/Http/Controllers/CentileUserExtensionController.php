<?php

namespace Provisioning\Http\Controllers;

use Illuminate\Http\Request;
use Provisioning\ComptaPrestation as Prestation;
use Provisioning\Centile\Extension;
use Provisioning\Centile\DialPlan;
use Provisioning\Helpers\LibClient;
use Provisioning\CentilePrestationTypes;
use Provisioning\Centile\UserExtension;
use Provisioning\Exceptions\InvalidPhoneNumberException;
use DB;
use CentileENT;

class CentileUserExtensionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Prestation $prestation)
    {
        $context = $prestation->getCentileContext();

        $userExtensions = CentileENT::getUserExtensions($context);

        $pstnNumbers = CentileENT::getPstnNumbers($context);
        foreach ($userExtensions as &$userExtension) {
            $userExtension->pstns = [];
            foreach ($pstnNumbers as $pstnNumber) {
                if ($userExtension->number == $pstnNumber->numberExtension)
                    $userExtension->pstns[] = $pstnNumber;
            }
        }

        $users = CentileENT::getUsers($context);
        foreach ($userExtensions as &$userExtension) {
            $userExtension->user = null;
            foreach ($users as $user) {
                if ($userExtension->number == $user->extension)
                    $userExtension->user = $user;
            }
        }

        $devices = CentileENT::getTerminals($context);
        foreach ($userExtensions as &$userExtension) {
            $userExtension->devices = [];
            foreach ($devices as $device) {
                if ($userExtension->number == $device->extension)
                    $userExtension->devices[] = $device;
            }
        }

        return response()->json(['data' => $userExtensions]);
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
    public function store(Request $request, Prestation $prestation)
    {
        $parentContext = $prestation->getCentileContext();
        $number = null;

        DB::transaction(function () use ($request, &$userExt, $parentContext, $prestation) {
            Prestation::centileFactory(
                CentilePrestationTypes::CENTREX_USER_EXT,
                (new \Datetime())->format('Y-m-d'),
                $prestation->getClientId(),
                $prestation->getGroupId(),
                $prestation->getCentileResellerContext(),
                $parentContext,
                $request->input('extension'),
                null,
                Prestation::STATUS_COMPLETION,
                $prestation->getId()
            );

            if (!$userExtension = CentileENT::createUserExtension(
                $parentContext,
                $request->input('extension'),
                $request->input('label')
            )) {
                return response()->json(['error' => 'Could not create user extension ' . $request->input('extension')]);
            }

            if ($request->input('number')) {
                try {
                    $number = toE164($request->input('number'));
                    CentileENT::updatePstnNumber($parentContext, $number, ['numberExtension' => $request->input('extension')]);
                } catch (\Exception $e) {
                    throw new PSTNNumberNotFoundException($e);
                }
            }
        });
        return response()->json(['data' => [
            'extension' => $request->input('extension'),
            'label' => $request->input('label'),
            'number' => $number
        ]]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Prestation $prestation, $extension)
    {
        $context = $prestation->getCentileContext();
        $ext = CentileENT::getUserExtension($context, $extension);

        $ext->pstns = [];
        if ($pstns = CentileENT::getPstnNumbersByUserExtension($context, $extension)) {
            foreach ($pstns as $pstn)
                $ext->pstns[] = $pstn->number;
        }

        return response()->json(['data' => $ext]);
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
    public function update(Request $request, Prestation $prestation, $selected)
    {
        $context = $prestation->getCentileContext();

        if ($request->input('number')) {
            try {
                $number = toE164($request->input('number'));
                CentileENT::updatePstnNumber($context, $number, ['numberExtension' => $selected]);
            } catch (\Exception $e) {
                throw new InvalidPhoneNumberException($e);
            }
        } else {
            // if $request->input('number') is not sent, then the pstn is empty
            // so we have to unassign it (only if it was previously assigned)
            if ($pstns = CentileENT::getPstnNumbersByUserExtension($context, $selected)) {
                foreach ($pstns as $pstn)
                    CentileENT::updatePstnNumber($context, $pstn->number, ['numberExtension' => null]);
            }
        }

        $ext = CentileENT::updateUserExtension(
            $context,
            $selected,
            $request->input('label')
        );

        return response()->json(['data' => $ext]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Prestation $prestation, $extension)
    {
        if (!CentileENT::deleteUserExtension($prestation->getCentileContext(), $extension))
            return response()->json(['error' => ['message' => 'Unable to delete user extension']], 400);

        return response(null, 204);
    }

    public function all(Prestation $prestation)
    {
        $context = $prestation->getCentileContext();
        $userExtensions = CentileENT::getUserExtensions($context);
        $extensions = array_pluck($userExtensions, 'number');
        sort($extensions);

        return response()->json(['data' => Extension::mapLinkedObjectsToExtensions($context, $extensions, true, true)]);
    }

    public function assignedToUser(Prestation $prestation)
    {
        $context = $prestation->getCentileContext();
        $extensions = UserExtension::getAssignedExtensions($context);
        sort($extensions);

        return response()->json(['data' => Extension::mapLinkedObjectsToExtensions($context, $extensions, true, false)]);
    }

    public function notAssignedToUser(Prestation $prestation)
    {
        $context = $prestation->getCentileContext();

        $userExtensions = CentileENT::getUserExtensions($context);
        $all = array_pluck($userExtensions, 'number');

        $users = CentileENT::getUsers($context);
        $used = array_pluck($users, 'extension');

        $ret = [];
        foreach ($all as $number) {
            if (!in_array($number, $used))
               $ret[] = (string)$number;
        }

        sort($ret);

        return response()->json(['data' => Extension::mapLinkedObjectsToExtensions($context, $ret, false, false)]);
    }
}
