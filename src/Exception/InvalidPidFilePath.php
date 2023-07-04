<?php

declare(strict_types=1);

namespace Crutch\Daemon\Exception;

use InvalidArgumentException;
use Throwable;

final class InvalidPidFilePath extends InvalidArgumentException
{
    public function __construct(string $pidFile, int $code = 1, Throwable $previous = null)
    {
        parent::__construct(sprintf('empty PID file path %s', $pidFile), $code, $previous);
    }
}
