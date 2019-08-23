<?php

namespace App\Http\Controllers\Admin;

use App\Filters\AdminPermissionFilter;
use App\Http\Requests\AdminPermissionRequest;
use App\Http\Resources\AdminPermissionResource;
use App\Models\AdminPermission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminPermissionController extends Controller
{
    public function store(AdminPermissionRequest $request, AdminPermission $model)
    {
        $inputs = $request->validated();
        $res = $model->create($inputs);
        return $this->created(AdminPermissionResource::make($res));
    }

    public function index(Request $request, AdminPermissionFilter $filter)
    {
        $perms = AdminPermission::query()
            ->filter($filter)
            ->orderByDesc('id');
        $perms = $request->get('all') ? $perms->get() : $perms->paginate();

        return $this->ok(AdminPermissionResource::collection($perms));
    }

    public function edit(AdminPermission $adminPermission)
    {
        return $this->ok(AdminPermissionResource::make($adminPermission));
    }

    public function update(AdminPermissionRequest $request, AdminPermission $adminPermission)
    {
        $inputs = $request->validated();
        $adminPermission->update($inputs);
        return $this->created(AdminPermissionResource::make($adminPermission));
    }

    public function destroy(AdminPermission $adminPermission)
    {
        $adminPermission->delete();
        return $this->noContent();
    }
}
