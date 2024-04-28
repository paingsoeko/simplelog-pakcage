<?php

namespace Kopaing\SimpleLog\Console;


use Illuminate\Console\Command;
use Carbon\Carbon;
use Kopaing\SimpleLog\Models\ActivityLog;
use Illuminate\Support\Facades\Config;

class PurgeOldLogs extends Command
{
    protected $signature = 'simplelog:purge';
    protected $description = 'Delete old log entries from the activity log';

    public function handle()
    {

        // Get retention period from configuration
        $retentionUnit = Config::get('log.retention_period.unit', 'year');
        $retentionValue = Config::get('log.retention_period.value', 1);
    
        // Calculate the date based on the retention period
        $oneMonthAgo = Carbon::now()->subMonths($retentionUnit === 'year' ? $retentionValue * 12 : $retentionValue);

        // Delete log entries older than one month
        ActivityLog::where('created_at', '<', $oneMonthAgo)->delete();

        $this->info('Old log entries have been deleted.');
    }
}
