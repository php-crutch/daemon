<?php

declare(strict_types=1);

namespace Crutch\Daemon\Exception;

use Throwable;

final class FailToSendSignal extends DaemonException
{
    public function __construct(string $signal, int $pid, int $code = 1, Throwable $previous = null)
    {
        parent::__construct(sprintf('fail to send %s to process #%d', $signal, $pid), $code, $previous);
    }
}
