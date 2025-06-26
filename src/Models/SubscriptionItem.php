<?php

namespace Fullstack\Redbird\Models;

use Laravel\Cashier\SubscriptionItem as CashierSubscriptionItem;

class SubscriptionItem extends CashierSubscriptionItem
{
    protected $guarded = [];
}
