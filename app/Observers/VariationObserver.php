<?php

namespace App\Observers;

use App\Models\Variation;

class VariationObserver
{
    /**
     * Handle the Variation "deleting" event.
     */
    public function deleting(Variation $variation): void
    {
        if ($variation->project->variations()->count() === 1) {
            throw new \Exception('Cannot delete the last variation of a project.');
        }
    }
}
