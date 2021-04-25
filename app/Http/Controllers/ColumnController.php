<?php

namespace App\Http\Controllers;

use App\Http\Requests\ColumnRequest;
use App\Http\Resources\ColumnResource;
use App\Models\Project;
use Illuminate\Http\Request;

class ColumnController extends Controller
{
    public function store(Project $project, ColumnRequest $request)
    {
        $column = $project->columns()->create($request->all());

        return new ColumnResource($column);
    }

    public function update(Project $project, int $id, Request $request)
    {
        $column = $project->columns()->findOrFail($id);

        $column->update($request->all());

        return new ColumnResource($column);
    }

    public function destroy(Project $project, int $id)
    {
        $column = $project->columns()->findOrFail($id);
        $column->delete();

        return response()->json([
            'message' => 'Column deleted successfully'
        ]);
    }
}
