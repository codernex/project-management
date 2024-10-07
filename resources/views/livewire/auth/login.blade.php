<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.guest')] class extends Component {
    public string $email;
    public string $password;

    public function mount()
    {
        $this->email="admin@example.com";
        $this->password="12345678";
    }

    public function rendering()
    {
        if(Auth::check()){
            return $this->redirect('/projects');
        }
    }

    public function login()
    {
        $data = $this->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);
        $user = \App\Models\User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return $this->addError('error', 'Invalid Credentials');
        }

        auth()->login($user);
        return redirect()->intended('/projects');

    }

}; ?>
<x-slot:title>
    Login
</x-slot:title>
<div class="w-full h-screen flex items-center">
    <div class=" w-full">
        <x-form method="post" wire:submit="login" class="shadow-md mx-auto max-w-2xl px-4 py-2 rounded-md grid">
            @csrf
            <h2 class="text-xl font-semibold text-center">Login</h2>
            <x-input label="Email" wire:model="email" icon-right="o-envelope" placeholder="john@example.com"/>
            <x-password placeholder="*******" label="password" wire:model="password" icon-right="o-key"/>
            <x-errors/>
            <x-button label="Login" class="btn-primary" type="submit" spinner="save2"/>
        </x-form>
    </div>
</div>
