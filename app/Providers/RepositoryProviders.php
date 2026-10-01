<?php

namespace App\Providers;

use App\Interfaces\Project\ProjectRepositoryInterface;
use App\Interfaces\User\UserRepositoryInterface;
use App\Repositories\ProjectRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryProviders extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(ProjectRepositoryInterface::class, ProjectRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    }
}
