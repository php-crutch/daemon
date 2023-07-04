<?php

declare(strict_types=1);

namespace Crutch\Daemon;

use Crutch\Daemon\Exception\CouldNotForkProcess;
use Crutch\Daemon\Exception\CouldNotSetSid;
use Crutch\Daemon\Exception\CouldNotWritePid;
use Crutch\Daemon\Exception\DaemonAlreadyStarted;
use Crutch\Daemon\Exception\FailToSendSignal;
use Crutch\Daemon\Exception\InvalidPid;
use Crutch\Daemon\Exception\InvalidPidFilePath;
use Crutch\Daemon\Exception\PidFileIsNotReadable;

final class Daemon
{

    private string $pidFile;
    private bool $isStopped = false;
    private array $workers = [];

    public function __construct(string $pidFile)
    {
        $pidFile = trim($pidFile);
        if ($pidFile === '') {
            throw new InvalidPidFilePath($pidFile);
        }
        $this->pidFile = trim($pidFile);
    }

    /**
     * @param callable(): void $worker
     * @param int $quantity
     * @return int
     * @throws CouldNotForkProcess
     * @throws CouldNotSetSid
     * @throws CouldNotWritePid
     * @throws DaemonAlreadyStarted
     */
    public function start(callable $worker, int $quantity): int
    {
        $this->checkPidFile();
        $this->detachMainProcess();
        $this->runWorkers($worker, $quantity);
        $this->registerSignalHandlers();
        $this->run();
        $this->finalize();

        return 0;
    }

    public function listen(callable $worker): void
    {
        $worker();
    }

    /**
     * @return void
     * @throws FailToSendSignal
     * @throws InvalidPid
     * @throws PidFileIsNotReadable
     */
    public function stop(): void
    {
        if (!is_file($this->pidFile)) {
            return;
        }

        if (!is_readable($this->pidFile)) {
            throw new PidFileIsNotReadable($this->pidFile, 1);
        }

        $pidRaw = trim(file_get_contents($this->pidFile));
        $pid = (int)$pidRaw;

        if ($pid < 1) {
            throw new InvalidPid($pidRaw, $this->pidFile, 2);
        }

        if (!posix_kill((-1) * $pid, SIGTERM)) {
            throw new FailToSendSignal('SIGTERM', $pid, 2);
        }

        while (posix_kill($pid, 0)) {
            usleep(500000);
        }
    }

    /**
     * @return void
     * @throws DaemonAlreadyStarted
     */
    private function checkPidFile(): void
    {
        if (!file_exists($this->pidFile)) {
            return;
        }

        $pid = (int)file_get_contents($this->pidFile);

        if ($pid && posix_kill($pid, 0)) {
            throw new DaemonAlreadyStarted($pid);
        }
    }

    /**
     * @return void
     * @throws CouldNotForkProcess
     * @throws CouldNotSetSid
     * @throws CouldNotWritePid
     */
    private function detachMainProcess(): void
    {
        $pid = pcntl_fork();

        if ($pid > 0) {
            exit(0);
        }

        if ($pid == -1) {
            throw new CouldNotForkProcess(1);
        }

        $sid = posix_setsid();

        if ($sid == -1) {
            throw new CouldNotSetSid(2);
        }

        $errorReporting = error_reporting();
        error_reporting(0);
        if (!file_put_contents($this->pidFile, posix_getpid())) {
            throw new CouldNotWritePid($this->pidFile, 3);
        }
        error_reporting($errorReporting);

        fclose(STDIN);
        fclose(STDOUT);
        fclose(STDERR);
    }

    private function runWorkers(callable $worker, int $workers): void
    {
        for ($i = 0; $i < $workers; ++$i) {
            $this->runWorker($worker);
        }
    }

    private function runWorker(callable $worker): void
    {
        $pid = pcntl_fork();
        if ($pid != 0) {
            $this->workers[$pid] = true;
            return;
        }

        exit($worker());
    }

    private function registerSignalHandlers(): void
    {
        $stop = function () {
            $this->isStopped = true;
        };

        $child = function () {
            while (($pid = pcntl_wait($status, WNOHANG)) > 0) {
                unset($this->workers[$pid]);
            }
        };

        pcntl_async_signals(true);
        pcntl_signal(SIGTERM, $stop);
        pcntl_signal(SIGINT, $stop);
        pcntl_signal(SIGCHLD, $child);
    }

    private function run(): void
    {
        while (!$this->isStopped) {
            usleep(1000);
        }
    }

    private function finalize(): void
    {
        while (count($this->workers)) {
            usleep(1000);
        }

        if (file_exists($this->pidFile)) {
            unlink($this->pidFile);
        }
    }
}
