<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Model for roles table (created for IDE Helper).
 *
 * @package App\Models
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin \Illuminate\Database\Query\Builder
 *
 * @property int id
 * @property string name
 * @property string friendly_name
 * @property string description
 * @property string guard_name
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property Collection|Permission[] permissions
 * @property Collection|User[] users
 */
class Role extends \Spatie\Permission\Models\Role
{
    public const ADMIN = 1;
    public const TEACHER = 2;
    public const COORDINATOR = 3;
    public const COMPANY = 4;
    public const STUDENT = 5;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = config('database.default');
    }
}
