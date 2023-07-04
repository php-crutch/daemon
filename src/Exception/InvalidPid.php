<?php

declare(strict_types=1);

namespace Crutch\Daemon\Exception;

use Throwable;

final class InvalidPid extends DaemonException
{
    public function __construct(string $pid, string $pidFile, int $code = 1, Throwable $previous = null)
    {
        parent::__construct(sprintf('invalid PID %s in file %s', $pid, $pidFile), $code, $previous);
    }
}
