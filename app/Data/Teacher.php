<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/2/21
 * Time: 23:01
 */

namespace App\Data;


use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{

    protected $table = 'teacher';
    protected $id = 'id';
    public $timestamps = false;

    protected $hidden = ['password'];
}