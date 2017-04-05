<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/3/21
 * Time: 20:30
 */

namespace App\Data;


use Illuminate\Database\Eloquent\Model;

class ProjectTeacher extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'project_teacher';
    public $timestamps = false;

}