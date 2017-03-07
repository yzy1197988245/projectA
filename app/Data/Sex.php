<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/3/2
 * Time: 11:13
 */

namespace App\Data;


use Illuminate\Database\Eloquent\Model;

class Sex extends Model
{

    protected $table = 'sex';
    protected $primaryKey = 'id';
    public $timestamps = false;
}