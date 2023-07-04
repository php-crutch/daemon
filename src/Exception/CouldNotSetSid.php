<?php

declare(strict_types=1);

namespace Crutch\Daemon\Exception;

use Throwable;

final class CouldNotSetSid extends DaemonException
{
    public function __construct(int $code = 1, Throwable $previous = null)
    {
        parent::__construct('could not set SID' , $code, $previous);
    }
}
