<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/2/21
 * Time: 21:41
 */

namespace App\Http\Controllers;

use App\Data\File;
use App\Data\Student;
use App\Data\StudentGeneralScore;
use App\MyResult;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class StudentScoreController extends Controller
{
    public function generalScoreMultiImport(Request $request) {
        $fileId = $request->get('fileId');
        $fileData = File::find($fileId);

        if ($fileData == null)
            return '未找到文件';

        $extension = $fileData['extension'];
        if ($extension != 'xls' && $extension != 'xlsx')
            return '格式不正确';

        $path = storage_path('app/'.$fileData['path']);

        $sheets = Excel::load($path)->all();
        $sheet = $sheets[0];
        $insertCount = 0;
        $updateCount = 0;

        foreach ($sheet as $row) {

            $studentNumber = $row['student_number'];
            $student = Student::where('student_number', $studentNumber)->first();

            if (!is_null($student)) {
                $year = $row['year'];

                $studentGeneralScore = StudentGeneralScore::where('student_id', $student['id'])->where('year', $year)->first();

                if (is_null($studentGeneralScore)) {
                    $studentGeneralScore = new StudentGeneralScore();
                    $insertCount ++;
                } else {
                    $updateCount ++;
                }

                $studentGeneralScore['student_id'] = $student['id'];
                $studentGeneralScore['R1'] = $row['r1'];
                $studentGeneralScore['R2'] = $row['r2'];
                $studentGeneralScore['R3'] = $row['r3'];
                $studentGeneralScore['R31'] = $row['r31'];
                $studentGeneralScore['R32'] = $row['r32'];
                $studentGeneralScore['R33'] = $row['r33'];
                $studentGeneralScore['total'] = $row['total'];
                $studentGeneralScore['grade'] = $row['grade'];
                $studentGeneralScore['year'] = $row['year'];

                $studentGeneralScore->save();
            }
        }

        return '共插入'.$insertCount.'条数据;'.'共更新'.$updateCount.'条数据';
    }

    public function generalScoreWithStudentId(Request $request) {
        $result = new MyResult();

        $id = $request->get('id');

        if (!is_null($id)) {
            $result->data = StudentGeneralScore::find($id);
        } else {
            $result->code = '100';
            $result->message = '缺少参数';
        }

        return response()->json($result);
    }

    public function updateStudentScore() {
        $result = new MyResult();
        $students = Student::all();

        foreach ($students as $student) {
            $score = StudentGeneralScore::where('student_id', $student['id'])->avg('total');
            $student['score'] = $score;
            $student->save();
        }

        return response()->json($result);
    }
}