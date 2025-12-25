<?php

namespace App\Services;

use Symfony\Component\Process\Process;

class ProcessFactory
{
    public function create(array $command): Process
    {
        return new Process($command);
    }
}
