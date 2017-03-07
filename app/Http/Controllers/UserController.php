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
}