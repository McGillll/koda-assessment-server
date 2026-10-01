<?php

namespace App\Providers;

use App\Interfaces\Auth\AuthServiceInterface;
use App\Interfaces\Project\ProjectServiceInterface;
use App\Services\AuthService;
use App\Services\ProjectService;
use Illuminate\Support\ServiceProvider;

class ServiceProviders extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(ProjectServiceInterface::class, ProjectService::class);
    }
}
