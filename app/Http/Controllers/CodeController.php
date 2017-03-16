<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/2/23
 * Time: 16:09
 */

namespace App\Http\Controllers;


use App\Data\ClassInfo;
use App\Data\Interest;
use App\Data\ProfessionalTitle;
use App\Data\Role;
use App\Data\School;
use App\Data\Sex;
use App\Data\Specialty;

class CodeController extends Controller
{

    public function professionalTitle() {
        return response()->json(ProfessionalTitle::all());
    }

    public function interest() {
        return response()->json(Interest::all());
    }

    public function sex() {
        return response()->json(Sex::all());
    }

    public function school() {
        $schools = School::orderBy('id', 'asc')->get();
        return response()->json($schools);
    }

    public function specialty() {
        $specialties = Specialty::orderBy('school_id', 'asc')->get();
        return response()->json($specialties);
    }

    public function classInfo() {
        return response()->json(ClassInfo::all());
    }

    public function role() {
        return response()->json(Role::all());
    }
}