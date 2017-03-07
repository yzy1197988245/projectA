<?php

namespace App\Http\Middleware;

use App\Data\Student;
use App\Data\Teacher;
use App\Data\User;
use App\MyResult;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $result = new MyResult();

        $userId = $request->header('userId');
        $userType = $request->header('userType');

        $request['isTeacher'] = false;
        $request['isStudent'] = false;
        $request['isAdministrator'] = false;

        if ($userId == 0) {
            $request['isAdministrator'] = true;
            return $next($request);
        }

        if (is_null($userId) || is_null($userType)) {
            $result->code = 100;
            $result->message = '缺少参数';
            return response()->json($result);
        } else {
            $user = null;
            switch ($userType) {
                case 0:
                    $user = User::find($userId);
                    $request['userRole'] = $user->role_id;
                    break;
                case 1:
                    $user = Student::find($userId);
                    $request['isStudent'] = true;
                    $request['userRole'] = 1;
                    break;
                case 2:
                    $user = Teacher::find($userId);
                    $request['isTeacher'] = true;
                    $request['userRole'] = 2;
                    break;
                default:
                    $result->code = 100;
                    $result->message = '用户类型错误';
                    return response()->json($result);
                    break;
            }

            if (is_null($user)) {
                $result->code = 100;
                $result->message = '用户没找到';
                return response()->json($result);
            } else {
                $request['user'] = $user;
            }
        }

        return $next($request);
    }
}