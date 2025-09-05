<?php

namespace App\Services;

class BreadcrumbService
{
    protected array $breadcrumbs = [];
    protected bool $showBackButton = true;

    public function add(string $title, ?string $url = null): void
    {
        $this->breadcrumbs[] = ['title' => $title, 'url' => $url];
    }

    public function get(): array
    {
        return $this->breadcrumbs;
    }

    public function hideBackButton(): void
    {
        $this->showBackButton = false;
    }

    public function getShowBackButton(): bool
    {
        return $this->showBackButton;
    }
}
