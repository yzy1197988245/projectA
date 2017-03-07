<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/2/1
 * Time: 15:40
 */

namespace App\Data;


use Illuminate\Database\Eloquent\Model;

class RoleRoute extends Model
{

    protected $table = 'role_route';
    protected $primaryKey = 'id';
    public $timestamps = false;

}