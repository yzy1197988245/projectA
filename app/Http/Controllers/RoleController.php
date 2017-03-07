<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/1/31
 * Time: 20:38
 */

namespace App\Http\Controllers;


use App\Data\Role;
use App\Data\RoleRoute;
use App\Data\Route;
use App\Data\UserRole;
use App\MyResult;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function roleList() {
        return Role::all();
    }

    public function listWithUser(Request $request) {
        $userId = $request->get('userId');
        $roles = Role::all();

        if ($userId != null) {

            foreach ($roles as $role) {
                $userRole = UserRole::where('user_id', $userId)->where('role_id', $role['id'])->first();
                if ($userRole == null)
                    $role['enable'] = false;
                else
                    $role['enable'] = true;
            }
        }

        return $roles;
    }

    public function createOrUpdate(Request $request) {
        $id = $request->get('id');

        if ($id != null) {
            $role = Role::find($id);
        } else {
            $role = new Role();
        }

        $name = $request->get('name');

        if ($name != null)
            $role->name = $name;

        $role->save();

        return response()->json(new MyResult());
    }

    public function routes(Request $request, $parentId = 0, $userId = 1) {
        $routes = Route::where('parent_id', $parentId)->with('data.menu')->get();
        $branch = [];

        $role_id = $request->get('id');

        foreach ($routes as $route) {
            $children = $this->routes($request, $route->id, $userId);

            if ($role_id != null) {
                if(RoleRoute::where('role_id', $role_id)->where('route_id', $route->id)->exists())
                    $route->enable = true;
                else
                    $route->enable = false;
            } else {
                $route->enable = false;
            }

            if ($children) {
                $route->children = $children;
            }

            $branch[] = $route;
        }

        return $branch;
    }

    public function addPower(Request $request) {
        $route_id = $request->get('route_id');
        $role_id = $request->get('role_id');

        if ($route_id != null && $role_id != null)

        $role_route = new RoleRoute();
        $role_route['route_id'] = $route_id;
        $role_route['role_id'] = $role_id;

        $role_route->save();

        return response()->json(new MyResult());
    }

    public function deletePower(Request $request) {
        $role_id = $request->get('role_id');
        $route_id = $request->get('route_id');

        if ($role_id != null && $route_id != null) {
            $powers = RoleRoute::where('role_id', $role_id)->where('route_id', $route_id)->get();

            foreach ($powers as $power)
                $power->delete();
        }

        return response()->json(new MyResult());
    }

    public function deleteRole(Request $request) {
        $id = $request->get('id');
        $result = new MyResult();

        if ($id == 0) {
            $result->message = '超级管理员无法删除';
            return response()->json($result);
        }

        if ($id != null) {
            $role = Role::find($id);
            if ($role != null)
                $role->delete();

            $roleRoutes = RoleRoute::where('role_id', $id)->get();

            foreach ($roleRoutes as $roleRoute) {
                $roleRoute->delete();
            }
        }

        return response()->json($result);
    }

    public function addRoleToUser(Request $request) {
        $userId = $request->get('userId');
        $roleId = $request->get('roleId');

        if ($userId != null && $roleId != null) {
            $userRole = new UserRole();
            $userRole['user_id'] = $userId;
            $userRole['role_id'] = $roleId;
            $userRole->save();
        }

        return response()->json(new MyResult());
    }

    public function deleteUserRole(Request $request) {
        $userId = $request->get('userId');
        $roleId = $request->get('roleId');

        if ($userId != null && $roleId != null) {
            $userRoles = UserRole::where('user_id', $userId)->where('role_id', $roleId)->get();
            foreach ($userRoles as $userRole) {
                $userRole->delete();
            }
        }

        return response()->json(new MyResult());
    }
}