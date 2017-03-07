<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/2/26
 * Time: 10:58
 */

namespace App\Data;


use Illuminate\Database\Eloquent\Model;

class Interest extends Model
{

    protected $table = 'interest';
    protected $primaryKey = 'id';
    public $timestamps = false;
}