<?php

namespace App\Observers;

use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

class SaleItemObserver
{
    /**
     * Handle the SaleItem "created" event.
     */
    public function created(SaleItem $saleItem): void
    {
        DB::transaction(function () use ($saleItem) {
            if ($saleItem->product) {
                // Atomic operation to prevent race conditions
                $saleItem->product->decrement('quantity', $saleItem->quantity);
            }
        },5);
    }

    /**
     * Handle the SaleItem "updated" event.
     */
    public function updated(SaleItem $saleItem): void
    {
        //
    }

    /**
     * Handle the SaleItem "deleted" event.
     */
    public function deleted(SaleItem $saleItem): void
    {
        //
    }

    /**
     * Handle the SaleItem "restored" event.
     */
    public function restored(SaleItem $saleItem): void
    {
        //
    }

    /**
     * Handle the SaleItem "force deleted" event.
     */
    public function forceDeleted(SaleItem $saleItem): void
    {
        //
    }
}
