<?php

namespace Kopaing\SimpleLog\Trait;

use Illuminate\Support\Str;
use Kopaing\SimpleLog\Helpers\ActivityLogger;

trait SimpleLog
{
    /**
     * Boot the trait.
     *
     * This method registers a model event listener for creating, updating,
     * and deleting events, which automatically logs the activities.
     *
     * @return void
     */
    protected static function bootSimpleLog()
    {
        static::created(function ($model) {
            self::logActivity($model, 'Created');
        });

        static::updated(function ($model) {
            self::logActivity($model, 'Updated');
        });

        static::deleted(function ($model) {
            self::logActivity($model, 'Deleted');
        });
    }

    /**
     * Log the activity.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $action
     * @return void
     */
    protected static function logActivity($model, $action)
    {
        $logName = $model->getLogName();
        $description = $model->getLogDescription($action);
        $event = strtolower($action);
        $status = 'success';


        // Get custom loggable columns if defined in the model
        $loggableColumns = isset($model->loggable) ? $model->loggable : null;

        // Determine which columns to log
        $columnsToLog = $loggableColumns ?? $model->getFillable();


        // Initialize properties array
        $properties = [];

        if ($action === 'Created') {
            $properties['created_data'] =  $model->only($columnsToLog);
        }

        if ($action === 'Updated') {
            $properties['old'] = array_intersect_key($model->getOriginal(), array_flip($columnsToLog));
            $properties['new'] = array_intersect_key($model->getDirty(), array_flip($columnsToLog));
        }

        if ($action === 'Deleted') {
            $properties['deleted_data'] = $model->only($columnsToLog);
        }

        $activityLogger = new ActivityLogger($logName);
        $activityLogger->log($description)
                       ->event($event)
                       ->status($status)
                       ->properties($properties)
                       ->save();
    }


    /**
     * Get the log name for the model.
     *
     * @return string
     */
    protected function getLogName()
    {
        // Customize this method to return the desired log name for your model
        return "{$this->getTable()}";
    }

    /**
     * Get the log description for the model activity.
     *
     * @param string $action
     * @return string
     */
    protected function getLogDescription($action)
    {
        // Get the model's singular table name (e.g., "task" from "tasks")
        $modelName = ucfirst(Str::singular($this->getTable()));

        // Generate a descriptive log message based on the action
        switch ($action) {
            case 'Created':
                return "{$modelName} record was created";
            case 'Updated':
                return "{$modelName} record was updated";
            case 'Deleted':
                return "{$modelName} record was deleted";
            default:
                return "{$modelName} record was {$action}";
        }
    }

}
