<?php

namespace Tests\Feature\Console\Commands;

use App\Console\Commands\BackupDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;
use Illuminate\Support\Facades\File;

class BackupDatabaseTest extends TestCase
{
    public function test_fails_if_not_mysql()
    {
        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite', ['driver' => 'sqlite']);

        $this->artisan('db:backup')
            ->expectsOutput('Chỉ hỗ trợ backup MySQL. Driver hiện tại: sqlite')
            ->assertExitCode(1);
    }

    public function test_fails_if_mysqldump_not_found()
    {
        Config::set('database.default', 'mysql');
        Config::set('database.connections.mysql', ['driver' => 'mysql', 'database' => 'test']);

        $command = $this->getMockBuilder(BackupDatabase::class)
            ->onlyMethods(['resolveMysqldumpBinary'])
            ->getMock();
        $command->method('resolveMysqldumpBinary')->willReturn(null);

        $this->app->instance(BackupDatabase::class, $command);

        $this->artisan('db:backup')
            ->expectsOutput('Không tìm thấy mysqldump. Đặt MYSQLDUMP_PATH trong .env hoặc cài MySQL/XAMPP.')
            ->assertExitCode(1);
    }

    public function test_fails_if_mkdir_fails()
    {
        Config::set('database.default', 'mysql');
        Config::set('database.connections.mysql', ['driver' => 'mysql', 'database' => 'test']);

        $command = $this->getMockBuilder(BackupDatabase::class)
            ->onlyMethods(['resolveMysqldumpBinary'])
            ->getMock();
        $command->method('resolveMysqldumpBinary')->willReturn('mysqldump');

        $this->app->instance(BackupDatabase::class, $command);

        // Windows invalid path chars, or just an impossible path
        $path = PHP_OS_FAMILY === 'Windows' ? 'Z:\\*?\\invalid\\file.sql' : '/root/forbidden/file.sql';

        $this->artisan('db:backup', ['--path' => $path])
            ->expectsOutput("Không tạo được thư mục: " . dirname($path))
            ->assertExitCode(1);
    }

    public function test_backup_fails_if_process_fails()
    {
        Config::set('database.default', 'mysql');
        Config::set('database.connections.mysql', ['driver' => 'mysql', 'database' => 'test']);

        $command = $this->getMockBuilder(BackupDatabase::class)
            ->onlyMethods(['resolveMysqldumpBinary'])
            ->getMock();
        $command->method('resolveMysqldumpBinary')->willReturn('mysqldump');

        $this->app->instance(BackupDatabase::class, $command);

        Process::fake([
            '*' => Process::result('error', 'Something went wrong', 1)
        ]);

        $path = storage_path('app/test_backup.sql');

        $this->artisan('db:backup', ['--path' => $path])
            ->expectsOutputToContain('Backup thất bại:')
            ->assertExitCode(1);
    }

    public function test_backup_fails_if_file_empty()
    {
        Config::set('database.default', 'mysql');
        Config::set('database.connections.mysql', ['driver' => 'mysql', 'database' => 'test']);

        $command = $this->getMockBuilder(BackupDatabase::class)
            ->onlyMethods(['resolveMysqldumpBinary'])
            ->getMock();
        $command->method('resolveMysqldumpBinary')->willReturn('mysqldump');

        $this->app->instance(BackupDatabase::class, $command);

        Process::fake([
            '*' => Process::result('success', '', 0)
        ]);

        $path = storage_path('app/test_backup_empty.sql');
        if (file_exists($path)) {
            unlink($path);
        }
        touch($path); // Create empty file

        $this->artisan('db:backup', ['--path' => $path])
            ->expectsOutput('Backup tạo ra file rỗng: ' . $path)
            ->assertExitCode(1);
            
        unlink($path);
    }

    public function test_backup_succeeds()
    {
        Config::set('database.default', 'mysql');
        Config::set('database.connections.mysql', ['driver' => 'mysql', 'database' => 'test']);

        $command = $this->getMockBuilder(BackupDatabase::class)
            ->onlyMethods(['resolveMysqldumpBinary'])
            ->getMock();
        $command->method('resolveMysqldumpBinary')->willReturn('mysqldump');

        $this->app->instance(BackupDatabase::class, $command);

        $path = storage_path('app/test_backup_success.sql');

        Process::fake([
            '*' => function () use ($path) {
                file_put_contents($path, 'DUMMY DATA');
                return Process::result('success', '', 0);
            }
        ]);

        $this->artisan('db:backup', ['--path' => $path])
            ->expectsOutput('Backup thành công: ' . $path . ' (' . round(10 / 1024, 1) . ' KB)')
            ->assertExitCode(0);
            
        unlink($path);
    }
}
