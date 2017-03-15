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
use App\Data\School;
use App\Data\Sex;
use App\Data\Specialty;

class CodeController extends Controller
{

    public function professionalTitleList() {
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
//        foreach ($schools as $school) {
//            $school['specialties'] = Specialty::where('school_id', $school->id)->get();
//            foreach ($school['specialties'] as $specialty) {
//                $specialty['classes'] = ClassInfo::where('specialty_id', $specialty->id)->get();
//            }
//        }
        return response()->json($schools);
    }

    public function specialty() {
        $specialties = Specialty::orderBy('school_id', 'asc')->get();
//        foreach ($specialties as $specialty) {
//            $specialty['classes'] = ClassInfo::where('specialty_id', $specialty->id)->get();
//        }
        return response()->json($specialties);
    }

    public function classInfo() {
        return response()->json(ClassInfo::all());
    }
}