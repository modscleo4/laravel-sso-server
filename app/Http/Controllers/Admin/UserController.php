<?php

namespace App\Http\Controllers\Admin;

use App\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DestroyUser;
use App\Http\Requests\Admin\StoreUser;
use App\Http\Requests\Admin\UpdateUser;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    public function index()
    {
        $users = User::all();

        return view('admin.user.index')->with(['users' => $users]);
    }

    public function create()
    {
        $roles = Role::all()->where('name', '<>', 'coordinator')->where('name', '<>', 'company')->where('name', '<>', 'student')->sortBy('id');

        return view('admin.user.new')->with(['roles' => $roles]);
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        if ($user->hasRole('company') || $user->hasRole('student')) {
            abort(404);
        }

        $roles = Role::all()->where('name', '<>', 'coordinator')->where('name', '<>', 'company')->where('name', '<>', 'student')->merge($user->roles)->sortBy('id');

        return view('admin.user.edit')->with(['user' => $user, 'roles' => $roles]);
    }

    public function store(StoreUser $request)
    {
        $user = new User();
        $params = [];

        $validatedData = (object)$request->validated();

        $log = "New User";
        $log .= "\nUser: " . Auth::user()->name;

        $user->name = $validatedData->name;
        $user->email = $validatedData->email;
        $user->phone = $validatedData->phone;
        $user->password = Hash::make($validatedData->password);

        $saved = $user->save();
        $log .= "\nNew data: " . json_encode($user, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $user->syncRoles([Role::findOrFail($validatedData->role)->name]);

        if ($saved) {
            Log::info($log);
        } else {
            Log::error("Error when saving User");
        }

        $params['saved'] = $saved;
        $params['message'] = ($saved) ? 'Saved successfully.' : 'Error when saving!';

        return redirect()->route('admin.user.index')->with($params);
    }

    public function update($id, UpdateUser $request)
    {
        $user = User::findOrFail($id);
        $params = [];

        $validatedData = (object)$request->validated();

        $log = "Edit User";
        $log .= "\nUser: " . Auth::user()->name;
        $log .= "\nOld data: " . json_encode($user, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $user->name = $validatedData->name;
        $user->email = $validatedData->email;
        $user->phone = $validatedData->phone;

        $saved = $user->save();
        $log .= "\nNew data: " . json_encode($user, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $roles = [Role::findOrFail($validatedData->role)->name];
        if ($user->isCoordinator()) {
            array_push($roles, Role::findOrFail(Role::COORDINATOR));
        }

        $user->syncRoles($roles);

        if ($saved) {
            Log::info($log);
        } else {
            Log::error("Error when updating User");
        }

        $params['saved'] = $saved;
        $params['message'] = ($saved) ? 'Updated successfully.' : 'Error when updating!';

        return redirect()->route('admin.user.index')->with($params);
    }

    public function destroy($id, DestroyUser $request)
    {
        $user = User::findOrFail($id);
        $params = [];

        $validatedData = (object)$request->validated();

        $log = "Delete User";
        $log .= "\nUser: " . Auth::user()->name;
        $log .= "\nOld data: " . json_encode($user, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $saved = $user->delete();

        if ($saved) {
            Log::info($log);
        } else {
            Log::error("Error when deleting User");
        }

        $params['saved'] = $saved;
        $params['message'] = ($saved) ? 'Deleted successfully.' : 'Error when deleting!';
        return redirect()->route('admin.user.index')->with($params);
    }
}
