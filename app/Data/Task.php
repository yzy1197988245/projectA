<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/3/5
 * Time: 17:12
 */

namespace App\Data;


use Illuminate\Database\Eloquent\Model;

class Task extends Model
{

    protected $table = 'task';
    protected $primaryKey = 'id';
    public $timestamps = false;

}