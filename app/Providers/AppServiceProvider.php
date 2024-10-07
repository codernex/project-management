<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Gate::define('isProjectOwner', function (User $user, Project $project) {
            if ($user->id !== $project->owner_id) {
                return false;
            }
            return true;
        });

        \Gate::define('isMember', function (User $user, Project $project) {
            return $project->users()->where('id', $user->id)->exists();
        });

        \Gate::define('has-access-to-project', function (User $user, Project $project) {
            $isOwner = \Gate::check('isProjectOwner', $project);
            $isMember = \Gate::check('isMember', $project);
            return $isOwner || $isMember;
        });

        \Gate::define('task-owner', function (User $user, Task $task) {
            return $task->owner->id === $user->id;
        });

        \Gate::define('has-access-to-task', function (User $user, Task $task) {
            $taskOwner = \Gate::check('task-owner', $task);
            $isMember = $task->users()->where('id', $user->id)->exists();

            return $taskOwner || $isMember;
        });
    }
}
