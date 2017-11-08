<?php

namespace Provisioning\Http\Controllers;

use Illuminate\Http\Request;
use Provisioning\ComptaPrestation as Prestation;
use Provisioning\Exceptions\CallBarringNotFoundException;
use CentileENT;

class CentileUserCallBarringController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Prestation $prestation, $login)
    {
        $user = CentileENT::getUser($login);
        $callBarrings = CentileENT::listAllCallBarringUsedByExtension($prestation->getCentileContext(), $user->extension);

        return response()->json(['data' => $callBarrings]);
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
    public function update(Request $request, Prestation $prestation, $login)
    {
        $context = $prestation->getCentileContext();
        $user = CentileENT::getUser($login);

        foreach (CentileENT::listAllCallBarringUsedByExtension($context, $user->extension) as $currentCallBarring) {
            if (!$request->input('callBarrings') || !in_array($currentCallBarring->name, $request->input('callBarrings')))
                CentileENT::unassignCallBarringToExtension($context, $currentCallBarring->name, $user->extension);
        }

        if ($request->input('callBarrings') && is_array($request->input('callBarrings'))) {
            foreach ($request->input('callBarrings') as $callBarring) {
                if (!CentileENT::getCallBarring($callBarring))
                    throw new CallBarringNotFoundException($callBarring);

                if (!CentileENT::assignCallBarringToExtension(
                    $context,
                    $callBarring,
                    $user->extension
                ))
                    return response()->json(['error' => ['message' => "Error assigning call barring " . $callBarring . " to extension " . $user->extension]]);
            }
        }

        return response(null, 204);
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
