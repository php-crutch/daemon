<?php

declare(strict_types=1);

namespace Crutch\Daemon\Exception;

use Throwable;

final class CouldNotWritePid extends DaemonException
{
    public function __construct(string $pidFile, int $code = 1, Throwable $previous = null)
    {
        parent::__construct(sprintf('could not write PID to file %s', $pidFile), $code, $previous);
    }
}
