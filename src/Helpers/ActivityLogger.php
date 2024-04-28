<?php

namespace Kopaing\SimpleLog\Helpers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Kopaing\SimpleLog\Models\ActivityLog;


class ActivityLogger
{
    protected $event;
    protected $description;
    protected $log_name;
    protected $status;
    protected $data;

    public function __construct($log_name = null)
    {
        $this->log_name = $log_name;
    }


    /**
     * Get the default log name from the configuration.
     *
     * @return string
     */
    public static function getDefaultLogName()
    {
        return Config::get('log.log_name', 'default');
    }

    public function log($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Set the event type.
     *
     * @param string $event The event type (create, update, delete, restore, login, logout, 'import', 'export', 'upload','download').
     * @return $this
     */
    public function event($event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * Set the status of the operation.
     *
     * @param string $status The status of the operation (success, warn, fail).
     * @return $this
     */
    public function status($status)
    {
        $this->status = $status;
        return $this;
    }

    public function properties($data)
    {
        $this->data = $data;
        return $this;
    }

    public function autoEvent()
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

        if (isset($trace[1]['function'])) {
            $methodName = $trace[1]['function'];

            if (in_array($methodName, ['store', 'update', 'destroy'])) {
                $this->event($methodName);
            }
        }

        return $this;
    }

    public function save()
    {
        return ActivityLog::create([
            'log_name' => $this->log_name ?: static::getDefaultLogName(),
            'description' => $this->description,
            'event' => $this->event,
            'status' => $this->status,
            'properties' => json_encode($this->data),
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);
    }

    public function update($id)
    {
        $activityLog = ActivityLog::findOrFail($id);

        $activityLog->update([
            'log_name' => $this->log_name ?? $activityLog->log_name,
            'description' => $this->description ?? $activityLog->description,
            'event' => $this->event ?? $activityLog->event,
            'status' => $this->status ?? $activityLog->status,
            'properties' => $this->data ? json_encode($this->data) : $activityLog->properties,
            'created_by' => Auth::id() ?? $activityLog->created_by,
            'updated_by' => Auth::id() ?? $activityLog->updated_by,
        ]);
    }


    /**
     * Retrieve all log entries.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        $query = ActivityLog::query();

        if ($this->log_name !== null) {
            $query->where('log_name', $this->log_name);
        }

        return $query->get();
    }

    /**
     * Retrieve the last log entry.
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function last()
    {
        $query = ActivityLog::latest();

        if ($this->log_name !== null) {
            $query->where('log_name', $this->log_name);
        }

        return $query->first();
    }


   /**
     * Purge old activity logs.
     *
     * @return int The exit code returned by the Artisan command
     */
    public function purgeOldLogs()
    {
        // Call the 'simplelog:purge' command using Artisan
        return Artisan::call('simplelog:purge');
    }

}
