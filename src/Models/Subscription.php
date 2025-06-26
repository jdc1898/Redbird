<?php

namespace Fullstack\Redbird\Models;

use Laravel\Cashier\Subscription as CashierSubscription;

class Subscription extends CashierSubscription
{
    protected $table = 'subscriptions';

    protected $guarded = [];

}
