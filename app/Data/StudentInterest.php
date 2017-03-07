<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/2/26
 * Time: 12:49
 */

namespace App\Data;


use Illuminate\Database\Eloquent\Model;

class StudentInterest extends Model
{

    protected $table = 'student_interest';
    protected $primaryKey = 'id';
    public $timestamps = false;

}