<?php

namespace App\Helpers;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Throwable;

/**
 * Pencatat audit formal (Permenkes 24/2022 — who/what/when/old/new/why).
 * Kegagalan mencatat TIDAK boleh menggagalkan operasi bisnis utama,
 * jadi exception ditelan dan ditulis ke log aplikasi.
 */
class Audit
{
    public static function log(
        string $action,
        string $entityType,
        ?string $entityId = null,
        ?array $old = null,
        ?array $new = null,
        ?string $reason = null,
    ): void {
        try {
            AuditLog::create([
                'id' => (string) Str::uuid(),
                'user_id' => Auth::id(),
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'old' => $old,
                'new' => $new,
                'reason' => $reason,
                'ip_address' => request()?->ip(),
                'user_agent' => (string) request()?->userAgent(),
            ]);
        } catch (Throwable $th) {
            report($th);
        }
    }
}
