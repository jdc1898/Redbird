<x-filament-panels::page>
    <x-filament::card>

        <div class="flex flex-col items-center justify-center mb-6">
            <p class="text-primary-600 font-bold">Pricing</p>
            <h1 class="text-3xl text-center font-bold mt-10 mb-4">
                Pricing that grows with you
            </h1>
            <p class="text-gray-600 text-center dark:text-gray-400 w-1/2 text-sm">Choose an affordable plan that’s packed with the best features for engaging your audience, creating member loyalty, and providing value.</p>
        </div>


        <div class="h-12 p-2">&nbsp;</div>


        <!-- Toggle for Monthly/Yearly Plans -->
        <div class="flex justify-center w-full mt-8 mb-6 border-b border-gray-200 pb-6">
            <div class="flex bg-neutral-200 dark:bg-gray-800 rounded-full p-1 w-60">
                <button type="button" wire:click="$set('plan', 'month')"
                    class="{{ $plan === 'month' ? 'bg-primary-600 text-white' : 'text-gray-700 dark:text-white' }} flex-1 text-center px-4 py-2 text-sm font-medium rounded-full transition">
                    Monthly
                </button>

                <button type="button" wire:click="$set('plan', 'year')"
                    class="{{ $plan === 'year' ? 'bg-primary-600 text-white' : 'text-gray-700 dark:text-white' }} flex-1 text-center px-4 py-2 text-sm font-medium rounded-full transition">
                    Yearly
                </button>
            </div>
        </div>

        <div class="flex gap-8 p-2 my-4">
            @forelse ($availablePlans as $availablePlan)
                @if ($availablePlan['interval_period'] === $plan)
                    <div class="p-4">

                        <div class="text-xl font-semibold text-gray-900 dark:text-white">{{ $availablePlan['name'] }}</div>

                        <div class="flex w-full items-center justify-start mt-2">
                            <div class="mr-1 text-3xl font-bold">{{ $availablePlan['unit_amount'] }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">/ {{ucfirst($availablePlan['interval_period']) }}</div>
                        </div>

                        <div class="mt-6 w-full flex justify-center px-6">
                            @if ($availablePlan['is_current'])
                                <a href="{{ route('billing') }}"
                                    class="inline-flex items-center justify-center w-full  px-6 py-3 font-semibold text-white rounded-lg hover:bg-primary-700 transition" style="background-color: #e67763;">
                                    Update
                                </a>
                            @else
                                <a href="{{ route('subscribe', ['plan' => $availablePlan['id']]) }}"
                                class="inline-flex items-center justify-center w-full  px-6 py-3 font-semibold text-white bg-primary-600 rounded-lg hover:bg-primary-700 transition">
                                Buy
                            </a>
                            @endif
                        </div>

                        <p class="w-full text-xs text-gray-500 text-center dark:text-zinc-400 mt-2">
                            {{ $availablePlan['description'] }}
                        </p>

                        <ul class="mt-4 text-left p-4 space-y-2 text-sm text-gray-700 dark:text-gray-300">
                            @foreach ($availablePlan['features'] as $feature)
                            <li class="flex items-center gap-2">
                                <svg class="h-6 w-5 flex-none text-primary-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"
                                    data-slot="icon">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z"
                                        clip-rule="evenodd">
                                    </path>
                                </svg>
                                {{ is_array($feature) ? $feature['name'] : $feature }}</li>
                            @endforeach
                        </ul>


                    </div>
                @endif
            @empty
            <div class="text-center text-gray-500 dark:text-gray-400">
                No plans available.
            </div>
            @endforelse
        </div>

    </x-filament::card>
</x-filament-panels::page>
