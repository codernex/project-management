<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Livewire\Volt\Volt;

Volt::route('/login', 'auth.login')->name('login');

Route::group(['middleware' => 'auth:web'], function () {
    Volt::route('/', 'dashboard');
    Volt::route('/projects', 'projects.index');
    Volt::route('/projects/{project}', 'project.show')->name('project.show');
    Route::get('/logout', function () {
        Auth::logout();
        return redirect('/login');
    })->name('logout');
});

Route::get('accept-invitation/{project}', function (Request $request, Project $project) {
    // Ensure 'invitee' parameter is provided
    if (!$request->has('invitee')) {
        abort(400, 'Invitation link malformed');
    }

    // Find the user by email
    $inviteeEmail = $request->query('invitee');
    $user = User::where('email', $inviteeEmail)->first();


    // If user exists, redirect to login if not authenticated
    if ($user) {

        /**
         * Is Onwner of this project
         */
        if ($user->id === $project->owner->id) {
            return redirect()->route('projects.show', $project);
        }

        /**
         * If user exist to this project
         */
        $isUserExist = $project->users->where('email', $user->email)->first();
        if ($isUserExist) {
            return redirect()->route('project.show', ['project' => $project]);
        }
        $project->users()->attach($user->id);

        /**
         * If user isn't logged in
         */
        if (!auth()->check()) {
            // Laravel will store the intended URL automatically
            return redirect()->guest(route('login'));
        }
        // User is logged in, continue to show the project
        return redirect()->route('project.show', ['project' => $project]);
    } else {
        return response('Ask your CEO to create an account for you', 403);
    }
});


