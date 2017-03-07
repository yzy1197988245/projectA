<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/2/21
 * Time: 21:03
 */

namespace App\Data;


use Illuminate\Database\Eloquent\Model;

class StudentGeneralScore extends Model
{

    protected $table = "student_general_score";
    protected $primaryKey = "id";
    public $timestamps = false;

}