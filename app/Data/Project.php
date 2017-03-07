<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/1/28
 * Time: 17:25
 */

namespace App\Data;


use Illuminate\Database\Eloquent\Model;

class Project extends Model
{

    protected $table = 'project';
    protected $primaryKey = 'id';

    public $timestamps = false;

}