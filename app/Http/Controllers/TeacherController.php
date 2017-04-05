<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/2/21
 * Time: 23:03
 */

namespace App\Http\Controllers;


use App\Data\File;
use App\Data\ProfessionalTitle;
use App\Data\School;
use App\Data\Sex;
use App\Data\Specialty;
use App\Data\Student;
use App\Data\Teacher;
use App\Data\TeacherStudentOrder;
use App\MyResult;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TeacherController extends Controller
{
    public function initStudentOrder() {
        $result = new MyResult();

        $teachers = Teacher::where('availability', 1)->get();
        foreach ($teachers as $teacher) {
            $students = Student::orderBy('score', 'desc')->get();
            $order = 1;
            foreach ($students as $student) {
                $teacherStudentOrder = TeacherStudentOrder::where('teacher_id', $teacher->id)
                    ->where('student_id', $student->id)
                    ->first();

                if (is_null($teacherStudentOrder))
                    $teacherStudentOrder = new TeacherStudentOrder();

                $teacherStudentOrder['teacher_id'] = $teacher->id;
                $teacherStudentOrder['student_id'] = $student->id;
                $teacherStudentOrder['order'] = $order;

                $teacherStudentOrder->save();

                $order ++;
            }
        }

        return response()->json($result);
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

            $teacherNumber = $row['teacher_number'];
            $teacher = Teacher::where('teacher_number', $teacherNumber)->first();

            if ($teacher == null) {
                $teacher = new Teacher();
                $insertCount++;
            } else {
                $updateCount++;
            }

            $teacher['teacher_number'] = $row['teacher_number'];
            $teacher['name'] = $row->name;
            $teacher->sex = $row->sex;
            $teacher->professional_title = $row->professional_title;
            $teacher->specialty = $row->specialty;
            $teacher->remarks = $row->remarks;

            $teacher->save();

        }

        return '共插入'.$insertCount.'条数据;'.'共更新'.$updateCount.'条数据';
    }

    public function simpleList(Request $request) {
        return response()->json(Teacher::select('id', 'name', 'professional_title_id')->paginate(10));
    }

    public function adminGetTeacherListWithParams(Request $request) {
        $teacherNumber = $request->get('teacherNumber');
        $name = $request->get('name');
        $schoolId = $request->get('schoolId');
        $specialtyId = $request->get('specialtyId');
        $professionalTitleId = $request->get('professionalTitleId');

        $teachers = Teacher::select('id', 'teacher_number', 'name', 'sex_id', 'school_id', 'specialty_id', 'professional_title_id');

        if ($schoolId != 0)
            $teachers = $teachers->where('school_id', $schoolId);

        if ($specialtyId != 0)
            $teachers = $teachers->where('specialty_id', $specialtyId);

        if ($professionalTitleId != 0)
            $teachers = $teachers->where('professional_title_id', $professionalTitleId);

        $teachers = $teachers->where('teacher_number', 'like', '%'.$teacherNumber.'%');
        $teachers = $teachers->where('name', 'like', '%'.$name.'%');

        return response()->json($teachers->paginate(10));
    }

    public function adminCreateTeacher(Request $request) {
        $this->validate($request, [
            'teacherNumber' =>  'required|unique:teacher,teacher_number',
            'name' => 'required',
            'sexId' => 'required',
            'schoolId' => 'required',
            'specialtyId' => 'required',
            'professionalTitleId' => 'required',
            'available' => 'required'
        ]);

        $teacher = new Teacher();
        $teacher['teacher_number'] = $request->get('teacherNumber');
        $teacher['name'] = $request->get('name');
        $teacher['sex_id'] = $request->get('sexId');
        $teacher['school_id'] = $request->get('schoolId');
        $teacher['specialty_id'] = $request->get('specialtyId');
        $teacher['professional_title_id'] = $request->get('professionalTitleId');
        $teacher['phone'] = $request->get('phone');
        $teacher['email'] = $request->get('email');
        $teacher['remarks'] = $request->get('remarks');
        $teacher['availability'] = $request->get('available');

        $teacher->save();

        $result = new MyResult();

        return response()->json($result);
    }

    public function adminImportTeacherFromFile(Request $request) {
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

            $teacherNumber = $row['teacher_number'];
            $name = $row['name'];
            $sex = $row['sex'];
            $school = $row['school'];
            $specialty = $row['specialty'];
            $professionalTitle = $row['professional_title'];

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

            $professionalTitleData = ProfessionalTitle::where('name', $professionalTitle)->first();
            if (is_null($professionalTitleData)) {
                $professionalTitleData = new ProfessionalTitle();
                $professionalTitleData['name'] = $professionalTitle;
                $professionalTitleData->save();
            }
            $professionalTitleId = $professionalTitleData->id;

            $teacher = Teacher::where('teacher_number', $teacherNumber)->first();

            if ($teacher == null) {
                $teacher = new Teacher();
                $insertCount++;
            } else {
                $updateCount++;
            }

            $teacher['name'] = $name;
            $teacher['sex_id'] = $sexId;
            $teacher['teacher_number'] = $teacherNumber;
            $teacher['specialty_id'] = $specialtyId;
            $teacher['school_id'] = $schoolId;
            $teacher['professional_title_id'] = $professionalTitleId;

            $teacher->save();
        }

        return $result->success('共插入'.$insertCount.'条数据;'.'共更新'.$updateCount.'条数据');
    }
}