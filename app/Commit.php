<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Commit extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'commits';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Get the repository that owns the commit.
     */
    public function repository()
    {
        return $this->belongsTo('App\Repository');
    }
}
