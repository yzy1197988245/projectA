<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/2/7
 * Time: 13:39
 */

namespace App\Data;


use Illuminate\Database\Eloquent\Model;

class Student extends Model
{

    protected $table = 'student';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public $hidden = ['password'];
}