<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

/**
 * Konteks cabang aktif (multi-branch Fase 4).
 * null = semua cabang (mode single / pusat).
 */
class BranchContext
{
    public const SESSION_KEY = 'branch_id';

    public static function currentId(): ?string
    {
        $id = Session::get(self::SESSION_KEY);
        if ($id && DB::table('branches')->where('id', $id)->where('is_active', true)->exists()) {
            return $id;
        }

        return null;
    }

    public static function current(): ?object
    {
        $id = self::currentId();

        return $id ? DB::table('branches')->where('id', $id)->first() : null;
    }

    public static function set(?string $id): void
    {
        if ($id && ! DB::table('branches')->where('id', $id)->where('is_active', true)->exists()) {
            return;
        }

        Session::put(self::SESSION_KEY, $id);
    }

    public static function defaultId(): ?string
    {
        return DB::table('branches')->where('is_active', true)->orderBy('code')->value('id');
    }
}
