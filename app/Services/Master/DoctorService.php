<?php

namespace App\Services\Master;

use App\Helpers\FileHelper;
use App\Models\User;
use App\Types\Entities\DoctorEntity;
use App\Types\FileMetadata;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DoctorService
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

    public function selectAllDoctorOption(): Collection
    {
        return DB::table('doctors')
            ->join('users', 'users.id', '=', 'doctors.user_id', 'left')
            ->select([
                'users.id as id',
                'users.name as name',
                'doctors.nipp as nipp',
            ])
            ->orderBy('name', 'asc')
            ->get();
    }

    public function selectAllDoctor(int $limit = 10, int $offset = 0): Collection
    {
        return DB::table('doctors')
            ->join('users', 'users.id', '=', 'doctors.user_id', 'left')
            ->join('user_addresses', 'user_addresses.user_id', '=', 'doctors.user_id', 'left')
            ->orderBy('name', 'asc')
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

    public function selectDoctorByID(string $userID): ?object
    {
        return DB::table('doctors')
            ->join('users', 'users.id', '=', 'doctors.user_id', 'left')
            ->join('user_addresses', 'user_addresses.user_id', '=', 'doctors.user_id', 'left')
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

    public function insertDoctor(DoctorEntity $doctor): string|Exception
    {
        // dd($doctor);
        $userID = Str::uuid();
        $user = User::create([
            'id' => $userID,
            'name' => $doctor->name,
            'email' => strtolower($doctor->email),
            'password' => $doctor->password,
            'email_verified_at' => Carbon::now(),
            'remember_token' => Str::random(10),
        ]);

        $user->assignRole('doctor');

        if (! $user) {
            return new Exception("user can't be inserted", 500);
        }

        $isSaved = DB::table('doctors')
            ->insert([
                'user_id' => $userID,
                'nipp' => $doctor->nipp,
                'niptk' => $doctor->niptk,
                'profile_picture' => $doctor->profile_picture,
                'cover_picture' => $doctor->cover_picture,
            ]);

        if (! $isSaved) {
            return new Exception("doctor can't be inserted", 500);
        }

        $isSaved = DB::table('user_addresses')
            ->insert([
                'user_id' => $userID,
                'street' => $doctor->address->street,
                'village' => $doctor->address->village,
                'tonarigumi' => $doctor->address->tonarigumi,
                'district' => $doctor->address->district,
                'regency' => $doctor->address->regency,
                'province' => $doctor->address->province,
                'zip_code' => $doctor->address->zip_code,
            ]);

        if (! $isSaved) {
            return new Exception("user address can't be inserted", 500);
        }

        return $userID;
    }

    public function updateDoctor(DoctorEntity $doctor): string|Exception
    {
        $userUpdateData = [
            'name' => $doctor->name,
            'email' => strtolower($doctor->email),
        ];

        if ($doctor->password) {
            $userUpdateData['password'] = $doctor->password;
        }

        $affected = DB::table('users')
            ->where('id', $doctor->id)
            ->update($userUpdateData);

        if (! $affected) {
            return new Exception("user can't be updated", 500);
        }

        $affected = DB::table('doctors')
            ->where('user_id', $doctor->id)
            ->update([
                'nipp' => $doctor->nipp,
                'niptk' => $doctor->niptk,
                'profile_picture' => $doctor->profile_picture,
                'cover_picture' => $doctor->cover_picture,
            ]);

        if (! $affected) {
            return new Exception("doctor can't be updated", 500);
        }

        $affected = DB::table('user_addresses')
            ->where('user_id', $doctor->id)
            ->update([
                'street' => $doctor->address->street,
                'village' => $doctor->address->village,
                'tonarigumi' => $doctor->address->tonarigumi,
                'district' => $doctor->address->district,
                'regency' => $doctor->address->regency,
                'province' => $doctor->address->province,
                'zip_code' => $doctor->address->zip_code,
            ]);

        if (! $affected) {
            return new Exception("user address can't be updated", 500);
        }

        return $doctor->id;
    }

    public function deleteDoctor(string $userID): string|Exception
    {
        $affected = DB::table('user_addresses')
            ->where('user_id', $userID)
            ->delete();

        if (! $affected) {
            return new Exception("user address can't be removed", 500);
        }

        $affected = DB::table('doctors')
            ->where('user_id', $userID)
            ->delete();

        if (! $affected) {
            return new Exception("doctor can't be removed", 500);
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
