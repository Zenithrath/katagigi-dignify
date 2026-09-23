<?php

namespace App\Http\Controllers\Operational;

use App\Http\Controllers\Controller;
use App\Models\RegionCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegionCodeController extends Controller
{
    public function lookup(Request $request): JsonResponse
    {
        $this->authorize('read patient');

        $request->validate([
            'q' => 'nullable|string|max:100',
            'level' => 'nullable|in:province,city,district,village',
            'parent_code' => 'nullable|string|max:10',
        ]);

        $query = RegionCode::active()->orderBy('name');

        if ($request->filled('level')) {
            $query->where('level', $request->string('level'));
        }
        if ($request->filled('parent_code')) {
            $query->where('parent_code', $request->string('parent_code'));
        }
        if ($request->filled('q')) {
            $term = $request->string('q');
            $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"));
        }

        return response()->json(
            $query->limit(25)->get(['code', 'name', 'level', 'parent_code'])
        );
    }
}
