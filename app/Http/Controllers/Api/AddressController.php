<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class AddressController extends Controller
{
    public function getProvinces()
    {
        $response = Storage::disk('local')->get('addresses/0.json');

        return response()->json(json_decode($response, true));
    }

    public function getRegencies($provinceId)
    {
        $response = Storage::disk('local')->get("addresses/{$provinceId}.json");

        return response()->json(json_decode($response, true));
    }

    public function getDistricts($provinceId, $regencyId)
    {
        $response = Storage::disk('local')->get("addresses/{$provinceId}/{$regencyId}.json");

        return response()->json(json_decode($response, true));
    }

    public function getVillages($provinceId, $regencyId, $districtId)
    {
        $response = Storage::disk('local')->get("addresses/{$provinceId}/{$regencyId}/{$districtId}.json");

        return response()->json(json_decode($response, true));
    }
}
