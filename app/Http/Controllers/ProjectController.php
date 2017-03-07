<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/1/28
 * Time: 17:26
 */

namespace App\Http\Controllers;

use App\Data\Interest;
use App\Data\Project;
use App\Data\ProjectFile;
use App\Data\ProjectInterest;
use App\Data\Student;
use App\Data\StudentProject;
use App\Data\Teacher;
use App\Data\TeacherStudentOrder;
use App\MyResult;
use Illuminate\Http\Request;

class ProjectController extends Controller
{

    public function createOrUpdate(Request $request) {
        $result = new MyResult();
        $projectId = $request->get('projectId');

        if (!$request['isTeacher']) {
            $result->code = 100;
            $result->message = '您没有权限';
            return response()->json($result);
        }

        if ($projectId == null) {
            $project = new Project();
            $result->message = '创建成功';
        } else {
            $project = Project::find($projectId);
            if ($project == null) {
                $project = new Project();
                $result->message = '创建成功';
            } else {
                $result->message = '更新成功';
            }
        }

        $title = $request->get('title');
        $description = $request->get('description');
        $creator = $request['user']->id;
        $interests = $request->get('interests');
        $type = 0;

        if ($title != null && $description != null && $creator != null) {
            $project['title'] = $title;
            $project['description'] = $description;
            $project['creator_id'] = $creator;
            $project['specialty_id'] = $request['user']['specialty_id'];
            $project['school_id'] = $request['user']['school_id'];
            $project['instructor_id'] = $creator;
            $project['type'] = $type;
            $project->save();

            ProjectInterest::where('project_id', $project->id)->delete();

            foreach ($interests as $interest) {
                $projectInterest = new ProjectInterest();
                $projectInterest['project_id'] = $project->id;
                $projectInterest['interest_id'] = $interest;
                $projectInterest->save();
            }

        } else {
            $result->code = 100;
            $result->message = '缺少参数';
        }

        return response()->json($result);
    }

    public function deleteProject(Request $request) {
        $result = new MyResult();
        $projectId = $request->get('projectId');

        if (is_null($projectId)) {
            $result->code = 100;
            $result->message = '缺少参数';
            return response()->json($result);
        }

        Project::where('id', $projectId)->delete();
        ProjectInterest::where('project_id', $projectId)->delete();
        ProjectFile::where('project_id', $projectId)->delete();

        $students = Student::where('selected_project_id', $projectId)->get();

        foreach ($students as $student) {
            $student['selected_project'] = null;
            $student->save();
        }

        StudentProject::where('project_id', $projectId)->delete();

        $result->message = '删除成功';

        return response()->json($result);
    }

    public function getTeacherProjectList(Request $request) {
        $teacherId = $request->get('teacherId');
        $result = new MyResult();

        if (is_null($teacherId)) {
            $result->code = 100;
            $result->message = '缺少参数';
            return response()->json($result);
        }

        $result->data = Project::select('id', 'title')->where('type', 0)->where('creator_id', $teacherId)->get();

        return response()->json($result);
    }

    public function getTeacherProjectDetail(Request $request) {
        $projectId = $request->get('projectId');
        $result = new MyResult();

        if (is_null($projectId)) {
            $result->code = 100;
            $result->message = '缺少参数';
            return response()->json($result);
        }

        $project = Project::find($projectId);

        if (is_null($project)) {
            $result->code = 100;
            $result->message = '未找到Project';
            return response()->json($result);
        }

        $theProject = [];
        $theProject['title'] = $project->title;
        $theProject['description'] = $project->description;

        $interests = [];

        $projectInterests = ProjectInterest::where('project_id', $project->id)->get();
        foreach ($projectInterests as $projectInterest) {
            $interest = Interest::find($projectInterest->interest_id);
            $interest->text = $interest->name;
            $interests[] = $interest;
        }

        $theProject['interests'] = $interests;

        $result->data = $theProject;

        return response()->json($result);
    }

    public function studentCreate(Request $request) {
        $result = new MyResult();

        $creatorId = $request->get('creatorId');
        $captain = Student::find($creatorId);

        if (is_null($captain)) {
            $result->code = 100;
            $result->message = '未找到该学生';
            return response()->json($result);
        }

        $project = Project::where('type', 1)->where('creator_id', $captain->id)->first();

        if (is_null($project)) {
            if (!is_null($captain->selected_project_id)) {
                $result->code = 100;
                $result->message = '您已选题，无法创建题目';
                return response()->json($result);
            } else {
                $project = new Project();
                $result->message = '创建成功';
            }
        } else {
            $result->message = '更新成功';
        }

        $title = $request->get('title');
        $instructor = $request->get('instructor');
        $description = $request->get('description');
        $members = $request->get('members');
        $type = 1;

        if ($title != null && $instructor != null && $description != null && $members != null) {

            $project['title'] = $title;
            $project['instructor_id'] = $instructor;
            $project['description'] = $description;
            $project['creator_id'] = $creatorId;
            $project['captain_id'] = $creatorId;
            $project['school_id'] = $request['user']['school_id'];
            $project['specialty_id'] = $request['user']['specialty_id'];
            $project['type'] = $type;

            $project->save();

            $captain['selected_project_id'] = $project->id;
            $captain->save();

            $students = Student::where('selected_project_id', $project->id)->get();
            foreach ($students as $student) {
                $student->selected_project_id = null;
                $student->save();
            }

            foreach ($members as $member) {
                $student = Student::find($member);
                if ($student != null) {
                    $student['selected_project_id'] = $project->id;
                    $student->save();
                }
            }

        } else {
            $result->code = '100';
            $result->message = '缺少参数';
        }

        return response()->json($result);
    }

    public function getStudentCreateProjectBaseInfo(Request $request) {
        $studentId = $request->get('studentId');
        $result = new MyResult();

        if (!is_null($studentId)) {
            $student = Student::find($studentId);
            if (!is_null($student)) {
                $project = Project::where('type', 1)->where('creator_id', $studentId)->first();
                $theProject = [];
                if (!is_null($project)) {
                    $theProject['title'] = $project->title;
                    $theProject['description'] = $project->description;
                    $theProject['instructor'] = Teacher::select('id', 'name', 'professional_title_id')->find($project->instructor_id);
                    $theProject['members'] = Student::select('id', 'student_number', 'name')->where('selected_project_id', $project->id)->get();

                    $result->data = $theProject;

                } else {
                    $result->code = 100;
                    $result->message = '没有创建项目';
                }

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

    public function studentProjectList(Request $request) {
        $projects = Project::select('id', 'title')->where('type', 0)->get();
        foreach ($projects as $project) {
            $project['selected_count'] = StudentProject::where('project_id', $project->id)->count();
        }
        return response()->json($projects);
    }

    public function studentSelectProject(Request $request) {
        $result = new MyResult();

        $studentId = $request->get('studentId');
        $projectId = $request->get('projectId');

        $project = Project::find($projectId);
        $student = Student::find($studentId);

        if ($project == null) {
            $result->code = 100;
            $result->message = '该项目不存在';
            return response()->json($result);
        } else if ($project->type == 1) {
            $result->code = 100;
            $result->message = '自拟题目无法自选';
            return response()->json($result);
        }

        if ($student == null) {
            $result->code = 100;
            $result->message = '该学生不存在';
            return response()->json($result);
        }

        $order = $request->get('order');

        if ($studentId != null && $projectId != null && $order != null) {
            $temp1 = StudentProject::where('student_id', $studentId)->where('project_id', $projectId)->first();
            $temp2 = StudentProject::where('student_id', $studentId)->where('order', $order)->first();

            if ($temp1 == null && $temp2 == null) {
                $studentProject = new StudentProject();
                $studentProject['student_id'] = $studentId;
                $studentProject['project_id'] = $projectId;
                $studentProject['order'] = $order;
                $studentProject->save();
                $result->message = '选题成功';
            }

            if ($temp1 == null && $temp2 != null) {
                $temp2['project_id'] = $projectId;
                $result->message = '志愿变更成功';
                $temp2->save();
            }

            if ($temp1 != null && $temp2 == null) {
                $temp1['order'] = $order;
                $temp1->save();
                $result->message = '志愿变更成功';
            }

            if ($temp1 != null && $temp2 != null) {
                $temp2['order'] = $temp1['order'];
                $temp1['order'] = $order;
                $temp1->save();
                $temp2->save();
                $result->message = '志愿顺序调整成功';
            }

        } else {
            $result->code = 100;
            $result->message = '缺少参数';
        }

        return response()->json($result);
    }

    public function studentSelectedProjects(Request $request) {
        $studentId = $request->get('studentId');
        $studentProjects = StudentProject::where('student_id', $studentId)->orderBy('order', 'asc')->get();
        $projects = [];
        foreach ($studentProjects as $studentProject) {
            $project = Project::select('id', 'title')->find($studentProject->project_id);
            $project['order'] = $studentProject->order;
            $projects[] = $project;
        }
        return response()->json($projects);
    }

    public function studentDeselectProject(Request $request) {
        $studentId = $request->get('studentId');
        $projectId = $request->get('projectId');

        if ($studentId != null && $projectId != null) {
            StudentProject::where('student_id', $studentId)->where('project_id', $projectId)->delete();
        }

        return response()->json(new MyResult());
    }

    public function getProjectDetail(Request $request) {
        $projectId = $request->get('projectId');
        $result = new MyResult();

        if ($projectId == null) {
            $result->code = 100;
            $result->message = '缺少参数';
            return response()->json($result);
        }

        $project = Project::find($projectId);
        $theProject = [];

        $theProject['title'] = $project->title;
        $theProject['description'] = $project->description;
        if ($project->type == 0) {
            $theProject['type'] = '指导老师创建';
            $theProject['creator'] = Teacher::find($project->creator_id)->name;
        } else {
            $theProject['type'] = '学生自拟';
            $theProject['creator'] = Student::find($project->creator_id)->name;
        }

        $theProject['instructor'] = Teacher::find($project->instructor_id)->name;

        $result->data = $theProject;
        return response()->json($result);
    }

    public function projectAutomaticDistribution() {
        $projects = Project::where('type', 0)->get();
        for ($i = 1; $i < 4; $i++) {
            foreach ($projects as $project) {
                $instructor = Teacher::find($project->instructor);
                $studentProjects = StudentProject::where('project_id', $project->id)->where('order', $i)->get();
                $students = [];
                foreach ($studentProjects as $studentProject) {
                    $student = Student::find($studentProject->student_id);
                    if (is_null($students->selected_project_id)) {
                        $student['order'] = TeacherStudentOrder::where('student_id', $student->id)->where('teacher_id', $instructor->id)->first()->order;
                        $students[] = $student;
                    }
                }
                $students->sortBy('order');
                $currentCount = Student::where('selected_project_id', $project->id)->count();
                for ($j = 0; $j < 5-$currentCount && $j < count($students); $j++) {
                    $student = $students[$i];
                    $student['selected_project_id'] = $project->id;
                    $student->save();
                }
            }
        }
    }

    public function getAdminProjectList(Request $request) {
        $result = new MyResult();
        $specialtyId = $request['user']['specialty_id'];

        $projects = Project::select('id', 'title')->where('specialty_id', $specialtyId)->get();

        foreach ($projects as $project) {
            $project['count'] = Student::where('selected_project_id', $project['id'])->count();
        }

        $result->data = $projects;
        return response()->json($result);
    }

    public function adminDistributeProject(Request $request) {
        $result = new MyResult();

        $projectId = $request->get('projectId');
        $studentId = $request->get('studentId');

        $student = Student::find($studentId);
        $project = Project::find($projectId);

        if (is_null($student) || is_null($project)) {
            $result->code = 100;
            $result->message = '学生或项目不存在';
            return response()->json($result);
        }

        if (Student::where('selected_project_id', $projectId)->count() >= 5) {
            $result->code = 100;
            $result->message = '该项目已超出限制';
            return response()->json($result);
        }

        if (!is_null($student['selected_project_id'])) {
            $result->code = 100;
            $result->message = '该学生已经选题';
            return response()->json($result);
        }

        $student['selected_project_id'] = $projectId;
        $student->save();
        $result->message = '选题成功';

        return response()->json($result);
    }

    public function adminRemoveStudentProject(Request $request) {
        $result = new MyResult();

        $studentId = $request->get('studentId');

        $student = Student::find($studentId);

        if (is_null($student)) {
            $result->code = 100;
            $result->message = '没有该学生';
            return response()->json($result);
        }

        $student['selected_project_id'] = null;
        $student->save();

        return response()->json($result);
    }

    public function projectSelectedStudentList(Request $request) {
        $result = new MyResult();

        $projectId = $request->get('projectId');

        $project = Project::find($projectId);

        if (is_null($project)) {
            $result->code = 100;
            $result->message = '该项目不存在';
            return response()->json($result);
        }

        $result->data = Student::select('id', 'name')->where('selected_project_id', $project->id)->get();

        return response()->json($result);
    }
}