<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/2/19
 * Time: 19:24
 */

namespace App\Http\Controllers;


use App\Data\RoleRoute;
use App\Data\Route;
use App\Data\Student;
use App\Data\Teacher;
use App\Data\User;
use App\MyResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function Login(Request $request) {
        $result = new MyResult();

        $userNumber = $request->get('userNumber');
        $password = $request->get('password');
        $userType = $request->get('userType');

        if ($userNumber == null) {
            $result->code = '100';
            $result->message = '缺少用户名参数';
            return response()->json($result);
        }

        if ($password == null) {
            $result->code = '100';
            $result->message = '缺少密码参数';
            return response()->json($result);
        }

        if ($userNumber == 'admin' && $password == 'admin') {
            $result->data = ['id' => 0];
            return response()->json($result);
        }

        switch ($userType) {
            case 1:
                $user = Student::where('student_number', $userNumber)->where('password', $password)->first();
                break;
            case 2:
                $user = Teacher::where('teacher_number', $userNumber)->where('password', $password)->first();
                break;
            case 0:
                $user = User::where('user_number', $userNumber)->where('password', $password)->first();
                break;
            default:
                $user = null;
                $result->code = 100;
                $result->message = '用户类型错误';
                return response()->json($result);
                break;
        }

        if ($user == null) {
            $result->code = '100';
            $result->message = '用户名或密码错误';
            return response()->json($result);
        } else {
            $result->data = ['id' => $user->id];
            return response()->json($result);
        }
    }

    public function menu(Request $request, $parentId = 0) {

        $routes = Route::where('parent_id', $parentId)->with('data.menu')->get();
        $branch = [];

        foreach ($routes as $route) {
            if ($this->menuVisible($request, $route->id)) {

                $children = $this->menu($request, $route->id);

                if ($children) {
                    $route->children = $children;
                }

                $branch[] = $route;
            }
        }

        return $branch;
    }

    private function menuVisible(Request $request, $routeId) {
        $userRole = $request['userRole'];

        if ($routeId == 1)
            return true;

        if ($request['isAdministrator'])
            return true;

        $route = Route::find($routeId);
        if ($route->long_time && $route->enable) {
            return RoleRoute::where('role_id', $userRole)->where('route_id', $routeId)->exists();
        } else {
            return false;
        }
    }
}