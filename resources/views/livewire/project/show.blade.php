<?php

use App\Jobs\InviteTeamMemberJob;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Livewire\Volt\Component;

new class extends Component {
    use \Mary\Traits\Toast;

    // Properties
    public Project $project;
    public string $name;
    public string $priority;
    public string $due_date;
    public bool $open;
    public mixed $users;
    public int $userId;
    public Task $task;
    public bool $taskModal;
    public bool $drawer;
    public string $email;
    public bool $discussion;
    public Comment $commentData;
    public string $github_url;

    public string $comment;
    public array $usersArr = [
        [
            'id' => 0,
            'name' => 'Select User'
        ]
    ];
    public User $assignedUser;
    public array $options = [
        ['id' => 'pri', 'name' => 'Select Priority'],
        ['id' => 'high', 'name' => 'High'],
        ['id' => 'medium', 'name' => 'Medium'],
        ['id' => 'low', 'name' => 'Low']
    ];

    /**
     * Publish Comment
     */

    public function publishComment(): void
    {
        $comment = new \App\Models\Comment(
            [
                'comment' => $this->comment,
                'user_id' => auth()->user()->id
            ]
        );
        $this->project->comments()->save($comment);
        $this->success('New comment published');
    }

    /**
     * @return void
     * Livewire Mount
     */
    public function mount(Project $project): void
    {
        $this->project = $project;
        $this->users = $project->users;
        $this->usersArr = array_merge($this->usersArr, $this->users->toArray());
    }

    /**
     * Task Modal Open
     */
    public function openTaskModal(Task $task): void
    {
        $this->taskModal = true;
        $this->task = $task;
    }

    /**
     * Invite Team Member to the project
     */
    public function inviteMember()
    {
        $data = $this->validate([
            'email' => 'required|email'
        ]);

        $userAlreadyExist = $this->project->users()->where('email', $data['email'])->exists();

        if ($userAlreadyExist) {
            $this->email = '';
            return $this->error('User already exists on this project');
        }

        $invitationLink = "http://localhost:8000/accept-invitation/{$this->project->id}?invitee={$this->email}";
        InviteTeamMemberJob::dispatch($data['email'], $invitationLink, auth()->user()->name);
        $this->email = '';
        $this->success('Invitation sent');
    }

    // Assigning user to the task
    public function assignUserToTask(): void
    {

        $this->validate(
            [
                'userId' => 'required'
            ], [
                'userId' => 'Please select an user'
            ]
        );
        $user = \App\Models\User::where('id', $this->userId)->first();
        $this->task->users()->attach($this->userId);
        $this->taskModal = false;
        $this->success("User assigned to this task");
        /**
         * Creating a job to the queue process email in background
         */
        \App\Jobs\AssignUserToTask::dispatch($user, $this->task);
    }

    // Creating new task to the project
    public function createNewTask(): void
    {
        $data = $this->validate(
            [
                'name' => 'string|required',
                'priority' => 'string|required',
                'due_date' => 'string|required',
                'github_url' => 'url|required'
            ]
        );

        $task = new Task();
        $task->name = $data['name'];
        $task->priority = $data['priority'];
        $task->due_date = $data['due_date'];
        $task->github_url = $data['github_url'];
        $task->owner_id = Auth::user()->id;
        $task->project_id = $this->project->id;
        $task->save();
        $this->success('New task created');
        $this->open = false;

    }

    /**
     * Lifecycle rendering hook
     * Checking if user has access to the project
     */
    public function rendering()
    {
        $hasAccess = \Gate::check('has-access-to-project', $this->project);

        if (!$hasAccess) {
            abort(403, 'You don\'t have permission to access this project');
        }
    }

}; ?>
<div>
    {{--    Page Header--}}
    <x-header :title="$project->title" separator progress-indicator>
        <x-slot:actions>
            @can('isProjectOwner',$project)
                <x-button @click="$wire.open=true" label="Create new Task" class="btn-neutral"/>
                <x-button label="Invite Team Member" @click="$wire.drawer=true"/>
                <x-button label="Project Discussion" @click="$wire.discussion=true"/>
            @endcan
        </x-slot:actions>
    </x-header>

    {{--    Create task modal--}}
    <x-modal wire:model="open" title="Create new Task">
        <x-form wire:submit="createNewTask" method="post">
            <x-input required label="Task name" wire:model="name" placeholder="Github issue #11"/>
            <x-input wire:model="github_url" required label="Github URL"
                     placeholder="https://github.com/microsoft/vscode/issues/230703"/>
            <x-select required wire:model="priority" label="Select Priority" :options="$options"/>
            <x-datetime required label="Due Date" wire:model="due_date" icon="o-calendar" type="datetime-local"/>
            <x-errors/>
            <x-slot:actions>
                <x-button type="submit" class="btn-neutral" label="Create Task"/>
            </x-slot:actions>
        </x-form>
    </x-modal>

    {{--    Invite member Drawer--}}
    <x-drawer wire:model="drawer" title="Invite member" separator subtitle="invite new team member to this project">
        <x-form wire:submit="inviteMember" class="mb-4">
            @csrf
            <x-input required wire:model="email" label="Team member email" placeholder="John@doe.com"/>
            <x-errors/>
            <x-button type="submit" label="Invite"/>
        </x-form>
        <h2 class="font-bold text-xl ">Members</h2>
        <hr class="mb-2 mt-2"/>
        <div class="space-y-2 overflow-y-scroll h-full">
            @foreach($users as $user)
                <div class="shadow-md px-4 py-2 rounded-md">
                    <h2 class="font-semibold">
                        {{$user->name}}
                    </h2>
                    <span class="text-slate-500">
                        {{$user->email}}
                    </span>
                </div>
            @endforeach
        </div>
    </x-drawer>

    {{--    Task Card--}}
    <div class="grid grid-cols-4 gap-10">
        @foreach($project->tasks as $task)
            @can('has-access-to-task',$task)
                <a target="_blank" href="{{$task->github_url}}">
                    <x-card title="{{$task->name}}">
                        <x-slot:menu>
                            <x-button icon="o-check"/>
                            @can('isProjectOwner',$project)
                                @if($task->users()->count()<1)
                                    <x-button wire:click="openTaskModal({{$task}})"
                                              icon="o-adjustments-horizontal"/>
                                @endif
                            @endcan
                        </x-slot:menu>
                        <div class="flex items-center justify-between">
                            <p class="font-semibold badge badge-neutral text-white">
                                Assigned: @foreach($task->users as $user)
                                    <span class="ml-2">

                                    @if($user->id===Auth::user()->id)
                                            Me
                                        @else
                                            {{$user->name}}
                                        @endif
                                </span>
                                @endforeach
                            </p>
                            <p @class([
    'badge font-semibold',
    'badge-accent text-white' => $task->priority === 'medium',
    'badge-success text-white' => $task->priority === 'low',
    'badge-error text-white' => $task->priority === 'low',

])>
                                {{$task->priority}}
                            </p>


                        </div>
                        <div class="mt-4">
                            <p class="badge badge-warning font-semibold ">Due
                                Date: {{\Carbon\Carbon::parse($task->due_date)->format('d/m/y g:i a')}}</p>
                        </div>
                    </x-card>
                </a>
            @endcan
        @endforeach

        {{--        Assing user task modal--}}
        <x-modal wire:model="taskModal" title="Assign User">
            <x-form wire:submit="assignUserToTask">
                <x-select required wire:model="userId" label="Assign User" :options="$usersArr"/>
                <x-button label="Save" type="submit"/>
            </x-form>
        </x-modal>

        {{-- Project Discussion --}}

        <x-drawer wire:model="discussion" right title="Project Discussion" separator>
            <x-form wire:submit="publishComment">
                <x-input placeholder="Comment" wire:model="comment" label="Comment"/>
                <x-button label="Publish" type="submit"/>
            </x-form>

            <div class="space-y-4 mt-6">
                @foreach($project->comments as $comment)
                    <div class="shadow-md px-2 py-2 rounded-md">
                        <div class="flex justify-between">
                            <h2 class="text-xl font-semibold">
                                {{$comment->user->name}}
                            </h2>
                            <span>
                            {{\Carbon\Carbon::parse($comment->created_at)->diffForHumans()}}
                        </span>
                        </div>

                        <p>
                            {{$comment->comment}}
                        </p>
                        <x-button label="Reply"/>
                    </div>
                @endforeach
            </div>
        </x-drawer>
    </div>
</div>
