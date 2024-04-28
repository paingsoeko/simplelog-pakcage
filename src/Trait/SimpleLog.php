<?php

namespace Kopaing\SimpleLog\Trait;

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

       // Initialize properties array
        $properties = [];

        if ($action === 'Created') {
            $properties['created_data'] = $model->toArray();
        }
    
        if ($action === 'Updated') {
            $properties['old'] = $model->getOriginal(); 
            $properties['new'] = $model->getDirty(); 
        }

        if ($action === 'Deleted') {
            $properties['deleted_data'] = $model->toArray();
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
        return 'default';
    }

    /**
     * Get the log description for the model activity.
     *
     * @param string $action
     * @return string
     */
    protected function getLogDescription($action)
    {
        // Customize this method to return the desired log description for your model and action
        return "{$action} a record";
    }
}