<?php

namespace App\Observers;

use App\Models\Project;

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
        $project->user_id = auth()->user()->id;
    }
}
