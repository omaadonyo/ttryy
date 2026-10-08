<form wire:submit="register" class="flex flex-col gap-4">
    <flux:input wire:model="name" name="name" label="Full name" type="text" required autocomplete="name" placeholder="Jane Nakato" />
    <flux:input wire:model="email" name="email" label="Email address" type="email" required autocomplete="email" placeholder="email@example.com" />
    <flux:input wire:model="password" name="password" label="Password" type="password" required autocomplete="new-password" placeholder="Minimum 8 characters" viewable />
    <flux:input wire:model="password_confirmation" name="password_confirmation" label="Confirm password" type="password" required autocomplete="new-password" viewable />
    <flux:error name="email" />
    <flux:button variant="primary" type="submit" class="w-full">Create account &amp; continue</flux:button>
    <p class="text-xs text-center text-zinc-500">Your package choices are saved and waiting after you sign up.</p>
</form>
