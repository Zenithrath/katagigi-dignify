<?php

namespace App\Http\Controllers\Integration;

use App\Helpers\Audit;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Services\SatuSehat\SatuSehatService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Onboarding resource SATUSEHAT: Practitioner, Organization, Location.
 * Sandbox-aware: hanya dipanggil saat enabled + kredensial terisi.
 */
class PractitionerOnboardingController extends Controller
{
    public function __construct(private SatuSehatService $satusehat) {}

    public function index()
    {
        $this->authorize('manage satusehat');

        return view('pages.integration.satusehat.onboarding', [
            'branches' => Branch::orderBy('name')->get(),
            'enabled' => $this->satusehat->isEnabled(),
        ]);
    }

    public function registerOrganization(Request $request)
    {
        $this->authorize('manage satusehat');
        $this->satusehat->ensureEnabled();

        $validated = $request->validate([
            'branch_id' => 'required|uuid|exists:branches,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:64',
            'phone' => 'nullable|string|max:32',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:2000',
            'region_code' => 'nullable|string|max:10',
        ]);

        $payload = [
            'resourceType' => 'Organization',
            'active' => true,
            'type' => [['coding' => [[
                'system' => 'http://terminology.hl7.org/CodeSystem/organization-type',
                'code' => 'prov',
                'display' => 'Healthcare Provider',
            ]]]],
            'name' => $validated['name'],
            'identifier' => $validated['code'] ? [[
                'system' => 'http://sys-ids.kemkes.go.id/nakes-facility',
                'value' => $validated['code'],
            ]] : [],
            'telecom' => array_values(array_filter([
                $validated['phone'] ? ['system' => 'phone', 'value' => $validated['phone'], 'use' => 'work'] : null,
                $validated['email'] ? ['system' => 'email', 'value' => $validated['email'], 'use' => 'work'] : null,
            ])),
            'address' => $validated['address'] ? [[
                'use' => 'work',
                'text' => $validated['address'],
                'country' => 'ID',
            ]] : [],
        ];

        return $this->postAndReport('Organization', $payload, $validated['branch_id']);
    }

    public function registerPractitioner(Request $request)
    {
        $this->authorize('manage satusehat');
        $this->satusehat->ensureEnabled();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nik' => 'required|string|size:16',
            'gender' => 'required|in:MALE,FEMALE',
            'birthdate' => 'required|date',
            'phone' => 'nullable|string|max:32',
            'sip_number' => 'nullable|string|max:64',
        ]);

        $payload = [
            'resourceType' => 'Practitioner',
            'active' => true,
            'identifier' => [[
                'system' => 'https://fhir.kemkes.go.id/id/nik',
                'value' => $validated['nik'],
            ]],
            'name' => [[
                'use' => 'official',
                'text' => $validated['name'],
                'family' => $validated['name'],
            ]],
            'gender' => strtolower($validated['gender']),
            'birthDate' => $validated['birthdate'],
            'telecom' => $validated['phone'] ? [[
                'system' => 'phone',
                'value' => $validated['phone'],
                'use' => 'work',
            ]] : [],
            'qualification' => $validated['sip_number'] ? [[
                'identifier' => [[
                    'system' => 'http://sys-ids.kemkes.go.id/sip',
                    'value' => $validated['sip_number'],
                ]],
                'code' => [['text' => 'Dokter Gigi / Tenaga Kesehatan']],
            ]] : [],
        ];

        return $this->postAndReport('Practitioner', $payload, null);
    }

    public function registerLocation(Request $request)
    {
        $this->authorize('manage satusehat');
        $this->satusehat->ensureEnabled();

        $validated = $request->validate([
            'organization_ihs' => 'required|string|max:64',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:2000',
            'region_code' => 'nullable|string|max:10',
        ]);

        $payload = [
            'resourceType' => 'Location',
            'status' => 'active',
            'name' => $validated['name'],
            'mode' => 'instance',
            'type' => [['coding' => [[
                'system' => 'http://terminology.hl7.org/CodeSystem/v3-RoleCode',
                'code' => 'HOSP',
                'display' => 'Hospital',
            ]]]],
            'managingOrganization' => ['reference' => 'Organization/'.$validated['organization_ihs']],
            'address' => $validated['address'] ? [[
                'use' => 'work',
                'text' => $validated['address'],
                'country' => 'ID',
            ]] : [],
        ];

        return $this->postAndReport('Location', $payload, null);
    }

    private function postAndReport(string $resource, array $payload, ?string $branchId)
    {
        try {
            $response = Http::withToken($this->satusehat->token())
                ->timeout(30)
                ->post(config('satusehat.base_url').'/fhir-r4/v1/'.$resource, $payload);

            if ($response->successful()) {
                Audit::log('satusehat.onboarding.'.$resource, $resource, $response->json('id'), null, [
                    'branch_id' => $branchId,
                ]);

                return back()->with('success', $resource.' IHS: '.$response->json('id'));
            }

            return back()->withErrors([
                'satusehat' => $resource.' gagal: HTTP '.$response->status().' — '.json_encode($response->json()),
            ]);
        } catch (Exception $e) {
            return back()->withErrors(['satusehat' => $resource.' gagal: '.$e->getMessage()]);
        }
    }
}
