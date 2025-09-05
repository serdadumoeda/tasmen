<?php

namespace App\Services;

class BreadcrumbService
{
    protected array $breadcrumbs = [];

    public function add(string $title, ?string $url = null): void
    {
        $this->breadcrumbs[] = ['title' => $title, 'url' => $url];
    }

    public function get(): array
    {
        return $this->breadcrumbs;
    }
}
