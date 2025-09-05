<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use App\Services\BreadcrumbService;

class BreadcrumbComposer
{
    protected $breadcrumbService;

    public function __construct(BreadcrumbService $breadcrumbService)
    {
        $this->breadcrumbService = $breadcrumbService;
    }

    public function compose(View $view)
    {
        $view->with('breadcrumbs', $this->breadcrumbService->get());
    }
}
