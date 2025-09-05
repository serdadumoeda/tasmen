<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use App\Services\PageTitleService;
use App\Services\NotificationService;

class PageTitleComposer
{
    protected $pageTitleService;
    protected $notificationService;

    public function __construct(PageTitleService $pageTitleService, NotificationService $notificationService)
    {
        $this->pageTitleService = $pageTitleService;
        $this->notificationService = $notificationService;
    }

    public function compose(View $view)
    {
        $this->pageTitleService->setNotificationCount($this->notificationService->getUnreadCount());
        $view->with('pageTitle', $this->pageTitleService->getFullTitle(config('app.name', 'Tasmen')));
    }
}
