<?php

namespace App\Observers;

use App\Models\Customer;
use App\Models\Audit;
use Illuminate\Support\Facades\Auth;

class CustomerObserver
{
    /**
     * Handle the Customer "created" event.
     */
    public function created(Customer $customer): void
    {
        Audit::create([
            'auditable_type' => Customer::class,
            'auditable_id' => $customer->NIT,
            'action' => 'created',
            'user_id' => Auth::id(),
            'new_values' => $customer->toArray(),
            'created_at' => now(),
        ]);
    }

    public function updated(Customer $customer): void
    {
        Audit::create([
            'auditable_type' => Customer::class,
            'auditable_id' => $customer->NIT,
            'action' => 'updated',
            'user_id' => Auth::id(),
            'old_values' => $customer->getOriginal(),
            'new_values' => $customer->getChanges(),
        ]);
    }

    /**
     * Handle the Customer "deleted" event.
     */
    public function deleted(Customer $customer): void
    {
        Audit::create([
            'auditable_type' => Customer::class,
            'auditable_id' => $customer->NIT,
            'action' => 'deleted',
            'user_id' => Auth::id(),
            'old_values' => $customer->toArray(),
        ]);
    }
}
