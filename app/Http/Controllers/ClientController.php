<?php

namespace Provisioning\Http\Controllers;

use Illuminate\Http\Request;
use Provisioning\ComptaPrestation as Prestation;
use Provisioning\Client;

class ClientController extends Controller
{
    /**
    * Show a list of all the prestations from client formatted for Datatables.
    *
    * @param int $id
    *
    * @return Datatables JSON
    */
    public function getClientsPrestations(Request $request, Client $client)
    {
        $prestations = Prestation::select(['prestationID', 'type', 'valeur', 'description', 'validFrom', 'validTill'])
            ->where('clientID', '=', $client->getId())->get();

        return response()->json([
            'data' => $prestations,
        ], 200);
    }

    public function show(Client $client)
    {
        return response()->json(['data' => $client]);
    }
}
