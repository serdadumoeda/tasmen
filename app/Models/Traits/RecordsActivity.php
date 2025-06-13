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
        // ✅ PERBAIKAN UTAMA ADA DI SINI
        // Jika model ini adalah Project, pemiliknya adalah leader-nya sendiri.
        if (class_basename($this) === 'Project') {
            return $this->leader;
        }

        // Jika model ini adalah Task (atau lainnya), pemiliknya adalah leader dari project terkait.
        return $this->project->leader;
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