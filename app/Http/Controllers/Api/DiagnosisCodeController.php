<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DiagnosisCode;
use Illuminate\Http\Request;

class DiagnosisCodeController extends Controller
{
    /**
     * Autocomplete kode diagnosis: input bahasa awam -> kode resmi.
     * GET /api/diagnosis-codes?q=gigi+berlubang&system=ICD10&limit=10
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
            'system' => ['nullable', 'string', 'in:ICD10,ICD9,SNOMED,icd10,icd9,snomed'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:25'],
        ]);

        $limit = $validated['limit'] ?? 10;

        $results = DiagnosisCode::query()
            ->active()
            ->system($validated['system'] ?? null)
            ->search($validated['q'])
            // kode yang cocok persis / awalan didahulukan, lalu abjad display
            ->orderByRaw('CASE WHEN LOWER(code) LIKE ? THEN 0 ELSE 1 END', [strtolower($validated['q']).'%'])
            ->orderBy('display_id')
            ->limit($limit)
            ->get(['id', 'system', 'code', 'display_id', 'display_en', 'category']);

        return response()->json(['data' => $results]);
    }
}
