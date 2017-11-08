<?php

namespace Provisioning\Http\Controllers;

use Illuminate\Http\Request;
use Provisioning\ComptaPrestation as Prestation;

class PrestationController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show(Request $request, Prestation $prestation)
    {
        if ($request->input('fields')) {
            $fields = explode(',', $request->input('fields'));
            $ret = null;
            foreach ($fields as $field) {
                if (isset($prestation, $field))
                    $ret[$field] = $prestation->$field;
            }

            return response()->json(['data' => $ret], 200);
        }

        return response()->json(['data' => $prestation], 200);
    }
}
