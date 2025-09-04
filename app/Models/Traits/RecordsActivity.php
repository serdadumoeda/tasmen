<?php

namespace App\Models\Traits;

use App\Models\Activity;
use Illuminate\Support\Arr;

trait RecordsActivity
{
    public array $oldAttributes = [];

    public static function bootRecordsActivity()
    {
        foreach (static::recordableEvents() as $event) {
            static::$event(function ($model) use ($event) {
                $model->recordActivity($model->activityDescription($event));
            });

            if ($event === 'updated') {
                static::updating(function ($model) {
                    $model->oldAttributes = $model->getOriginal();
                });
            }
        }
    }

    protected function activityDescription($description): string
    {
        return "{$description}_" . strtolower(class_basename($this));
    }

    protected static function recordableEvents(): array
    {
        if (isset(static::$recordableEvents)) {
            return static::$recordableEvents;
        }
        return ['created', 'updated', 'deleting'];
    }

    public function recordActivity($description)
    {
        $owner = $this->activityOwner();

        $this->activity()->create([
            'user_id' => auth()->id() ?? ($owner ? $owner->id : null),
            'description' => $description,
            'project_id' => class_basename($this) === 'Project' ? $this->id : $this->project_id,
            'before' => $this->getActivityChanges('before'),
            'after' => $this->getActivityChanges('after')
        ]);
    }

    public function activity()
    {
        return $this->morphMany(Activity::class, 'subject')->latest();
    }

    protected function activityOwner()
    {
        // If the model is a Project, its owner is the leader.
        if (class_basename($this) === 'Project') {
            return $this->leader;
        }
    
        // If the model has a project_id (like a Task), get the project's leader.
        // This is more robust than checking `isset($this->project)` because the relationship
        // may not be loaded during model events like 'created'.
        if (isset($this->project_id)) {
            // Use relationLoaded to prevent N+1 queries if the relation is already there.
            $project = $this->relationLoaded('project') ? $this->project : \App\Models\Project::find($this->project_id);
            if ($project) {
                return $project->leader;
            }
        }
    
        // Fallback for models without a project (e.g., ad-hoc tasks, User model itself).
        // The activity owner is the currently authenticated user.
        // If there's no logged-in user (e.g., during seeding), this will return null.
        return auth()->user();
    }

    public function getActivityChanges($type)
    {
        if ($type === 'after') {
            $changed = $this->getDirty();
            if (empty($this->oldAttributes)) {
                return $changed;
            }
            return Arr::except($changed, ['updated_at']);
        }

        if ($type === 'before' && !empty($this->oldAttributes)) {
            return Arr::only(
                $this->oldAttributes,
                array_keys($this->getDirty())
            );
        }

        return null;
    }
}