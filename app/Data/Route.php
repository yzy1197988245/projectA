<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/1/9
 * Time: 21:02
 */

namespace App\Data;


use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    protected $table = 'route';
    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $hidden = ['route_data_id'];

    public function data() {
        return $this->hasOne('\App\Data\RouteData', 'id', 'route_data_id');
    }
}