<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterInsurance;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Fase 4.3: master penjamin/asuransi — khusus manajemen
 * (satu-satunya role yang boleh mengubah master, sesuai Access matrix).
 */
class InsuranceController extends Controller
{
    public function store(Request $request)
    {
        $this->authorize('manage branch'); // izin manajemen-only yang ada

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:master_insurances,name',
            'type' => ['required', Rule::in(array_keys(MasterInsurance::TYPES))],
            'notes' => 'nullable|string|max:1000',
        ]);

        MasterInsurance::create([
            'id' => (string) Str::uuid(),
            'name' => $validated['name'],
            'type' => $validated['type'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Penjamin ditambahkan.');
    }

    public function update(Request $request, string $id)
    {
        $this->authorize('manage branch');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:master_insurances,name,'.$id,
            'type' => ['required', Rule::in(array_keys(MasterInsurance::TYPES))],
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'required|boolean',
        ]);

        MasterInsurance::where('id', $id)->firstOrFail()->update($validated);

        return back()->with('success', 'Penjamin diperbarui.');
    }
}
