<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
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

    public function sync($visitId)
    {
        $this->authorize('manage satusehat');
        $visit = Visit::findOrFail($visitId);
        abort_unless($visit->isSigned(), 422, 'Hanya visit SIGNED yang disinkronkan.');

        try {
            $summary = $this->satusehat->syncVisit($visit);

            return back()->with(
                'success',
                "Sinkron selesai: {$summary['success']} sukses, {$summary['failed']} gagal, {$summary['skipped']} dilewati."
            );
        } catch (Throwable $th) {
            return back()->withErrors('error', $th->getMessage());
        }
    }
}
