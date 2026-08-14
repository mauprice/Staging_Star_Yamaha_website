<x-filament-panels::page>
    {{ $this->content }}
</x-filament-panels::page>

@script
<script>
    setInterval(() => $wire.$refresh(), 10000);
</script>
@endscript
