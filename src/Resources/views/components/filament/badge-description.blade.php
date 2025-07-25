@props(['price', 'currency', 'units', 'period', 'billing_scheme', 'tiers'])

<div class="-mt-3 text-xs">
        <div>
            {!! $price !!} {{ $currency }}
            @if ($units)
                per {{ $units }} units
            @endif

            @if ($billing_scheme === 'tiered')
                Starts at ${{ $tiers['starting_amount'] / 100 }} {{ $currency }} per unit + ${{ $tiers['flat_amount'] / 100 }} {{ $currency }}
            @endif
        </div>

        <div class="flex items-center gap-1">
            <div class="w-3 h-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                    class="size-3">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
            </div>

            <span>Per {{ $period }}</span>
        </div>
</div>
