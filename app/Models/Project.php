<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'description', 'user_id'
    ];

    /**
     * @param array|int $users
     */
    public function assignUsers($users)
    {
        if (is_array($users)) {
            $this->users()->sync($users);
        } else {
            $this->users()->toggle($users);
        }
    }

    /**
     * @param array|int $users
     */
    public function unAssignUsers($users)
    {
        $this->users()->detach($users);
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param mixed $value
     * @param string|null $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        /** @var User $user */
        $user = auth()->user();

        return $this->where($field ?? $this->getRouteKeyName(), $value)
            ->whereHas('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->first();
    }

    /**
     * Get the owner of the project
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the owner of the project
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function columns()
    {
        return $this->hasMany(Column::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
