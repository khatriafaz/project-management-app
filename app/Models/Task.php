<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'title', 'description', 'column_id'
    ];

    public function column()
    {
        return $this->belongsTo(Column::class);
    }
}
