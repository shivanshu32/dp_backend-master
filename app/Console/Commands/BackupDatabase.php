<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;


class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:printing';
    protected $process;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup Database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        // $today = today()->format('Y-m-d');
        // if(!is_dir(storage_path('backups'))) {
        //     mkdir(storage_path('backups'), 777);
        // }

        // $this->process= new Process(sprintf(
        //     'mysqldump --compact --skip-comments -u%s -p%s %s > %s',
        //     config('database.connections.mysql.username'),
        //     config('database.connections.mysql.password'),
        //     config('database.connections.mysql.database'),
        //     storage_path("backups/{$today}.sql")
        // ));
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // try {
        //     $this->process->mustRun();
        //     $today = today()->format('Y-m-d');
        //     $file = storage_path("backups/{$today}.sql");
        //     $p = Storage::disk('s3')->put("backups/{$today}.sql", file_get_contents($file), 'public');
        //     Log::info('Daily DB Backup - Success');
        // } catch (ProcessFailedException $exception) {
        //     Log::error('Daily backup error - Failed', (array)$exception);
        // }
    }
}
