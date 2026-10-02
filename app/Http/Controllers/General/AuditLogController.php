<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

/**
 * Fase 4: monitoring audit log (manajemen) — jejak who/what/when/why.
 */
class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('read audit log');

        $query = AuditLog::with('user:id,name,email')->orderBy('created_at', 'desc');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('user')) {
            $query->where('user_id', $request->user);
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        return view('pages.general.audit-logs', [
            'logs' => $query->paginate(20)->withQueryString(),
            'actions' => AuditLog::query()->select('action')->distinct()->orderBy('action')->pluck('action'),
            'users' => AuditLog::query()->with('user:id,name')->get()->pluck('user.name', 'user_id')->filter()->unique(),
        ]);
    }
}
