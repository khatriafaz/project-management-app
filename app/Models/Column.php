<?php

namespace App\Models;

use App\Scopes\OrderScope;
use ArrayAccess;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use InvalidArgumentException;

class Column extends Model
{
    use SoftDeletes;

    protected $fillable = ['title'];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new OrderScope);
    }

    public static function reOrderForProject(Project $project, $ids, $startOrder = 1)
    {
        if (!is_array($ids) && !$ids instanceof ArrayAccess) {
            throw new InvalidArgumentException('You must pass an array or ArrayAccess object to setNewOrder');
        }

        foreach ($ids as $id) {
            $project->columns()->where('id', $id)
                ->update(['order' => $startOrder++]);
        }
    }
}
