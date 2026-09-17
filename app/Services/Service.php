<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;

class Service
{
    protected function writeLog(string $position, Throwable|Exception $err)
    {
        Log::error($err->getMessage());

        Log::channel('dberr')->info(
            sprintf(
                "%s\t%s\t%s",
                date('c'),
                $position,
                $err->getMessage()
            )
        );
    }
}
