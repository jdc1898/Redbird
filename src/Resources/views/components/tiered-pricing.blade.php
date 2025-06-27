@php
    $id = $getId();
    $statePath = $getStatePath();
    $rows = $getState() ?? [];
@endphp

<div
    x-data="{
        rows: $wire.entangle('{{ $statePath }}').live,
        addRow() {
            const lastRow = this.rows[this.rows.length - 1] ?? null;
            const lastUnit = lastRow ? (lastRow.last_unit === '∞' ? '∞' : parseInt(lastRow.last_unit) + 1) : 1;

            this.rows.push({
                first_unit: lastUnit === '∞' ? lastUnit : parseInt(lastUnit),
                last_unit: '∞',
                per_unit: '',
                flat_fee: '',
            });
        },
        removeRow(index) {
            this.rows.splice(index, 1);
            this.updateFirstUnits();
        },
        updateFirstUnits() {
            this.rows.forEach((row, index) => {
                if (index === 0) {
                    row.first_unit = 1;
                    return;
                }
                const prevRow = this.rows[index - 1];
                if (prevRow.last_unit === '∞') {
                    row.first_unit = '∞';
                } else {
                    row.first_unit = parseInt(prevRow.last_unit) + 1;
                }
            });
        }
    }"
>
    <div class="space-y-2">
        <div class="flex gap-2 px-2">
            <div class="text-sm font-medium text-gray-700 w-1/5">First unit</div>
            <div class="text-sm font-medium text-gray-700 w-1/5">Last unit</div>
            <div class="text-sm font-medium text-gray-700 w-1/5 pl-8">Per unit</div>
            <div class="text-sm font-medium text-gray-700 w-1/5 pl-8">Flat fee</div>
            <div class="w-1/5"></div>
        </div>

        <template x-for="(row, index) in rows" :key="index">
            <div class="flex gap-2 items-center">
                <div class="w-1/5">
                    <input type="text"
                        x-model="row.first_unit"
                        disabled
                        class="block dark:bg-gray-800 dark:border-gray-700 w-full rounded-lg border-gray-300 shadow-sm disabled:bg-gray-50 disabled:text-gray-500 sm:text-sm"
                    >
                </div>
                <div class="relative w-1/5">
                    <input type="text"
                        x-model="row.last_unit"
                        @input="updateFirstUnits"
                        class="block w-full rounded-lg dark:bg-gray-800 dark:border-gray-700 border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                    >
                    <button
                        x-show="index === rows.length - 1"
                        type="button"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                    >
                    </button>
                </div>
                <div class="relative w-1/5">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500"><span class="px-1">$</span></span>
                    <input type="text"
                        x-model="row.per_unit"
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                        class="block w-full rounded-lg border-gray-300 pl-8 shadow-sm focus:border-primary-500 dark:bg-gray-800 dark:border-gray-700 focus:ring-primary-500 sm:text-sm"
                    >
                </div>
                <div class="relative w-1/5">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500"><span class="px-1">$</span></span>
                    <input type="text"
                        x-model="row.flat_fee"
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                        class="block w-full dark:bg-gray-800 dark:border-gray-700 rounded-lg border-gray-300 pl-8 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                    >
                </div>
                <div class="w-1/5 flex justify-start">
                    <button
                        x-show="rows.length > 1"
                        @click="removeRow(index)"
                        type="button"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </template>

        <button
            type="button"
            @click="addRow"
            class="mt-2 inline-flex items-center gap-1 text-sm text-primary-600 hover:text-primary-500"
        >
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Add tier
        </button>
    </div>
</div>
