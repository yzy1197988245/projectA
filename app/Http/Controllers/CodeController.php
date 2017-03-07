<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/2/23
 * Time: 16:09
 */

namespace App\Http\Controllers;


use App\Data\Interest;
use App\Data\ProfessionalTitle;

class CodeController extends Controller
{

    public function professionalTitleList() {
        return response()->json(ProfessionalTitle::all());
    }

    public function interest() {
        return response()->json(Interest::all());
    }
}