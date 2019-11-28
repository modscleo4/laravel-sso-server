<?php

namespace App\Models;

/**
 * Class Broker
 * @package App\Models
 *
 * @property int id
 * @property string name
 * @property string url
 * @property string secret
 */
class Broker extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'url', 'secret',
    ];
}
