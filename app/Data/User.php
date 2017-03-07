<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/1/23
 * Time: 09:49
 */

namespace App\Data;


use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'id';
    public $timestamps = false;
}