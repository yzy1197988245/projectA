<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/2/26
 * Time: 14:44
 */

namespace App\Data;


use Illuminate\Database\Eloquent\Model;

class ProjectInterest extends Model
{
    protected $table = 'project_interest';
    protected $primaryKey = 'id';
    public $timestamps = false;
}