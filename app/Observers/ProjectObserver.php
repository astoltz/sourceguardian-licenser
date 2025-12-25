<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\Version;
use App\Models\Variation;

class ProjectObserver
{
    /**
     * Handle the Project "created" event.
     */
    public function created(Project $project): void
    {
        $project->versions()->create([
            'display_name' => 'Default',
        ]);

        $project->variations()->create([
            'display_name' => 'Default',
        ]);
    }
}
