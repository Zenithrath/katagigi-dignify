<?php

namespace App\Types\Entities;

use Illuminate\Support\Facades\Hash;

class AdminEntity extends UserEntity
{
    public ?string $nipp;

    public ?string $niptk;

    public ?string $profile_picture;

    public ?string $cover_picture;

    public function fromRequest(array $validatedRequest, ?string $userID = null, ?string $profilePicturePath = null, ?string $coverPicturePath = null)
    {
        $this->id = $userID;
        $this->name = $validatedRequest['name'];
        $this->email = $validatedRequest['email'];
        $this->password = Hash::make($validatedRequest['password']);
        $this->is_active = true;

        // address data
        $this->address = new UserAddressEntity;
        $this->address->street = $validatedRequest['street'];
        $this->address->village = $validatedRequest['village'];
        $this->address->tonarigumi = $validatedRequest['tonarigumi'];
        $this->address->district = $validatedRequest['district'];
        $this->address->regency = $validatedRequest['regency'];
        $this->address->province = $validatedRequest['province'];
        $this->address->zip_code = $validatedRequest['zip_code'];

        // admin data
        $this->nipp = $validatedRequest['nipp'];
        $this->niptk = $validatedRequest['niptk'];
        $this->profile_picture = $profilePicturePath;
        $this->cover_picture = $coverPicturePath;
    }

    public function updateRequest(object $validatedRequest, ?string $userID = null, ?string $profilePicturePath = null, ?string $coverPicturePath = null)
    {
        $this->id = $userID;
        $this->name = $validatedRequest['name'];
        $this->email = $validatedRequest['email'];
        $this->password = Hash::make($validatedRequest['password']);

        // address data
        $this->address = new UserAddressEntity;
        $this->address->street = $validatedRequest['street'];
        $this->address->village = $validatedRequest['village'];
        $this->address->tonarigumi = $validatedRequest['tonarigumi'];
        $this->address->district = $validatedRequest['district'];
        $this->address->regency = $validatedRequest['regency'];
        $this->address->province = $validatedRequest['province'];
        $this->address->zip_code = $validatedRequest['zip_code'];

        // admin data
        $this->nipp = $validatedRequest['nipp'];
        $this->niptk = $validatedRequest['niptk'];
        $this->profile_picture = $profilePicturePath;
        $this->cover_picture = $coverPicturePath;
    }
}
