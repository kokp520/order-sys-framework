<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CacheClear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clearall';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '清除所有快取';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info('Clearing cache...');

            // 使用 file 驅動清除快取
            config(['cache.default' => 'file']);
            $this->call('cache:clear');

            $this->call('config:clear');
            $this->call('view:clear');
            $this->call('route:clear');

            $this->info('All cache cleared successfully!');
        } catch (\Exception $e) {
            $this->error('Error clearing cache: ' . $e->getMessage());
        }
    }
}
