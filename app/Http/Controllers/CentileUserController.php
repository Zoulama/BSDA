<?php

namespace Provisioning\Http\Controllers;

use Illuminate\Http\Request;
use Provisioning\ComptaPrestation as Prestation;
use Provisioning\CentilePrestationTypes;
use Provisioning\Centile\User;
use Provisioning\Http\Requests\CentileUserStore;
use Provisioning\Http\Requests\CentileUserUpdate;
use DB;
use CentileENT;

class CentileUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Prestation $prestation)
    {
        $context = $prestation->getCentileContext();
        $users = CentileENT::getUsers($prestation->getCentileContext());
        foreach ($users as &$user)
            $user = $user->withoutPassword()
                ->withDevices($context, true)
                ->withPSTNs($context)
                ->withCallBarrings($context)
                ->withSoftPhone($context)
                ->withLogicalTerminals($context);

        return response()->json(['data' => $users]);
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
    public function store(CentileUserStore $request, Prestation $prestation)
    {
        $context = $prestation->getCentileContext();
        $user = null;

        DB::transaction(function () use ($request, &$user, $context, $prestation) {
            $prestation = Prestation::centileFactory(
                CentilePrestationTypes::CENTREX_USER,
                date('Y-m-d'),
                $prestation->getClientId(),
                $prestation->getGroupId(),
                $prestation->getCentileResellerContext(),
                $context,
                $request->input('login'),
                trim($request->input('firstName') . ' ' . $request->input('lastName')),
                Prestation::STATUS_COMPLETION,
                $prestation->getId()
            );

            $user = CentileENT::createUser(
                $context,
                $request->input('login'),
                $request->input('password'),
                $request->input('firstName'),
                $request->input('lastName'),
                $request->input('mobileNumber'),
                $request->input('email'),
                $request->input('extension'),
                $request->input('firstName') . ' ' . $request->input('lastName')
            );
        });

        if ($request->exists('callBarrings') && is_array($request->input('callBarrings')))
            $this->updateCallBarrings($context, $user, $request->input('callBarrings'));

        return response()->json(['data' => $user->withoutPassword()], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Prestation $prestation, User $user)
    {
        $context = $prestation->getCentileContext();

        return response()->json([
            'data' => $user->withoutPassword()
                ->withPSTNs($context)
                ->withDevices($context, true)
                ->withCallBarrings($context)
                ->withLogicalTerminals($context)
                ->withSoftPhone($context)
                ->withForwardings($context)
        ]);
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
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CentileUserUpdate $request, Prestation $prestation, User $user)
    {
        $context = $prestation->getCentileContext();

        $params = [];
        if ($request->exists('login'))
            $params['login'] = $request->input('login') ? $request->input('login') : null;
        if ($request->exists('firstName'))
            $params['firstName'] = $request->input('firstName') ? $request->input('firstName') : null;
        if ($request->exists('lastName'))
            $params['lastName'] = $request->input('lastName') ? $request->input('lastName') : null;
        if ($request->exists('mobileNumber'))
            $params['mobileNumber'] = $request->input('mobileNumber') ? $request->input('mobileNumber') : null;
        if ($request->exists('email'))
            $params['emails'] = $request->input('email') ? $request->input('email') : null;
        if ($request->input('extension'))
            $params['extension'] = $request->input('extension') ? $request->input('extension') : null;
        if ($request->exists('password'))
            $params['password'] = $request->input('password');
        if ($request->exists('callBarrings'))
            $callBarrings = $request->input('callBarrings') ? $request->input('callBarrings') : [];
        if ($request->exists('softphone'))
            $softphone = $request->input('softphone') == "false" ? 0 : 1;

        // find prestation before the login is changed as finding the prestation is based on the login
        if ($request->exists('firstName') || $request->exists('lastName') || $request->exists('login'))
            $userPrestation = $user->findPrestation($prestation);

        $user = CentileENT::updateUser($context, $user->login, $params);

        if ($request->exists('firstName') || $request->exists('lastName') || $request->exists('login')) {
            $userPrestation->description = trim($user->firstName . ' ' . $user->lastName);
            $userPrestation->valeur = $user->login;
            $userPrestation->save();
        }

        if (isset($callBarrings))
            $this->updateCallBarrings($context, $user, $callBarrings);

        if (isset($softphone))
            $this->updateUserExtensionSoftphone($prestation, $context, $user->extension, $softphone);

        return response()->json(['data' => $user->withoutPassword()]);
    }

    protected function updateCallBarrings($context, User $user, array $callBarrings)
    {
        foreach (CentileENT::listAllCallBarringUsedByExtension($context, $user->extension) as $currentCallBarring) {
            if (!in_array($currentCallBarring->name, $callBarrings))
                CentileENT::unassignCallBarringToExtension($context, $currentCallBarring->name, $user->extension);
        }

        foreach ($callBarrings as $callBarring) {
            CentileENT::assignCallBarringToExtension(
                $context,
                $callBarring,
                $user->extension
            );
        }
    }

    protected function updateUserExtensionSoftphone($parentPrestation, $context, $extension, $softphone)
    {
      DB::transaction(function () use ($parentPrestation, $context, $extension, $softphone) {

        if ($softphone == 1) {
          Prestation::centileFactory(
              CentilePrestationTypes::CENTREX_SOFTPHONE,
              date('Y-m-d'),
              $parentPrestation->getClientId(),
              $parentPrestation->getGroupId(),
              $parentPrestation->getCentileResellerContext(),
              $context,
              $extension,
              null,
              Prestation::STATUS_COMPLETION,
              $parentPrestation->getId()
          );
        }
        else {
          $prestation = Prestation::where('type', CentilePrestationTypes::CENTREX_SOFTPHONE)
              ->where('linkedWith', $parentPrestation->getId())
              ->where('valeur', $extension)
              ->whereNull('validTill')
              ->first();

            if ($prestation)
              $prestation->terminate();
          }

        CentileENT::updateUserExtensionSoftphone($context,$extension,$softphone);

        });
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Prestation $prestation, User $user)
    {
        $context = $prestation->getCentileContext();

        if ($user->extension)
        {
            $prestationSoftphone = Prestation::where('type', CentilePrestationTypes::CENTREX_SOFTPHONE)
                ->where('linkedWith', $prestation->getId())
                ->where('valeur', $user->extension)
                ->whereNull('validTill')
                ->first();
            if ($prestationSoftphone)
                $prestationSoftphone->terminate();

            CentileENT::deleteUserExtension($context, $user->extension);
        }

        if (!CentileENT::deleteUser($context, $user->login))
            return response(null, 500);

        $user->terminatePrestation($prestation);

        return response(null, 204);
    }
}
