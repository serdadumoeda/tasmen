<?php

namespace App\Services;

class PageTitleService
{
    protected string $title = '';
    protected ?int $notificationCount = null;

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function setNotificationCount(int $count): void
    {
        $this->notificationCount = $count;
    }

    public function getFullTitle(string $appName = 'Tasmen'): string
    {
        $fullTitle = '';

        if ($this->notificationCount !== null && $this->notificationCount > 0) {
            $fullTitle .= '(' . $this->notificationCount . ') ';
        }

        if (!empty($this->title)) {
            $fullTitle .= $this->title . ' - ' . $appName;
        } else {
            $fullTitle .= $appName;
        }

        return $fullTitle;
    }
}
