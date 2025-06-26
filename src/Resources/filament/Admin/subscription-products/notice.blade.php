    <h1 class="text-3xl font-bold">

    <x-filament::breadcrumbs :breadcrumbs="$breadcrumbs" />


        <div class="flex items-center justify-between mt-3">
        {{ $heading }}

        <x-filament::button href="/super-admin/subscription-products/create" tag="a">
        New Subscription Product
        </x-filament::button>


        </div>
    </h1>

        <div class="p-4 text-sm border rounded-xl" style="color: #818CF8; border-color: #818CF8;">
            <strong>Important: </strong>Editing products have been disabled on the demo site to maintain a functioning demo, but on your own site
            you'll be able to edit products as needed.
        </div>
