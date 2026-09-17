<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ICD10Controller extends Controller
{
    public function getICD10()
    {
        $response = Storage::disk('local')->get('icd10/dental_icd_x.json');
        $response = json_decode($response, true);

        return response()->json($response);
    }
}
