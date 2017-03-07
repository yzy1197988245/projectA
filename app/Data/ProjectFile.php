<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/2/28
 * Time: 10:28
 */

namespace App\Data;


use Illuminate\Database\Eloquent\Model;

class ProjectFile extends Model
{

    protected $table = 'project_file';
    protected $primaryKey = 'id';
    public $timestamps = false;

}