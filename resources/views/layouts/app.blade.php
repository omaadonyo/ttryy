<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main>
        <div class="mx-auto w-full md:w-[70%]">{{ $slot }}</div>
    </flux:main>
</x-layouts::app.sidebar>
