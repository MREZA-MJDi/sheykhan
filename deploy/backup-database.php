<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$connectionName = (string) config('database.default');
$connection = (array) config("database.connections.{$connectionName}", []);
$driver = (string) ($connection['driver'] ?? '');

$backupDirectory = storage_path('app/private/deploy-backups');
if (! is_dir($backupDirectory) && ! mkdir($backupDirectory, 0700, true) && ! is_dir($backupDirectory)) {
    fwrite(STDERR, "Unable to create private backup directory.\n");
    exit(1);
}
@chmod($backupDirectory, 0700);

$timestamp = now()->format('Ymd-His');
$database = preg_replace('/[^A-Za-z0-9_.-]/', '_', (string) ($connection['database'] ?? 'database'));
$backupPath = $backupDirectory . DIRECTORY_SEPARATOR . "{$database}-{$timestamp}";

if ($driver === 'sqlite') {
    $source = (string) ($connection['database'] ?? '');
    if ($source === ':memory:' || $source === '' || ! is_file($source)) {
        fwrite(STDERR, "SQLite backup requires a file-based database; no migrations were run.\n");
        exit(1);
    }

    if (! copy($source, $backupPath . '.sqlite')) {
        fwrite(STDERR, "Could not copy SQLite database.\n");
        exit(1);
    }

    @chmod($backupPath . '.sqlite', 0600);
    fwrite(STDOUT, "Database backup created: {$backupPath}.sqlite\n");
    exit(0);
}

if ($driver !== 'mysql') {
    fwrite(STDERR, "Unsupported database driver '{$driver}'; refusing to deploy without a backup.\n");
    exit(1);
}

$binary = trim((string) env('MYSQLDUMP_BINARY', 'mysqldump'));
$dumpPath = $backupPath . '.sql';
$arguments = [
    $binary,
    '--single-transaction',
    '--quick',
    '--routines',
    '--triggers',
    '--events',
    '--default-character-set=utf8mb4',
    '--host=' . (string) ($connection['host'] ?? '127.0.0.1'),
    '--port=' . (string) ($connection['port'] ?? '3306'),
    '--user=' . (string) ($connection['username'] ?? ''),
    '--result-file=' . $dumpPath,
    (string) ($connection['database'] ?? ''),
];

try {
    $process = new Process($arguments, dirname(__DIR__), [
        'MYSQL_PWD' => (string) ($connection['password'] ?? ''),
    ]);
    $process->setTimeout(600);
    $process->mustRun();
    if (! is_file($dumpPath) || filesize($dumpPath) < 128) {
        throw new RuntimeException('Database dump file is missing or unexpectedly small.');
    }

    @chmod($dumpPath, 0600);
    fwrite(STDOUT, "Database backup created: {$dumpPath}\n");
} catch (Throwable $exception) {
    @unlink($dumpPath);
    fwrite(STDERR, "Database backup failed: {$exception->getMessage()}\n");
    exit(1);
}
