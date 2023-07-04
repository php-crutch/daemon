<?php

declare(strict_types=1);

namespace Crutch\Daemon\Exception;

use Throwable;

final class PidFileIsNotReadable extends DaemonException
{
    public function __construct(string $pidFile, int $code = 1, Throwable $previous = null)
    {
        parent::__construct(sprintf('PID file %s is not readable', $pidFile), $code, $previous);
    }
}
