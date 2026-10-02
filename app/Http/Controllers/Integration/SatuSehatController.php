<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Jobs\SyncVisitToSatuSehat;
use App\Models\SatuSehatSyncLog;
use App\Models\Visit;
use App\Services\SatuSehat\SatuSehatService;
use Illuminate\Http\Request;
use Throwable;

class SatuSehatController extends Controller
{
    public function __construct(private SatuSehatService $satusehat) {}

    public function index(Request $request)
    {
        $this->authorize('manage satusehat');

        $query = SatuSehatSyncLog::with(['visit:id,visit_number'])
            ->orderBy('created_at', 'desc');
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('resource')) {
            $query->where('resource_type', $request->resource);
        }

        return view('pages.integration.satusehat.index', [
            'logs' => $query->paginate(20)->withQueryString(),
            'enabled' => $this->satusehat->isEnabled(),
            'env' => config('satusehat.env'),
        ]);
    }

    public function sync(Request $request, $visitId)
    {
        $this->authorize('manage satusehat');
        $visit = Visit::findOrFail($visitId);
        abort_unless($visit->isSigned(), 422, 'Hanya visit SIGNED yang disinkronkan.');

        try {
            // Fase 2.2: queue=true → job antre (tanpa memblokir request);
            // default sinkron agar hasil langsung terlihat di flash message.
            if ($request->boolean('queue')) {
                dispatch(new SyncVisitToSatuSehat($visit));

                return back()->with('success', 'Sinkronisasi dikirim ke antrian — pantau di log SATUSEHAT.');
            }

            $summary = $this->satusehat->syncVisit($visit);

            return back()->with(
                'success',
                "Sinkron selesai: {$summary['success']} sukses, {$summary['failed']} gagal, {$summary['skipped']} dilewati."
            );
        } catch (Throwable $th) {
            return back()->withErrors(['error' => $th->getMessage()]);
        }
    }

    /**
     * Ulangi sinkron visit ini. Aman diulang: resource yang sudah sukses dan
     * tidak berubah dilewati tanpa HTTP, yang berubah di-PUT (lihat SatuSehatService::post).
     */
    public function retry($visitId)
    {
        $this->authorize('manage satusehat');
        $visit = Visit::findOrFail($visitId);
        abort_unless($visit->isSigned(), 422, 'Hanya visit SIGNED yang disinkronkan.');

        $failed = SatuSehatSyncLog::query()
            ->where('visit_id', $visit->id)
            ->where('status', SatuSehatSyncLog::STATUS_FAILED)
            ->count();

        if ($failed === 0) {
            return back()->with('success', 'Tidak ada sinkronisasi yang gagal pada visit ini.');
        }

        try {
            $summary = $this->satusehat->syncVisit($visit);

            return back()->with(
                'success',
                "Ulang sinkron ({$failed} sebelumnya gagal): {$summary['success']} sukses, {$summary['failed']} gagal, {$summary['skipped']} dilewati."
            );
        } catch (Throwable $th) {
            return back()->withErrors(['error' => $th->getMessage()]);
        }
    }
}
