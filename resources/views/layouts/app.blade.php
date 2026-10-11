<x-layouts::app.sidebar :title="$title ?? null">
    <main class="block">
        <div class="w-full">{{ $slot }}</div>
    </main>
</x-layouts::app.sidebar>
