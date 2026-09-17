<?php

namespace App\Types\Entities;

class UserEntity
{
    public string $id;

    public string $name;

    public string $email;

    public string $password;

    public bool $is_active;

    public UserAddressEntity $address;

    public string $created_at;

    public string $updated_at;
}
