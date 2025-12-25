<?php

namespace App\Observers;

use App\Models\Version;

class VersionObserver
{
    /**
     * Handle the Version "deleting" event.
     */
    public function deleting(Version $version): void
    {
        if ($version->project->versions()->count() === 1) {
            throw new \Exception('Cannot delete the last version of a project.');
        }
    }
}
