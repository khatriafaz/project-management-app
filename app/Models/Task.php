<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'description', 'column_id'
    ];

    public function column()
    {
        return $this->belongsTo(Column::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
