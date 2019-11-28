<?php

namespace App\Http\Requests\Admin;

use App\Models\Role;
use App\Models\User;
use App\Rules\Integer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBroker extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', 'max:191'],
            'url' => ['required', 'max:191'],
            'roles' => ['required', 'array'],
            'roles.*' => ['required', 'integer', 'min:1', 'exists:roles,id'],
        ];
    }
}
