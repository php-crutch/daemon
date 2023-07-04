# crutch/daemon

Daemon helper

# Install

```shell
composer require crutch/daemon:^1.0
```

# Usage

Create worker runner

```php
#!/usr/bin/env php
<?php
// crutch_worker_demo.php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

$worker = function () {
    while (true) {
        sleep(1);
    }
};

$pidFile = '/tmp/crutch-daemon.pid';

$daemon = new \Crutch\Daemon\Daemon($pidFile);

$action = $argv[1] ?? '';
$num = max(1, intval($argv[2] ?? '1'));


switch ($action) {
    case 'start':
        try {
            $daemon->start($worker, $num);
        } catch (\Crutch\Daemon\Exception\DaemonException $exception) {
            printf("Could not start daemon: %s\n", $exception->getMessage());
            exit($exception->getCode());
        }
        break;
    case 'listen':
        $daemon->listen($worker);
        break;
    case 'stop':
        try {
            $daemon->stop();
        } catch (\Crutch\Daemon\Exception\DaemonException $exception) {
            printf("Could not stop daemon: %s\n", $exception->getMessage());
            exit($exception->getCode());
        }
        break;
    default:
        print("unknown action\n");
        exit(127);
}

```

Run it

```shell
php ./crutch_worker_demo.php start 3
```

Check processes
```shell
ps aux | grep crutch_worker_demo
```

```text
user       58576  0.0  0.0 140080 11624 ?        Ss   17:58   0:00 php ./crutch_worker_demo.php start
user       58577  0.0  0.0 140080  9584 ?        S    17:58   0:00 php ./crutch_worker_demo.php start
user       58578  0.0  0.0 140080  9584 ?        S    17:58   0:00 php ./crutch_worker_demo.php start
user       58579  0.0  0.0 140080  9584 ?        S    17:58   0:00 php ./crutch_worker_demo.php start
user       58580  0.0  0.0   9116  2488 pts/3    S+   17:58   0:00 grep --color=auto crutch_worker_demo
```

You see 1 parent process (PID=58576) and 3 child processes (PIDs=58577, 58578 and 58579).
Process 58580 is a grep, ignore it.

Stop process


```shell
php ./crutch_worker_demo.php stop
```

Check processes
```shell
ps aux | grep crutch_worker_demo
```

```text
user       59825  0.0  0.0   9116  2488 pts/3    S+   17:58   0:00 grep --color=auto crutch_worker_demo
```

All processes has been killed.