<?php

namespace App\Http\Controllers\Operational;

use App\Helpers\BranchContext;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BranchController extends Controller
{
    public function index()
    {
        $this->authorize('manage branch');

        return view('pages.operational.branches.index', [
            'branches' => DB::table('branches')->orderBy('code')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('manage branch');

        $validated = $request->validate([
            'code' => 'required|string|max:16|unique:branches,code',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:32',
        ]);

        DB::table('branches')->insert([
            'id' => (string) Str::uuid(),
            'org' => 'Klinik Kata Gigi',
            'code' => $validated['code'],
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Cabang ditambahkan.');
    }

    public function toggle($id)
    {
        $this->authorize('manage branch');

        $branch = DB::table('branches')->where('id', $id)->firstOrFail();
        DB::table('branches')->where('id', $id)->update(['is_active' => ! $branch->is_active]);

        return back()->with('success', 'Status cabang diperbarui.');
    }

    /**
     * Ganti konteks cabang aktif (semua user login; memengaruhi
     * filter antrian/tagihan + penandaan record baru).
     */
    public function switch(Request $request)
    {
        $validated = $request->validate(['branch_id' => 'nullable|string']);
        BranchContext::set($validated['branch_id'] ?? null);

        return back()->with('success', 'Cabang aktif: '.(BranchContext::current()?->name ?? 'Semua cabang').'.');
    }
}
