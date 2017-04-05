<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/3/21
 * Time: 10:01
 */

namespace App\Http\Controllers;


use App\Data\Interest;
use App\Data\StudentInterest;
use App\MyResult;
use Illuminate\Http\Request;

class InterestController extends Controller
{

    public function getInterestListWithParams(Request $request) {
        $name = $request->get('name');
        $schoolId = $request->get('schoolId');
        $specialtyId = $request->get('specialtyId');

        $interests = Interest::select('id', 'name', 'school_id', 'specialty_id');

        if (!is_null($schoolId) && $schoolId != 0) {
            $interests = $interests->where('school_id', $schoolId);
        }

        if (!is_null($specialtyId) && $specialtyId != 0 ) {
            $interests = $interests->where('specialty_id', $specialtyId);
        }

        if (!is_null($name))
            $interests = $interests->where('name', 'like', '%'.$name.'%');

        return response()->json($interests->paginate(10));
    }

    public function getInterestDataForChart(Request $request) {
        $user = $request['user'];
        $interests = Interest::select('id', 'name');
        if (!is_null($user['school_id']) && $user['school_id'] != 0) {
            $interests = $interests->where('school_id', $user['school_id']);
        }

        if (!is_null($user['specialty_id']) && $user['specialty_id'] != 0) {
            $interests = $interests->where('specialty_id', $user['specialty_id']);
        }
        $interests = $interests->get();
        $data = [];
        $count = [];
        foreach ($interests as $interest) {
            $data['labels'][] = $interest['name'];
            $count[] = StudentInterest::where('interest_id', $interest->id)->count();
        }
        $data['series'][] = $count;

        $result = new MyResult();
        $result->data = $data;
        return response()->json($result);
    }
}