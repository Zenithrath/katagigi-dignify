<?php

namespace App\Http\Controllers\Operational;

use App\Helpers\Audit;
use App\Http\Controllers\Controller;
use App\Models\OrganizationProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrganizationProfileController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('manage satusehat');
        $branchId = $request->user()->current_branch_id
            ?? auth()->user()->current_branch_id;

        $profile = OrganizationProfile::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('organization_name')
            ->get();

        return view('pages.integration.satusehat.organization', [
            'profiles' => $profile,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('manage satusehat');

        $validated = $request->validate([
            'branch_id' => 'required|uuid|exists:branches,id',
            'organization_name' => 'required|string|max:255',
            'organization_ihs' => 'nullable|string|max:64',
            'nakes_facility_code' => 'nullable|string|max:32',
            'location_ihs' => 'nullable|string|max:64',
            'location_name' => 'nullable|string|max:255',
            'region_code' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:2000',
            'phone' => 'nullable|string|max:32',
            'email' => 'nullable|email|max:255',
            'practitioner_ihs' => 'nullable|string|max:64',
        ]);

        $profile = OrganizationProfile::create([
            'id' => (string) Str::uuid(),
            ...$validated,
            'active' => true,
        ]);

        Audit::log('satusehat.org_profile.create', 'organization_profiles', $profile->id, null, [
            'branch_id' => $profile->branch_id,
        ]);

        return back()->with('success', 'Profil Organization/Location SATUSEHAT tersimpan.');
    }

    public function update(Request $request, $id)
    {
        $this->authorize('manage satusehat');
        $profile = OrganizationProfile::findOrFail($id);

        $validated = $request->validate([
            'organization_name' => 'required|string|max:255',
            'organization_ihs' => 'nullable|string|max:64',
            'nakes_facility_code' => 'nullable|string|max:32',
            'location_ihs' => 'nullable|string|max:64',
            'location_name' => 'nullable|string|max:255',
            'region_code' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:2000',
            'phone' => 'nullable|string|max:32',
            'email' => 'nullable|email|max:255',
            'practitioner_ihs' => 'nullable|string|max:64',
            'active' => 'nullable|boolean',
        ]);

        $profile->update($validated);
        Audit::log('satusehat.org_profile.update', 'organization_profiles', $profile->id, null, [
            'branch_id' => $profile->branch_id,
        ]);

        return back()->with('success', 'Profil SATUSEHAT diperbarui.');
    }
}
