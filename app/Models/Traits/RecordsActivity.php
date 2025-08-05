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
        return ['created', 'updated', 'deleted'];
    }

    public function recordActivity($description)
    {
        $this->activity()->create([
            'user_id' => auth()->id() ?? $this->activityOwner()->id,
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
        // Jika model ini adalah Project, pemiliknya adalah leader-nya sendiri.
        if (class_basename($this) === 'Project') {
            return $this->leader;
        }
    
        // PERBAIKAN: Jika tugasnya ada proyek, pemiliknya adalah leader proyek
        if (isset($this->project)) {
            return $this->project->leader;
        }
    
        // Jika tidak ada proyek (misal: tugas ad-hoc atau model User),
        // pemilik aktivitas adalah user yang sedang login atau user itu sendiri.
        return auth()->user() ?? $this;
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