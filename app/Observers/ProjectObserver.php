<?php

namespace App\Observers;

use App\Models\Project;
use Illuminate\Support\Facades\App;

class ProjectObserver
{
    /**
     * Handle the Project "created" event.
     *
     * @param Project $project
     * @return void
     */
    public function creating(Project $project)
    {
        if (!auth()->check()) {
            return;
        }

        $project->user_id = auth()->user()->id;
    }
}
