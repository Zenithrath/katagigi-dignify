<?php

namespace App\Helpers;

class GeneralHelper
{
    public static function floatToRupiah(float $value): string
    {
        return 'Rp. '.number_format($value, 0, ',', '.');
    }

    public static function addPhoneCountryCode($phoneNumber, $countryCode)
    {
        $phoneNumber = preg_replace('/\D/', '', $phoneNumber);

        if (substr($phoneNumber, 0, 1) === '0') {
            $phoneNumber = substr($phoneNumber, 1);
        }

        $phoneNumber = $countryCode.$phoneNumber;

        return $phoneNumber;
    }
}
