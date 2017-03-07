<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2016/12/28
 * Time: 20:16
 */

namespace App\Http\Controllers;


use App\Data\Menu;
use App\Data\Route;
use App\Data\User;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{

    public function user() {
        return User::with('roles')->get();
    }

    public function menuTree() {
        Log::debug('666');
        return User::where('id', '1')->first();
    }

}