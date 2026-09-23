<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\KfaProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KfaController extends Controller
{
    /** Autocomplete kamus KFA (obat & BHP) untuk form resep. */
    public function lookup(Request $request): JsonResponse
    {
        $this->authorize('write prescription');

        $request->validate(['q' => 'nullable|string|max:100']);

        $query = KfaProduct::query()->active()->orderBy('name');

        if ($request->filled('q')) {
            $term = $request->string('q');
            $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"));
        }

        return response()->json(
            $query->limit(25)->get(['code', 'name', 'dosage_form', 'is_drug'])
        );
    }
}
