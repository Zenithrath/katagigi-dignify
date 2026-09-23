<?php

namespace App\Http\Controllers\Operational;

use App\Helpers\Audit;
use App\Http\Controllers\Controller;
use App\Models\SatuSehatCredential;
use App\Services\SatuSehat\SatuSehatService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Fase 2.1: UI admin untuk kredensial SATUSEHAT per cabang.
 * Secret tidak pernah dikirim balik ke view (hanya mask "••••last4").
 */
class SatuSehatCredentialController extends Controller
{
    public function __construct(private SatuSehatService $satusehat) {}

    public function index()
    {
        $this->authorize('manage satusehat');

        return view('pages.integration.satusehat.credentials', [
            'branches' => \App\Models\Branch::with('satusehatCredential')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, string $branchId)
    {
        $this->authorize('manage satusehat');

        $validated = $request->validate([
            'client_id' => 'required|string|max:191',
            // Kosong = pertahankan secret lama (tidak dikirim balik ke form).
            'client_secret' => 'nullable|string|max:191',
            'organization_id' => 'nullable|string|max:64',
            'location_id' => 'nullable|string|max:64',
            'environment' => ['required', Rule::in(array_keys(SatuSehatCredential::ENVIRONMENTS))],
            'is_active' => 'required|boolean',
        ]);

        $credential = SatuSehatCredential::where('branch_id', $branchId)->first();

        if (! $credential) {
            abort_unless(! empty($validated['client_secret']), 422, 'Client secret wajib diisi saat pertama kali.');

            $credential = SatuSehatCredential::create([
                'id' => (string) Str::uuid(),
                'branch_id' => $branchId,
                'client_id' => $validated['client_id'],
                'client_secret' => $validated['client_secret'],
                'organization_id' => $validated['organization_id'] ?? null,
                'location_id' => null,
                'environment' => $validated['environment'],
                'is_active' => $validated['is_active'],
            ]);
        } else {
            $credential->update([
                'client_id' => $validated['client_id'],
                'organization_id' => $validated['organization_id'] ?? null,
                'location_id' => $validated['location_id'] ?? null,
                'environment' => $validated['environment'],
                'is_active' => $validated['is_active'],
            ]);

            if (! empty($validated['client_secret'])) {
                $credential->update(['client_secret' => $validated['client_secret']]);
            }
        }

        Audit::log('satusehat.credential.update', 'satusehat_credentials', $credential->id, null, [
            'branch_id' => $branchId,
        ]);

        return back()->with('success', 'Kredensial SATUSEHAT cabang tersimpan.');
    }

    /** Ping OAuth untuk memverifikasi kredensial cabang tanpa kirim data pasien. */
    public function verify(string $branchId)
    {
        $this->authorize('manage satusehat');

        try {
            $config = $this->satusehat->resolveConfig($branchId);                $response = \Illuminate\Support\Facades\Http::asForm()
                    ->post($config['auth_url'].'/accesstoken', [
                        'grant_type' => 'client_credentials',
                    'client_id' => $config['client_id'],
                    'client_secret' => $config['client_secret'],
                ]);

            abort_unless($response->successful(), 422, 'Verifikasi gagal: HTTP '.$response->status());

            return back()->with('success', 'Kredensial valid — token diterima server SATUSEHAT.');
        } catch (\Throwable $e) {
            return back()->withErrors(['satusehat' => 'Verifikasi gagal: '.$e->getMessage()]);
        }
    }
}
