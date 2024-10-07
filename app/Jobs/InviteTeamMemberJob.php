<?php

namespace App\Jobs;

use App\Mail\InviteUserToProject;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class InviteTeamMemberJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $email, public string $invitationLink, public string $fromName)
    {
        Mail::to($this->email)->send(new InviteUserToProject($this->invitationLink, $this->fromName));
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

    }

    public function failed(\Exception $exception): void
    {
        // Log failure or perform other actions
        \Log::error('Job failed: ' . $exception->getMessage());
    }
}
