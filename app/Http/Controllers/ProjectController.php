<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = $request->user()->projects()->get();

        return ProjectResource::collection($projects);
    }

    public function show(Project $project): ProjectResource
    {
        return new ProjectResource($project);
    }

    public function store(Request $request): ProjectResource
    {
        $project = Project::create($request->all());

        return new ProjectResource($project);
    }

    public function update(Request $request, Project $project): ProjectResource
    {
        $project->update($request->all());

        if ($users = $request->input('users')) {
            $project->assignUsers($users);
        }

        return new ProjectResource($project);
    }

    public function destroy(Project $project)
    {
        $project->users()->detach();

        $project->delete();

        return response()->json([], 204);
    }
}
