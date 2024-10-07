<?php

namespace App\Listeners;

use App\Mail\InviteUserToProject;
use Illuminate\Support\Facades\Mail;

class InviteTeamMember
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(\App\Events\InviteTeamMember $event): void
    {
        Mail::to($event->email)->send(new InviteUserToProject($event->invitationLink, $event->fromName));
    }
}
