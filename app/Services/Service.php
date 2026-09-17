<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Output\ConsoleOutput;
use Throwable;

class Service
{
    protected $console;

    public function __construct()
    {
        $this->console = new ConsoleOutput;
    }

    protected function writeLog(string $position, Throwable|Exception $err)
    {
        $this->console->writeln($err->getMessage());

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
