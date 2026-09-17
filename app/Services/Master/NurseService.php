<?php

namespace App\Services\Master;

use App\Helpers\FileHelper;
use App\Models\User;
use App\Types\Entities\NurseEntity;
use App\Types\FileMetadata;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NurseService
{
    public function storeCoverImage(mixed $image): FileMetadata|Exception
    {
        return FileHelper::storeFile($image, 'uploads/images/cover', 'public');
    }

    public function storeProfileImage(mixed $image): FileMetadata|Exception
    {
        return FileHelper::storeFile($image, 'uploads/images/profile', 'public');
    }

    public function deleteCoverImage(mixed $image): bool|Exception
    {
        return FileHelper::deleteFile('public', $image);
    }

    public function deleteProfileImage(mixed $image): bool|Exception
    {
        return FileHelper::deleteFile('public', $image);
    }

    public function selectAllNurse(int $limit = 10, int $offset = 0): Collection
    {
        return DB::table('nurses')
            ->join('users', 'users.id', '=', 'nurses.user_id', 'left')
            ->join('user_addresses', 'user_addresses.user_id', '=', 'nurses.user_id', 'left')
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

    public function selectNurseByID(string $userID): object
    {
        return DB::table('nurses')
            ->join('users', 'users.id', '=', 'nurses.user_id', 'left')
            ->join('user_addresses', 'user_addresses.user_id', '=', 'nurses.user_id', 'left')
            ->select([
                'cover_picture',
                'profile_picture',
                'name',
                'nipp',
                'niptk',
                'village',
                'street',
                'zip_code',
                'tonarigumi',
                'district',
                'regency',
                'province',
                'email',
            ])
            ->where('users.id', $userID)
            ->first();
    }

    public function insertNurse(NurseEntity $nurse): string|Exception
    {
        $userID = Str::uuid();
        $user = User::create([
            'id' => $userID,
            'name' => $nurse->name,
            'email' => strtolower($nurse->email),
            'password' => $nurse->password,
            'email_verified_at' => Carbon::now(),
            'remember_token' => Str::random(10),
        ]);

        $user->assignRole('nurse');

        if (! $user) {
            return new Exception("user can't be inserted", 500);
        }

        $isSaved = DB::table('nurses')
            ->insert([
                'user_id' => $userID,
                'nipp' => $nurse->nipp,
                'niptk' => $nurse->niptk,
                'profile_picture' => $nurse->profile_picture,
                'cover_picture' => $nurse->cover_picture,
            ]);

        if (! $isSaved) {
            return new Exception("nurse can't be inserted", 500);
        }

        $isSaved = DB::table('user_addresses')
            ->insert([
                'user_id' => $userID,
                'street' => $nurse->address->street,
                'village' => $nurse->address->village,
                'tonarigumi' => $nurse->address->tonarigumi,
                'district' => $nurse->address->district,
                'regency' => $nurse->address->regency,
                'province' => $nurse->address->province,
                'zip_code' => $nurse->address->zip_code,
            ]);

        if (! $isSaved) {
            return new Exception("user address can't be inserted", 500);
        }

        return $userID;
    }

    public function updateNurse(NurseEntity $nurse): string|Exception
    {
        $affected = DB::table('users')
            ->where('id', $nurse->id)
            ->update([
                'name' => $nurse->name,
                'email' => strtolower($nurse->email),
            ]);

        if (! $affected) {
            return new Exception("user can't be updated", 500);
        }

        $affected = DB::table('nurses')
            ->where('user_id', $nurse->id)
            ->update([
                'nipp' => $nurse->nipp,
                'niptk' => $nurse->niptk,
                'profile_picture' => $nurse->profile_picture,
                'cover_picture' => $nurse->cover_picture,
            ]);

        if (! $affected) {
            return new Exception("nurse can't be updated", 500);
        }

        $affected = DB::table('user_addresses')
            ->where('user_id', $nurse->id)
            ->update([
                'street' => $nurse->address->street,
                'village' => $nurse->address->village,
                'tonarigumi' => $nurse->address->tonarigumi,
                'district' => $nurse->address->district,
                'regency' => $nurse->address->regency,
                'province' => $nurse->address->province,
                'zip_code' => $nurse->address->zip_code,
            ]);

        if (! $affected) {
            return new Exception("user address can't be updated", 500);
        }

        return $nurse->id;
    }

    public function deleteNurse(string $userID): string|Exception
    {
        $affected = DB::table('user_addresses')
            ->where('user_id', $userID)
            ->delete();

        if (! $affected) {
            return new Exception("user address can't be removed", 500);
        }

        $affected = DB::table('nurses')
            ->where('user_id', $userID)
            ->delete();

        if (! $affected) {
            return new Exception("nurse can't be removed", 500);
        }

        $affected = DB::table('users')
            ->where('id', $userID)
            ->delete();

        if (! $affected) {
            return new Exception("user can't be removed", 500);
        }

        return $userID;
    }
}
