<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Repository extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'repositories';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}
