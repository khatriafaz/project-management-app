<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Column;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->projects();

        foreach (Arr::wrap($request->input('with', [])) as $relation) {
            $query->with($relation);
        }

        return ProjectResource::collection($query->get());
    }

    public function show(Project $project, Request $request): ProjectResource
    {
        foreach (Arr::wrap($request->input('with', [])) as $relation) {
            $project->loadMissing($relation);
        }

        return new ProjectResource($project);
    }

    public function store(ProjectRequest $request): ProjectResource
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

    public function unAssingn(Request $request, Project $project)
    {
        if ($users = $request->input('users')) {
            $project->unAssignUsers($users);
        }

        return new ProjectResource($project->refresh());
    }

    public function orderColumns(Project $project, Request $request)
    {
        Column::reOrderForProject($project, $request->input('columnIds'));

        foreach (Arr::wrap($request->input('with', [])) as $relation) {
            $project->loadMissing($relation);
        }

        return new ProjectResource($project);
    }

    public function destroy(Project $project)
    {
        $project->users()->detach();
        $project->columns()->delete();

        $project->delete();

        return response()->json([], 204);
    }
}
