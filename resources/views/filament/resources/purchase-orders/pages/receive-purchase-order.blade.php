<x-filament-panels::page>
    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <table class="fi-ta-table w-full text-start">
            <thead>
                <tr class="bg-gray-50 dark:bg-white/5">
                    <th class="px-4 py-3 text-start text-xs font-semibold text-gray-600 dark:text-gray-300">Product</th>
                    <th class="px-4 py-3 text-start text-xs font-semibold text-gray-600 dark:text-gray-300">Ordered</th>
                    <th class="px-4 py-3 text-start text-xs font-semibold text-gray-600 dark:text-gray-300">Already Received</th>
                    <th class="px-4 py-3 text-start text-xs font-semibold text-gray-600 dark:text-gray-300">Receive Now</th>
                </tr>
            </thead>
            <tbody>
                @foreach($this->getRecord()->lines as $line)
                <tr class="border-t border-gray-100 dark:border-white/5">
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-950 dark:text-white">{{ $line->product_name }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $line->part_number ?? '—' }}</div>
                    </td>
                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $line->quantity_ordered }}</td>
                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $line->quantity_received }}</td>
                    <td class="px-4 py-3">
                        @if($line->quantityOutstanding() > 0)
                        <input
                            type="number"
                            min="0"
                            max="{{ $line->quantityOutstanding() }}"
                            wire:model="receiveQty.{{ $line->id }}"
                            class="fi-input block w-24 rounded-lg border-none bg-white px-3 py-1.5 text-sm shadow-sm ring-1 ring-gray-950/10 dark:bg-white/5 dark:text-white dark:ring-white/20"
                        >
                        @else
                        <span class="text-xs text-gray-400">Fully received</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <x-filament::button wire:click="receive" wire:loading.attr="disabled">
            Confirm Receipt
        </x-filament::button>
    </div>
</x-filament-panels::page>
