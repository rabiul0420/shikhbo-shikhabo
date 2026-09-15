<?php

// Run before the account migration. The dump stays in private, git-ignored storage.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
set_exception_handler(static function (Throwable $error): void {
    fwrite(STDERR, $error->getMessage().PHP_EOL);
    exit(1);
});

$connection = \Illuminate\Support\Facades\DB::connection()->getConfig();
\Illuminate\Support\Facades\DB::connection()->getPdo();
if ($connection['driver'] !== 'mysql') {
    throw new RuntimeException('This backup script expects MySQL.');
}
$directory = storage_path('app/private/backups');
if (! is_dir($directory)) {
    mkdir($directory, 0700, true);
}
$path = $directory.'/before-account-split-'.date('Ymd-His').'.sql';
$binary = $argv[1] ?? 'mysqldump';
$process = new \Symfony\Component\Process\Process([
    $binary, '--no-defaults', '--protocol=TCP', '--host='.$connection['host'], '--port='.$connection['port'],
    '--user='.$connection['username'], '--single-transaction', '--routines', '--triggers',
    '--result-file='.$path, $connection['database'],
], null, ['MYSQL_PWD' => $connection['password']]);
$process->setTimeout(120);
$process->mustRun();
if (! is_file($path) || filesize($path) < 100) {
    throw new RuntimeException('Database backup was empty.');
}
echo 'Database backup saved: '.$path.PHP_EOL;
