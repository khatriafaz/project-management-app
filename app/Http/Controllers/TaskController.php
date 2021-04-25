<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TaskController extends Controller
{
    public function index(Project $project)
    {
        return TaskResource::collection(
            $project->tasks()->get()
        );
    }

    public function store(Project $project, TaskRequest $request)
    {
        $task = $project->tasks()->create($request->all());

        return new TaskResource($task);
    }

    public function show(Project $project, int $id)
    {
        $task = $project->tasks()->findOrFail($id);

        if (!$task) {
            throw new NotFoundHttpException('This task cannot be found');
        }

        return new TaskResource($task);
    }

    public function update(Project $project, int $id, Request $request)
    {
        $task = $project->tasks()->findOrFail($id);

        if (!$task) {
            throw new NotFoundHttpException('This task cannot be found');
        }

        $task->update($request->all());

        return new TaskResource($task);
    }

    public function assign(Project $project, int $id, User $user)
    {
        $task = $project->tasks()->findOrFail($id);

        if (!$task) {
            throw new NotFoundHttpException('This task cannot be found');
        }

        $task->users()->attach($user);

        $task->refresh();

        return new TaskResource($task);
    }

    public function unassign(Project $project, int $id, User $user)
    {
        $task = $project->tasks()->findOrFail($id);

        if (!$task) {
            throw new NotFoundHttpException('This task cannot be found');
        }

        $task->users()->detach($user);

        $task->refresh();

        return new TaskResource($task);
    }

    public function destroy(Project $project, int $id)
    {
        $task = $project->tasks()->findOrFail($id);

        if (!$task) {
            throw new NotFoundHttpException('This task cannot be found');
        }

        $task->delete();

        return response()->json([
            'message' => 'Task has been deleted'
        ]);
    }
}
