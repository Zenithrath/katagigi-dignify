<?php

namespace App\Types\Entities;

class PatientEntity
{
    public ?string $id;

    public ?string $name;

    public ?string $code;

    public ?string $email;

    public ?string $payment_email;

    public ?string $sosmed;

    public ?string $phone;

    public ?string $birthdate;

    public ?string $religion;

    public ?string $gender;

    public ?object $address;

    public ?string $picture;

    public function fromRequest(array $validatedRequest, string $id, $picturePath, string $code)
    {
        $this->id = $id;
        $this->code = $code;
        $this->name = $validatedRequest['name'];
        $this->email = $validatedRequest['email'] ?? null;
        $this->payment_email = $validatedRequest['payment_email'] ?? null;
        $this->sosmed = $validatedRequest['sosmed'] ?? null;
        $this->phone = $validatedRequest['phone'];
        $this->birthdate = $validatedRequest['birthdate'] ?? null;
        $this->religion = $validatedRequest['religion'] ?? null;
        $this->gender = $validatedRequest['gender'] ?? null; // not nuulable -> default : male
        $this->picture = $picturePath;

        // address data
        $this->address = new PatientAddressEntity;
        $this->address->zip_code = $validatedRequest['zip_code'];
        $this->address->tonarigumi = $validatedRequest['tonarigumi'];
        $this->address->street = $validatedRequest['street'];
        $this->address->village = $validatedRequest['village'];
        $this->address->district = $validatedRequest['district'];
        $this->address->regency = $validatedRequest['regency'];
        $this->address->province = $validatedRequest['province'];
    }
}
