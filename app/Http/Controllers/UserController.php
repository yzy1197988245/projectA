<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/1/23
 * Time: 11:36
 */

namespace App\Http\Controllers;


use App\Data\Route;
use App\Data\User;
use App\Data\UserRole;
use App\MyResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function userList() {
        return User::with('roles')->get();
    }

    public function create(Request $request) {
        $name = $request->get('name');
        $userName = $request->get('userName');
        $password = $request->get('password');

        if ($name != null && $userName != null && $password != null) {
            $user = new User();
            $user['name'] = $name;
            $user['userName'] = $userName;
            $user['password'] = $password;
            $user->save();
        }

        return response()->json(new MyResult());
    }

    public function delete(Request $request) {
        $id = $request->get('id');

        if ($id != null) {
            $user = User::find($id);

            if ($user != null) {
                $detailTable = $user['detailTable'];
                $detailId = $user['detailId'];

                if ($detailTable != null && $detailId != null) {
                    $detail = DB::table($detailTable)->where('id', $detailId)->get();
                    if ($detailId != null)
                        $detail->delete();
                }

                $userRoles = UserRole::where('user_id', $user['id'])->get();

                foreach ($userRoles as $userRole) {
                    $userRole->delete();
                }

                $user->delete();
            }
        }

        return response()->json(new MyResult());
    }

    public function adminGetUserListWithParams(Request $request) {
        $userNumber = $request->get('userNumber');
        $name = $request->get('name');
        $roleId = $request->get('roleId');
        $schoolId = $request->get('schoolId');
        $specialtyId = $request->get('specialtyId');

        $users = User::select('user_number', 'name', 'role_id', 'school_id', 'specialty_id');

        if (!is_null($roleId) && $roleId != 0)
            $users = $users->where('role_id', $roleId);

        if (!is_null($schoolId) && $schoolId != 0)
            $users = $users->where('school_id', $schoolId);

        if (!is_null($specialtyId) && $specialtyId != 0)
            $users = $users->where('specialty_id', $specialtyId);

        $users = $users->where('user_number', 'like', '%'.$userNumber.'%');
        $users = $users->where('name', 'like', '%'.$name.'%');

        return response()->json($users->paginate(10));
    }

    public function adminCreateUser(Request $request) {
        $this->validate($request, [
            'userNumber' =>  'required|unique:user,user_number',
            'name' => 'required',
            'schoolId' => 'required',
            'specialtyId' => 'required',
            'roleId' => 'required'
        ]);

        $user = new User();

        $user['user_number'] = $request->get('userNumber');
        $user['name'] = $request->get('name');
        $user['role_id'] = $request->get('roleId');
        $user['school_id'] = $request->get('schoolId');
        $user['specialty_id'] = $request->get('specialtyId');
        $user['password'] = $request->get('password');

        $user->save();

        $result = new MyResult();

        return response()->json($result);
    }

    public function userModifyPassword(Request $request) {
        $old = $request->get('old');
        $new = $request->get('new');
        $user = $request['user'];
        $result = new MyResult();
        if ($old != $user['password']) {
            return $result->error('旧密码输入错误');
        } else {
            $user['password'] = $new;
            $user->save();
            return $result->success('修改成功');
        }
    }
}