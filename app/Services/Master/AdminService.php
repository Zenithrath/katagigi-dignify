<?php

namespace App\Services\Master;

use App\Helpers\FileHelper;
use App\Models\User;
use App\Types\Entities\AdminEntity;
use App\Types\FileMetadata;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminService
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

    public function selectAllAdmin(int $limit = 10, int $offset = 0): Collection
    {
        return DB::table('admins')
            ->join('users', 'users.id', '=', 'admins.user_id', 'left')
            ->join('user_addresses', 'user_addresses.user_id', '=', 'admins.user_id', 'left')
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

    public function selectAdminByID(string $userID): ?object
    {
        return DB::table('admins')
            ->join('users', 'users.id', '=', 'admins.user_id', 'left')
            ->join('user_addresses', 'user_addresses.user_id', '=', 'admins.user_id', 'left')
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

    public function insertAdmin(AdminEntity $admin): string|Exception
    {
        $userID = Str::uuid();
        $user = User::create([
            'id' => $userID,
            'name' => $admin->name,
            'email' => strtolower($admin->email),
            'password' => $admin->password,
            'email_verified_at' => Carbon::now(),
            'remember_token' => Str::random(10),
        ]);

        $user->assignRole('admin');

        if (! $user) {
            return new Exception("user can't be inserted", 500);
        }

        $isSaved = DB::table('admins')
            ->insert([
                'user_id' => $userID,
                'nipp' => $admin->nipp,
                'niptk' => $admin->niptk,
                'profile_picture' => $admin->profile_picture,
                'cover_picture' => $admin->cover_picture,
            ]);

        if (! $isSaved) {
            return new Exception("admin can't be inserted", 500);
        }

        $isSaved = DB::table('user_addresses')
            ->insert([
                'user_id' => $userID,
                'street' => $admin->address->street,
                'village' => $admin->address->village,
                'tonarigumi' => $admin->address->tonarigumi,
                'district' => $admin->address->district,
                'regency' => $admin->address->regency,
                'province' => $admin->address->province,
                'zip_code' => $admin->address->zip_code,
            ]);

        if (! $isSaved) {
            return new Exception("user address can't be inserted", 500);
        }

        return $userID;
    }

    public function updateAdmin(AdminEntity $admin): string|Exception
    {
        $affected = DB::table('users')
            ->where('id', $admin->id)
            ->update([
                'name' => $admin->name,
                'email' => strtolower($admin->email),
                'password' => $admin->password,
            ]);

        if (! $affected) {
            return new Exception("user can't be updated", 500);
        }

        $affected = DB::table('admins')
            ->where('user_id', $admin->id)
            ->update([
                'nipp' => $admin->nipp,
                'niptk' => $admin->niptk,
                'profile_picture' => $admin->profile_picture,
                'cover_picture' => $admin->cover_picture,
            ]);

        if (! $affected) {
            return new Exception("admin can't be updated", 500);
        }

        $affected = DB::table('user_addresses')
            ->where('user_id', $admin->id)
            ->update([
                'street' => $admin->address->street,
                'village' => $admin->address->village,
                'tonarigumi' => $admin->address->tonarigumi,
                'district' => $admin->address->district,
                'regency' => $admin->address->regency,
                'province' => $admin->address->province,
                'zip_code' => $admin->address->zip_code,
            ]);

        if (! $affected) {
            return new Exception("user address can't be updated", 500);
        }

        return $admin->id;
    }

    public function deleteAdmin(string $userID): string|Exception
    {
        $affected = DB::table('user_addresses')
            ->where('user_id', $userID)
            ->delete();

        if (! $affected) {
            return new Exception("user address can't be removed", 500);
        }

        $affected = DB::table('admins')
            ->where('user_id', $userID)
            ->delete();

        if (! $affected) {
            return new Exception("admin can't be removed", 500);
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
