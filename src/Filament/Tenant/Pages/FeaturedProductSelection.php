<?php

namespace App\Filament\Tenant\Pages;

use App\Models\Plan;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class FeaturedProductSelection extends Page
{
    public string $plan = 'month';

    protected static ?string $navigationIcon = 'heroicon-s-fire';

    protected static ?string $title = 'Subscriptions';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.admin.pages.featured-product-selection';

    public array $availablePlans = [];

    public string $clientSecret;

    public function mount(): void
    {
        $this->clientSecret = Auth::user()->createSetupIntent()->client_secret;

        $currentPlanId = Auth::user()->currentSubscription();

        // Store current plan's interval_period for toggle default (month/year)
        $currentPlan = Plan::find($currentPlanId);

        $this->plan = $currentPlan?->interval_period ?? 'month';

        $this->availablePlans = Plan::with('product')
            ->whereNull('deleted_at')
            ->whereNull('archived_at')
            ->where('is_active', true)
            ->get()
            ->map(function ($plan) use ($currentPlanId) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'description' => $plan->description,
                    'unit_amount' => '$'.ceil($plan->unit_amount / 100),
                    'interval_period' => $plan->interval_period,
                    'product_name' => $plan->product->name ?? null,
                    'features' => is_array($plan->product->features)
                        ? $plan->product->features
                        : json_decode($plan->product->features ?? '[]', true),
                    'is_current' => $plan->id === $currentPlanId,
                ];
            })->toArray();
    }

    protected function getViewData(): array
    {

        return [
            'availablePlans' => $this->availablePlans,
            'plan' => $this->plan,
            'clientSecret' => $this->clientSecret,
        ];
    }
}
