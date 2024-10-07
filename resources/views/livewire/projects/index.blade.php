<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Validate;

new class extends Component {
    use \Mary\Traits\Toast, \Livewire\WithFileUploads, \Livewire\WithPagination;

    public bool $open = false;
    #[Validate('string|required')]
    public string $title;

    #[Validate('string|required')]
    public string $description;

    #[Validate('image')]
    public ?\Illuminate\Http\UploadedFile $file = null;

    #[Validate('required')]
    public string $due_date;

    public function saveProject()
    {
        DB::beginTransaction();

        try {
            // Create and save the project
            $project = new \App\Models\Project([
                'title' => $this->title,
                'description' => $this->description,
                'owner_id' => Auth::user()->id,
                'due_date' => $this->due_date
            ]);
            $project->save();

            // Upload the image file
            if ($this->file) {
                $imageUrl = Storage::disk('public')->put('projects', $this->file);

                // Create and save the image
                $image = new \App\Models\Image([
                    'url' => $imageUrl
                ]);
                $project->image()->save($image);
            }

            DB::commit();

            // Success message
            $this->open = false;
            $this->success(
                'Project Created',
                'New Project created successfully'
            );

        } catch (\Exception $e) {
            dd($e);
            DB::rollBack();
            // Handle the exception (e.g., log it, show an error message)
            $this->error('Error', 'Failed to create project. Please try again.');
        }
    }


    public function redirectToProjectPage(int $projectId)
    {
        return redirect()->route('project.show', ['project' => $projectId]); // Replace with the actual route and parameter
    }

    public function with(): array
    {
        $projects = Auth::user()->projects()->paginate(5);
        $invitedProjects = Auth::user()->invitedProjects()->paginate(5);

        // Merge the projects collection with invitedProjects and convert to a collection
        $mergedProjects = $projects->getCollection()->merge($invitedProjects);

        // Set the merged collection back to the paginator
        $projects->setCollection($mergedProjects);

        return [
            'projects' => $projects
        ];
    }
}; ?>
<x-slot:title>
    Projects
</x-slot:title>
<div>
    <x-header title="Projects" separator>
        <x-slot:actions>
            <x-button @click="$wire.open=true" label="Add New Project" class="btn btn-neutral"/>
        </x-slot:actions>
    </x-header>
    <x-modal title="Create new Project" wire:model="open">
        <x-form wire:submit="saveProject" enctype="multipart/form-data">
            <x-input required label="Project Title" wire:model="title" placeholder="eg: multi vendor ecommerce"/>
            <x-textarea required label="Project Description" wire:model="description"
                        placeholder="eg: this project is created to track multi vendor ecommerce project progression"/>
            <x-datetime required label="Due date" type="datetime-local" wire:model="due_date"/>
            <x-file required wire:model="file" label="Project Image" hint="Only Image" accept="image/*"/>
            <x-slot:actions>
                <x-button type="submit" class="btn-neutral">
                    Save
                </x-button>
            </x-slot:actions>
        </x-form>
    </x-modal>
    <div class="grid grid-cols-4 gap-10">
        @foreach($projects as $project)
            <a wire:navigate href="{{route('project.show',['project'=>$project->id])}}">
                <x-card :title="$project->title">
                    <x-slot:figure>
                        <img src="{{Storage::url($project->image->url??'')}}" alt=""/>
                    </x-slot:figure>
                    <x-slot:actions>
                        <p class="badge badge-warning font-semibold">
                            {{\Carbon\Carbon::parse($project->due_date)->format('m-d-y')}}
                        </p>
                        <p class="badge badge-info text-white font-semibold">
                            {{$project->status}}
                        </p>
                    </x-slot:actions>
                </x-card>
            </a>
        @endforeach
    </div>
    <div class="mt-6">
        {{$projects->links()}}
    </div>
</div>
