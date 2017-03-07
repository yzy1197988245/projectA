<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/2/10
 * Time: 19:46
 */

namespace App\Data;


use Illuminate\Database\Eloquent\Model;

class TeacherStudentOrder extends Model
{
    protected $table = 'teacher_student_order';
    public $timestamps = false;
}