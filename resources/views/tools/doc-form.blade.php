@php
$titles = ['quotation' => 'New Quotation', 'invoice' => 'New Invoice', 'receipt' => 'New Receipt'];
@endphp
<x-layouts::app :title="__('Business Tools — '.$titles[$type])">
    @include('partials.toast')
    @include('partials.custom-select')
    <div class="flex w-full flex-col gap-5">
        <x-page-head :title="$titles[$type]" sub="Totals are computed on the server — what you see is what the PDF prints." />
        @include('tools._tabs', ['active' => $active])

        @if($errors->any())
        <div class="card card-pad border-red-300 bg-red-50 text-sm text-red-700 dark:bg-red-500/10 dark:text-red-300">
            <p class="font-bold">Please fix the following:</p>
            <ul class="list-disc ml-5 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        @include('tools._doc-form', ['formTitle' => substr($titles[$type], 4)])
    </div>
</x-layouts::app>
