<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/2/9
 * Time: 12:12
 */

namespace App\Data;


use Illuminate\Database\Eloquent\Model;

class StudentProject extends Model
{

    protected $table = 'student_project';
    protected $primaryKey = 'id';
    public $timestamps = false;

}