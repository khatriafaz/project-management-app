<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
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
     * Retrieve the model for a bound value.
     *
     * @param mixed $value
     * @param string|null $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? $this->getRouteKeyName(), $value)
            ->whereHas('users', function ($query) {
                $query->where('user_id', auth()->user()->id);
            })->first();
    }

    /**
     * Get the owner of the project
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the owner of the project
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
