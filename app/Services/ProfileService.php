<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    public function getProfilePicture($role, $userID): ?string
    {
        $record = DB::table($role.'s')->where('user_id', $userID)
            ->select('profile_picture')->first();
        if (! $record || ! $record->profile_picture) {
            return null;
        }

        return asset(Storage::url('public/'.$record->profile_picture));
    }
}
