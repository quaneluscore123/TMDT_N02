<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Process;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup {--path= : Đường dẫn file .sql tùy chỉnh}';

    protected $description = 'Sao lưu cơ sở dữ liệu MySQL ra file .sql trong storage/app/backups';

    public function handle(): int
    {
        $default = (string) Config::get('database.default');
        $db = Config::get("database.connections.{$default}");

        if (($db['driver'] ?? '') !== 'mysql') {
            $this->error('Chỉ hỗ trợ backup MySQL. Driver hiện tại: '.($db['driver'] ?? 'unknown'));

            return self::FAILURE;
        }

        $mysqldump = $this->resolveMysqldumpBinary();

        if ($mysqldump === null) {
            $this->error('Không tìm thấy mysqldump. Đặt MYSQLDUMP_PATH trong .env hoặc cài MySQL/XAMPP.');

            return self::FAILURE;
        }

        $path = $this->option('path')
            ?: storage_path('app/backups/database_'.date('Y-m-d_His').'.sql');

        $dir = dirname($path);
        if (! is_dir($dir) && ! @mkdir($dir, 0755, true) && ! is_dir($dir)) {
            $this->error("Không tạo được thư mục: {$dir}");

            return self::FAILURE;
        }

        $host = $db['host'] ?? '127.0.0.1';
        $port = $db['port'] ?? '3306';
        $username = $db['username'] ?? 'root';
        $password = (string) ($db['password'] ?? '');
        $database = $db['database'] ?? '';

        // Password truyền qua env MYSQL_PWD (không hiện trên process list)
        $cmd = sprintf(
            '"%s" -h%s -P%s -u%s --single-transaction --routines --triggers --events %s > "%s"',
            $mysqldump,
            $host,
            $port,
            $username,
            escapeshellarg($database),
            $path
        );

        $result = Process::timeout(300)
            ->env(['MYSQL_PWD' => $password])
            ->run($cmd);

        if ($result->failed()) {
            $this->error('Backup thất bại: '.$result->errorOutput());
            return self::FAILURE;
        }

        if (! is_file($path) || filesize($path) === 0) {
            $this->error('Backup tạo ra file rỗng: '.$path);

            return self::FAILURE;
        }

        $this->info('Backup thành công: '.$path.' ('.round(filesize($path) / 1024, 1).' KB)');

        return self::SUCCESS;
    }

    protected function resolveMysqldumpBinary(): ?string
    {
        $candidates = array_filter([
            env('MYSQLDUMP_PATH'),
            'D:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            'mysqldump',
        ]);

        foreach ($candidates as $binary) {
            if ($binary === 'mysqldump' || str_contains($binary, DIRECTORY_SEPARATOR) || str_contains($binary, '/')) {
                if (is_file($binary) && is_executable($binary)) {
                    return $binary;
                }

                if ($binary === 'mysqldump') {
                    $result = Process::timeout(5)->run('mysqldump --version');

                    if ($result->successful()) {
                        return 'mysqldump';
                    }
                }

                continue;
            }

            if (is_file($binary)) {
                return $binary;
            }
        }

        return null;
    }
}
