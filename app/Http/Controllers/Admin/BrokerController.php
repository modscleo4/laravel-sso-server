<?php

namespace App\Http\Controllers\Admin;

use App\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DestroyBroker;
use App\Http\Requests\Admin\StoreBroker;
use App\Http\Requests\Admin\UpdateBroker;
use App\Models\Broker;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BrokerController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    public function index()
    {
        $brokers = Broker::all();

        return view('admin.broker.index')->with(['brokers' => $brokers]);
    }

    public function create()
    {
        $roles = Role::all();

        return view('admin.broker.new')->with(['roles' => $roles]);
    }

    public function edit($id)
    {
        $broker = Broker::findOrFail($id);
        $roles = Role::all();

        return view('admin.broker.edit')->with(['broker' => $broker, 'roles' => $roles]);
    }

    public function store(StoreBroker $request)
    {
        $broker = new Broker();
        $params = [];

        $validatedData = (object)$request->validated();

        $log = "New Broker";
        $log .= "\nUser: " . Auth::user()->name;

        $broker->name = $validatedData->name;
        $broker->url = $validatedData->url;
        $broker->secret = Str::random(40);

        $saved = $broker->save();
        $log .= "\nNew data: " . json_encode($broker, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($saved) {
            Log::info($log);
            Permission::create([
                'name' => $broker->name,
            ]);

            $roles = $validatedData->roles;
            foreach ($roles as $role) {
                $role = Role::findOrFail($role);
                $role->givePermissionTo($broker->name);
            }
        } else {
            Log::error("Error when saving Broker");
        }

        $params['saved'] = $saved;
        $params['message'] = ($saved) ? "Saved successfully with secret = {$broker->secret}." : 'Error when saving!';

        return redirect()->route('admin.broker.index')->with($params);
    }

    public function update($id, UpdateBroker $request)
    {
        $broker = Broker::findOrFail($id);
        $params = [];

        $validatedData = (object)$request->validated();

        $log = "Edit Broker";
        $log .= "\nUser: " . Auth::user()->name;
        $log .= "\nOld data: " . json_encode($broker, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $oldName = $broker->name;

        $broker->name = $validatedData->name;
        $broker->url = $validatedData->url;

        $saved = $broker->save();
        $log .= "\nNew data: " . json_encode($broker, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($saved) {
            Log::info($log);
            $permission = Permission::findByName($oldName);
            $permission->name = $broker->name;
            $permission->save();

            $roles = Role::all();
            foreach ($roles as $role) {
                if (in_array($role->id, $validatedData->roles)) {
                    $role->givePermissionTo($broker->name);
                } else {
                    $role->revokePermissionTo($broker->name);
                }
            }
        } else {
            Log::error("Error when updating Broker");
        }

        $params['saved'] = $saved;
        $params['message'] = ($saved) ? 'Updated successfully.' : 'Error when updating!';

        return redirect()->route('admin.broker.index')->with($params);
    }

    public function destroy($id, DestroyBroker $request)
    {
        $broker = Broker::findOrFail($id);
        $params = [];

        $validatedData = (object)$request->validated();

        $log = "Delete Broker";
        $log .= "\nUser: " . Auth::user()->name;
        $log .= "\nOld data: " . json_encode($broker, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $saved = $broker->delete();

        if ($saved) {
            Log::info($log);

            $permission = Permission::findByName($broker->name);
            $permission->delete();
        } else {
            Log::error("Error when deleting Broker");
        }

        $params['saved'] = $saved;
        $params['message'] = ($saved) ? 'Deleted successfully.' : 'Error when deleting!';
        return redirect()->route('admin.broker.index')->with($params);
    }
}
