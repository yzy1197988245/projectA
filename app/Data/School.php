<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/2/26
 * Time: 16:31
 */

namespace App\Data;


use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $table = 'school';
    protected $primaryKey = 'id';
    public $timestamps = false;
}