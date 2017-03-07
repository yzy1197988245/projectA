<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/1/9
 * Time: 21:03
 */

namespace App\Data;


use Illuminate\Database\Eloquent\Model;

class RouteData extends Model
{
    protected $table = 'route_data';
    protected $primaryKey = 'id';

    public $timestamps = false;

    public function menu() {
        return $this->hasOne('\App\Data\Menu', 'id', 'menu_id');
    }
}