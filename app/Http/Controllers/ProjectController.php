<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function store(Request $request): ProjectResource
    {
        $project = Project::create($request->all());

        return new ProjectResource($project);
    }

    public function update(Request $request, Project $project): ProjectResource
    {
        $project->update($request->all());

        return new ProjectResource($project);
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return response()->json([], 204);
    }
}
