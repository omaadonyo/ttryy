<form wire:submit="login" class="flex flex-col gap-4">
    <flux:input wire:model="email" name="email" label="Email address" type="email" required autocomplete="email" placeholder="email@example.com" />
    <flux:input wire:model="password" name="password" label="Password" type="password" required autocomplete="current-password" placeholder="Password" viewable />
    <flux:checkbox wire:model="remember" label="Remember me" />
    <flux:error name="email" />
    <flux:button variant="primary" type="submit" class="w-full">Log in &amp; continue</flux:button>
    <p class="text-sm text-center text-zinc-600 dark:text-zinc-400">
        <flux:link :href="route('password.request')">Forgot your password?</flux:link>
    </p>
</form>
