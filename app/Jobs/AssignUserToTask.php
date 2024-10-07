<?php

namespace App\Jobs;

use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class AssignUserToTask implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public User $user, public Task $task)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Mail::to($this->user->email)->send(new \App\Mail\AssignedUserToTask($this->task, $this->user));
    }

    public function failed(\Exception $exception): void
    {
        // Log failure or perform other actions
        \Log::error('Job failed: ' . $exception->getMessage());
    }
}
