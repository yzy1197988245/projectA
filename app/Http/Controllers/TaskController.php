<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/3/5
 * Time: 20:08
 */

namespace App\Http\Controllers;


use App\Data\Task;
use App\MyResult;
use Illuminate\Http\Request;

class TaskController extends Controller
{

    public function getTaskList(Request $request) {
        $result = new MyResult();
        $roleId = $request['userRole'];

        $result->data = Task::where("role_id", $roleId)->get();

        return response()->json($result);
    }
}