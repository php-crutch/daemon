<?php

declare(strict_types=1);

namespace Crutch\Daemon\Exception;

use Throwable;

final class DaemonAlreadyStarted extends DaemonException
{
    public function __construct(int $pid, int $code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf('daemon already started with PID = %d', $pid), $code, $previous);
    }
}
