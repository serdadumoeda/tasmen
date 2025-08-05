<?php

namespace App\Observers;

use App\Models\SubTask;

class SubTaskObserver
{
    // Method ini akan dipanggil setelah sub-task dibuat, diperbarui, atau dihapus
    protected function recalculate(SubTask $subTask)
    {
        $subTask->task->recalculateProgress();
    }

    public function created(SubTask $subTask): void
    {
        $this->recalculate($subTask);
    }

    public function updated(SubTask $subTask): void
    {
        $this->recalculate($subTask);
    }

    public function deleted(SubTask $subTask): void
    {
        $this->recalculate($subTask);
    }
}