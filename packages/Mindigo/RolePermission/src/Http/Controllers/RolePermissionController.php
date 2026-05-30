<?php

namespace Mindigo\RolePermission\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Mindigo\RolePermission\Http\Requests\RolePermissionRequest;
use Mindigo\RolePermission\Services\RolePermissionService;

class RolePermissionController extends Controller
{
    public function index()
    {
        RolePermissionService::syncDefaults();

        $roles = RolePermissionService::roleLabels();
        $roleDescriptions = RolePermissionService::roleDescriptions();
        $permissionGroups = RolePermissionService::permissionGroups();
        $permissionMap = RolePermissionService::permissionMap();
        $totalPermissions = count(RolePermissionService::flatPermissions());

        return view('Mindigo-role-permission::index', compact(
            'roles',
            'roleDescriptions',
            'permissionGroups',
            'permissionMap',
            'totalPermissions'
        ));
    }

    public function update(RolePermissionRequest $request): RedirectResponse
    {
        RolePermissionService::syncDefaults();

        $oldMap = RolePermissionService::permissionMap();
        $newMap = RolePermissionService::updatePermissionMap($request->submittedPermissions());

        if (class_exists(\Mindigo\AuditLog\Services\AuditLogService::class)) {
            app(\Mindigo\AuditLog\Services\AuditLogService::class)->record(
                'update',
                'role_permission',
                ['permissions' => $oldMap],
                ['permissions' => $newMap],
            );
        }

        return back()->with('success', __('Mindigo-role-permission::app.saved'));
    }
}
