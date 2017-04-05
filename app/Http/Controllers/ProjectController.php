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
use App\Data\ProjectTeacher;
use App\Data\Student;
use App\Data\StudentProject;
use App\Data\Teacher;
use App\Data\TeacherStudentOrder;
use App\MyResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        $teachers = $request->get('teachers');
        $memberCount = $request->get('memberCount');
        $type = 0;

        if ($title != null && $description != null && $creator != null && $memberCount != null) {
            $project['title'] = $title;
            $project['description'] = $description;
            $project['creator_id'] = $creator;
            $project['specialty_id'] = $request['user']['specialty_id'];
            $project['school_id'] = $request['user']['school_id'];
            $project['instructor_id'] = $creator;
            $project['type'] = $type;
            $project['member_count'] = $memberCount;
            $project->save();

            ProjectInterest::where('project_id', $project->id)->delete();
            foreach ($interests as $interest) {
                $projectInterest = new ProjectInterest();
                $projectInterest['project_id'] = $project->id;
                $projectInterest['interest_id'] = $interest;
                $projectInterest->save();
            }

            ProjectTeacher::where('project_id', $project->id)->delete();
            foreach ($teachers as $teacher) {
                $projectTeacher = new ProjectTeacher();
                $projectTeacher['project_id'] = $project->id;
                $projectTeacher['teacher_id'] = $teacher;
                $projectTeacher->save();
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
        StudentProject::where('project_id', $projectId)->delete();
        ProjectTeacher::where('project_id', $projectId)->delete();

        $students = Student::where('selected_project_id', $projectId)->get();
        foreach ($students as $student) {
            $student['selected_project'] = null;
            $student->save();
        }

        $result->message = '删除成功';

        return response()->json($result);
    }

    public function getTeacherProjectList(Request $request) {
        $teacherId = $request['user']->id;
        $result = new MyResult();

        if (is_null($teacherId)) {
            $result->code = 100;
            $result->message = '缺少参数';
            return response()->json($result);
        }

        $result->data = Project::select('id', 'title', 'member_count')->where('type', 0)->where('creator_id', $teacherId)->get();

        foreach ($result->data as $project) {
            $project['student_count'] = StudentProject::where('project_id', $project->id)->count();
        }

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
        $theProject['memberCount'] = $project->member_count;

        $interests = [];
        $projectInterests = ProjectInterest::where('project_id', $project->id)->get();
        foreach ($projectInterests as $projectInterest) {
            $interest = Interest::find($projectInterest->interest_id);
            $interests[] = $interest;
        }
        $theProject['interests'] = $interests;

        $teachers = [];
        $projectTeachers = ProjectTeacher::where('project_id', $project->id)->get();
        foreach ($projectTeachers as $projectTeacher) {
            $teacher = Teacher::select('id', 'name')->where('id', $projectTeacher['teacher_id'])->first();
            $teachers[] = $teacher;
        }
        $theProject['teachers'] = $teachers;

        $result->data = $theProject;

        return response()->json($result);
    }

    public function studentCreate(Request $request) {
        $result = new MyResult();

        $title = $request->get('title');
        $instructor = $request->get('instructor');
        $description = $request->get('description');
        $members = $request->get('members');
        $teachers = $request->get('teachers');
        $creatorId = $members[0];
        $type = 1;
        $projectId = $request->get('projectId');

        if (is_null($projectId)) {
            foreach ($members as $member) {
                $student = Student::find($member);
                if ($student['selected_project_id'] != null)
                    return $result->error($student['name'].'已选题');
            }
        }

        if ($title != null && $instructor != null && $members != null) {

            $project = Project::find($projectId);
            if ($project == null) {
                $project = new Project();
            }

            $project['title'] = $title;
            $project['instructor_id'] = $instructor;
            $project['description'] = $description;
            $project['creator_id'] = $creatorId;
            $project['captain_id'] = $creatorId;
            $project['school_id'] = $request['user']['school_id'];
            $project['specialty_id'] = $request['user']['specialty_id'];
            $project['type'] = $type;
            $project->save();

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

            ProjectTeacher::where('project_id', $project->id)->delete();
            foreach ($teachers as $teacher) {
                $projectTeacher = new ProjectTeacher();
                $projectTeacher['project_id'] = $project->id;
                $projectTeacher['teacher_id'] = $teacher;
                $projectTeacher->save();
            }

        } else {
            $result->code = '100';
            $result->message = '缺少参数';
        }

        return response()->json($result);
    }

    public function getStudentCreateProjectBaseInfo(Request $request) {
        $result = new MyResult();
        $projectId = $request->get('projectId');

        if (!is_null($projectId)) {
            $project = Project::find($projectId);
            $theProject = [];
            if (!is_null($project)) {
                $theProject['title'] = $project->title;
                $theProject['description'] = $project->description;

                $teachers = [];
                $teachers[] = Teacher::select('id', 'name')->where('id', $project['instructor_id'])->first();

                $projectTeachers = ProjectTeacher::where('project_id', $project->id)->get();
                foreach ($projectTeachers as $projectTeacher) {
                    $teachers[] = Teacher::select('id', 'name')->where('id', $projectTeacher['teacher_id'])->first();
                }

                $students = Student::select('id', 'name')->where('selected_project_id', $project->id)->get();

                $theProject['teachers'] = $teachers;
                $theProject['students'] = $students;

                $result->data = $theProject;
            } else {
                $result->code = 100;
                $result->message = '没有创建项目';
            }
        } else {
            $result->code = 100;
            $result->message = '缺少参数';
        }

        return response()->json($result);
    }

    public function studentProjectList(Request $request) {
        $projects = Project::select('id', 'title', 'school_id', 'specialty_id', 'instructor_id')->where('type', 0)->get();
        foreach ($projects as $project) {
            $project['selected_count'] = StudentProject::where('project_id', $project->id)->count();
            $project['instructor'] = Teacher::select('name')->where('id', $project['instructor_id'])->first()->name;
        }
        return response()->json($projects);
    }

    public function studentSelectProject(Request $request) {
        $result = new MyResult();

        $studentId = $request->get('studentId');
        $projectId = $request->get('projectId');
        $order = $request->get('order');

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

                $instructor = Teacher::find($project['instructor_id']);
                $teacherStudentOrder = TeacherStudentOrder::where('student_id', $studentId)->where('teacher_id', $instructor->id)->first();
                if (is_null($teacherStudentOrder)) {
                    $order = TeacherStudentOrder::where('teacher_id', $instructor->id)->max('order');
                    $order++;
                    $teacherStudentOrder = new TeacherStudentOrder();
                    $teacherStudentOrder['teacher_id'] = $instructor->id;
                    $teacherStudentOrder['student_id'] = $student->id;
                    $teacherStudentOrder['order'] = $order;
                    $teacherStudentOrder->save();
                }
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

    public function getProjectListForJM(Request $request) {
        $user = $request['user'];
        $schoolId = $user['school_id'];
        $projects = Project::select('id', 'specialty_id', 'title', 'instructor_id', 'member_count')->where('school_id', $schoolId)->get();
        foreach ($projects as $project) {
            $project['instructor'] = Teacher::find($project['instructor_id'])->name;
            $project['current_count'] = Student::where('selected_project_id', $project->id)->count();
        }
        $result = new MyResult();
        $result->data = $projects;
        return response()->json($result);
    }

    public function getZNProjectList(Request $request) {
        $user = $request['user'];
        $schoolId = $user['school_id'];
        $specialtyId = $user['specialty_id'];
        $projects = Project::select('id', 'title', 'instructor_id')
            ->where('type', 1)
            ->where('school_id', $schoolId)
            ->where('specialty_id', $specialtyId)
            ->get();
        foreach ($projects as $project) {
            $project['instructor'] = Teacher::find($project['instructor_id'])->name;
        }
        $result = new MyResult();
        $result->data = $projects;
        return response()->json($result);
    }

    public function getProjectListWithStudents(Request $request) {
        $user = $request['user'];
        $projects = Project::select('id', 'title')->where('instructor_id', $user->id)->get();
        foreach ($projects as $project) {
            $students = [];
            for ($i=1; $i<4; $i++) {
                $studentIds = StudentProject::where('project_id', $project->id)->where('order', $i)->get();
                foreach ($studentIds as $studentId) {
                    $student = Student::select('id', 'name')->where('id', $studentId['student_id'])->first();
                    $student['refused'] = $studentId['refused'];
                    $students[$i][] = $student;
                }
            }
            $project['students'] = $students;
        }
        $result = new MyResult();
        $result->data = $projects;
        return response()->json($result);
    }

    public function teacherRefuseAcceptStudent(Request $request) {
        $projectId = $request->get('projectId');
        $studentId = $request->get('studentId');
        $refused = $request->get('refused');

        $studentProject = StudentProject::where('student_id', $studentId)->where('project_id', $projectId)->first();
        $studentProject['refused'] = $refused;
        $studentProject->save();

        return response()->json(new MyResult());
    }
}