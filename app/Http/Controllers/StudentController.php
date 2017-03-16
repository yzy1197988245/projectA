<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/2/7
 * Time: 22:29
 */

namespace App\Http\Controllers;

use App\Data\ClassInfo;
use App\Data\File;
use App\Data\Interest;
use App\Data\School;
use App\Data\Sex;
use App\Data\Specialty;
use App\Data\Student;
use App\Data\StudentGeneralScore;
use App\Data\StudentInterest;
use App\Data\Teacher;
use App\Data\TeacherStudentOrder;
use App\MyResult;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    public function studentList() {
        return Student::select('id', 'name')->get();
    }

    public function multiImport(Request $request) {
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

            if ($student == null) {
                $student = new Student();
                $insertCount++;
            } else {
                $updateCount++;
            }

            $student['name'] = $row['name'];
            $student['sex'] = $row['sex'];
            $student['student_number'] = $row['student_number'];
            $student['class'] = $row['class'];

            $student->save();
        }

        return '共插入'.$insertCount.'条数据;'.'共更新'.$updateCount.'条数据';
    }

    public function exportToExcel() {
        Excel::create('学生信息', function ($excel) {
            $excel->sheet('sheet1', function ($sheet) {
                $sheet->fromModel(Student::all());
            });
        })->download('xlsx');
    }

    public function updateDescription(Request $request) {
        $result = new MyResult();
        $userId = $request->get('userId');

        if ($userId != null) {
            $student = Student::find($userId);
            if ($student != null) {

                $student['description'] = $request->get('description');
                $student['interest_teacher_id'] = $request->get('interestTeacher');

                $interests = $request->get('interests');

                StudentInterest::where('student_id', $student->id)->delete();

                foreach ($interests as $interest) {
                    $studentInterest = new StudentInterest();
                    $studentInterest['student_id'] = $student->id;
                    $studentInterest['interest_id'] = $interest;
                    $studentInterest->save();
                }

                $student->save();
            } else {
                $result->code = 100;
                $result->message = '未找到该学生';
            }
        } else {
            $result->code = 100;
            $result->message = '缺少学生id';
        }

        return response()->json($result);
    }

    public function studentListWithOrder(Request $request) {
        $teacherId = $request->get('teacherId');
        if ($teacherId != null) {
            $students = [];
            $studentIds = TeacherStudentOrder::select('student_id')->where('teacher_id', $teacherId)->orderBy('order', 'asc')->get();
            for ($i = 0; $i < count($studentIds); $i++) {
                $student = Student::select('id', 'name', 'interest_teacher_id')->where('id', $studentIds[$i]->student_id)->first();
                $student['interested'] = $student->interest_teacher_id == $teacherId;
                $student['order'] = $i+1;
                $students[] = $student;
            }
            return response()->json($students);
        }
        return response()->json([]);
    }

    public function simpleList() {
        return response()->json(Student::select('id', 'student_number', 'name')->paginate(10));
    }

    public function detail(Request $request) {
        $id = $request->get('id');
        $student = Student::find($id);
        $theStudent = [];
        $theStudent['name'] = $student->name;
        $theStudent['sex'] = Sex::find($student->sex_id)['name'];
        $theStudent['studentNumber'] = $student->student_number;
        $theStudent['specialty'] = Specialty::find($student->specialty_id)['name'];
        $theStudent['school'] = School::find($student->school_id)['name'];
        $theStudent['class'] = ClassInfo::find($student->class_id)['name'];
        $theStudent['description'] = $student->description;
        $theStudent['interestTeacher'] = Teacher::find($student->interest_teacher_id)['name'];

        $studentInterests = StudentInterest::where('student_id', $student->id)->get();
        $interests = [];
        foreach ($studentInterests as $studentInterest) {
            $interests[] = Interest::find($studentInterest->interest_id);
        }

        $theStudent['interests'] = $interests;
        $theStudent['generalScores'] = StudentGeneralScore::where('student_id', $student->id)->get();
        $result = new MyResult();
        $result->data = $theStudent;
        return response()->json($theStudent);
    }

    public function updateStudentOrder(Request $request) {
        $teacherId = $request->get('teacherId');
        $students = $request->get('order');
        for ($i = 0; $i < count($students); $i++) {
            $teacherStudentOrder = TeacherStudentOrder::where('teacher_id', $teacherId)->where('student_id', $students[$i])->first();
            if ($teacherStudentOrder == null)
                $teacherStudentOrder = new TeacherStudentOrder();
            $teacherStudentOrder['teacher_id'] = $teacherId;
            $teacherStudentOrder['student_id'] = $students[$i];
            $teacherStudentOrder['order'] = $i + 1;
            $teacherStudentOrder->save();
        }
        return response()->json(new MyResult());
    }

    public function getStudentBaseInfo(Request $request) {
        $result = new MyResult();
        $studentId = $request->get('studentId');

        if (!is_null($studentId)) {

            $student = Student::find($studentId);
            if (!is_null($student)) {
                $theStudent = [];
                $theStudent['description'] = $student['description'];
                $theStudent['interestTeacher'] = Teacher::select('id', 'name')->find($student['interest_teacher_id']);
                $studentInterests = StudentInterest::where('student_id', $student['id'])->get();
                $interests = [];
                foreach ($studentInterests as $studentInterest) {
                    $interest = Interest::find($studentInterest['interest_id']);
                    $interest['text'] = $interest['name'];
                    $interests[] = $interest;
                }
                $theStudent['interests'] = $interests;
                $result->data = $theStudent;
            } else {
                $result->code = 100;
                $result->message = '未找到该学生';
            }

        } else {
            $result->code = 100;
            $result->message = '缺少参数';
        }

        return response()->json($result);
    }

    public function getStudentListWithoutSelectedProject() {
        $result = new MyResult();

        $result->data = Student::select('id', 'name')->whereNull('selected_project_id')->get();

        return response()->json($result);
    }

    /**
     * Admin
     */

    public function adminGetStudentListWithParams(Request $request) {
        $studentNumber = $request->get('studentNumber');
        $name = $request->get('name');
        $schoolId = $request->get('schoolId');
        $specialtyId = $request->get('specialtyId');
        $grade = $request->get('grade');
        $classId = $request->get('classId');

        $students = Student::select('student_number', 'name', 'sex_id', 'school_id', 'specialty_id', 'class_id', 'grade_year', 'year');

        if ($schoolId != 0)
            $students = $students->where('school_id', $schoolId);

        if ($specialtyId != 0)
            $students = $students->where('specialty_id', $specialtyId);

        if ($grade != 0)
            $students = $students->where('grade_year', $grade);

        if ($classId != 0)
            $students = $students->where('class_id', $classId);

        $students = $students->where('student_number', 'like', '%'.$studentNumber.'%');
        $students = $students->where('name', 'like', '%'.$name.'%');

        $students = $students->orderBy('school_id', 'asc');

        return response()->json($students->paginate(10));
    }

    public function adminCreateStudent(Request $request) {
        $this->validate($request, [
            'studentNumber' =>  'required|unique:student,student_number',
            'name' => 'required',
            'sexId' => 'required',
            'schoolId' => 'required',
            'specialtyId' => 'required',
            'grade' => 'required',
            'classId' => 'required',
            'year' => 'required'
        ]);

        $student = new Student();
        $student['student_number'] = $request->get('studentNumber');
        $student['name'] = $request->get('name');
        $student['sex_id'] = $request->get('sexId');
        $student['school_id'] = $request->get('schoolId');
        $student['specialty_id'] = $request->get('specialtyId');
        $student['grade_year'] = $request->get('grade');
        $student['class_id'] = $request->get('classId');
        $student['year'] = $request->get('year');

        $student->save();

        $result = new MyResult();

        return response()->json($result);
    }

    public function adminUpdateStudent(Request $request) {
        $this->validate($request, [
            'id' => 'required',
            'studentNumber' =>  'required|unique:student,student_number',
            'name' => 'required',
            'sexId' => 'required',
            'schoolId' => 'required',
            'specialtyId' => 'required',
            'gradeYear' => 'required',
            'classId' => 'required',
            'year' => 'required'
        ]);

        $result = new MyResult();

        $student = Student::find($request->get('id'));

        if (is_null($student)) {
            $result->code = 100;
            $result->message = '没有找到该学生';
            return response()->json($result);
        }

        $student['student_number'] = $request->get('studentNumber');
        $student['name'] = $request->get('name');
        $student['sex_id'] = $request->get('sexId');
        $student['school_id'] = $request->get('schoolId');
        $student['specialty_id'] = $request->get('specialtyID');
        $student['grade_year'] = $request->get('gradeYear');
        $student['class_id'] = $request->get('classId');
        $student['year'] = $request->get('year');

        $student->save();

        return response()->json($result);
    }

    public function adminImportStudentFromFile(Request $request) {
        $result = new MyResult();

        $fileId = $request->get('fileId');
        $fileData = File::find($fileId);

        if ($fileData == null)
            return $result->error('未找到文件');

        $extension = $fileData['extension'];
        if ($extension != 'xls' && $extension != 'xlsx')
            return $result->error('文件格式不正确');

        $path = storage_path('app/'.$fileData['path']);

        $sheets = Excel::load($path)->all();
        $sheet = $sheets[0];
        $insertCount = 0;
        $updateCount = 0;

        foreach ($sheet as $row) {

            $studentNumber = $row['student_number'];
            $name = $row['name'];
            $sex = $row['sex'];
            $school = $row['school'];
            $specialty = $row['specialty'];
            $class = $row['class'];
            $grade = $row['grade'];
            $year = $row['year'];

            $sexData = Sex::where('name', $sex)->first();
            if (is_null($sexData)) {
                $sexData = new Sex();
                $sexData['name'] = $sex;
                $sexData->save();
            }
            $sexId = $sexData->id;

            $schoolData = School::where('name', $school)->first();
            if (is_null($schoolData)) {
                $schoolData = new School();
                $schoolData['name'] = $school;
                $schoolData->save();
            }
            $schoolId = $schoolData->id;

            $specialtyData = Specialty::where('name', $specialty)->first();
            if (is_null($specialtyData)) {
                $specialtyData = new Specialty();
                $specialtyData['name'] = $specialty;
                $specialtyData['school_id'] = $schoolId;
                $specialtyData->save();
            }
            $specialtyId = $specialtyData->id;

            $classData = ClassInfo::where('name', $class)->first();
            if (is_null($classData)) {
                $classData = new ClassInfo();
                $classData['name'] = $class;
                $classData['school_id'] = $schoolId;
                $classData['specialty_id'] = $specialtyId;
                $classData['year'] = $grade;
                $classData->save();
            }
            $classId = $classData->id;

            $student = Student::where('student_number', $studentNumber)->first();

            if ($student == null) {
                $student = new Student();
                $insertCount++;
            } else {
                $updateCount++;
            }

            $student['name'] = $name;
            $student['sex_id'] = $sexId;
            $student['student_number'] = $studentNumber;
            $student['class_id'] = $classId;
            $student['specialty_id'] = $specialtyId;
            $student['school_id'] = $schoolId;
            $student['grade_year'] = $grade;
            $student['year'] = $year;

            $student->save();
        }

        return $result->success('共插入'.$insertCount.'条数据;'.'共更新'.$updateCount.'条数据');
    }
}