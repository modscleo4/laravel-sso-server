<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * Class User
 *
 * @package App\Models
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin \Illuminate\Database\Query\Builder
 *
 * @property int id
 * @property string name
 * @property string email
 * @property string phone
 * @property string password
 * @property Carbon password_change_at
 * @property string remember_token
 * @property string api_token
 * @property Carbon email_verified_at
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class User extends Authenticatable
{
    use HasRoles;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'phone', 'password', 'password_change_at', 'api_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'api_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'password_change_at' => 'datetime',
        'email_verified_at' => 'datetime',
    ];

    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    public function isTeacher()
    {
        return $this->hasRole('teacher');
    }

    public function isCoordinator()
    {
        return $this->hasRole('coordinator');
    }

    public function isCompany()
    {
        return $this->hasRole('company');
    }

    public function isStudent()
    {
        return $this->hasRole('student');
    }

    public function brokers()
    {
        $permissions = array_column($this->getAllPermissions()->toArray(), 'name');
        $brokers = Broker::whereIn('name', $permissions)->get();

        return $brokers;
    }
}
