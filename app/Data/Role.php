<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/1/23
 * Time: 09:48
 */

namespace App\Data;


use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'role';
    public $timestamps = false;

    public function routes() {
        return $this->belongsToMany('\App\Data\Route', 'role_route', 'role_id', 'route_id');
    }
}