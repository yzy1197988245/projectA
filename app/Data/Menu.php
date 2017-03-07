<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/1/9
 * Time: 21:02
 */

namespace App\Data;


use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{

    protected $table = 'menu';
    protected $primaryKey = 'id';

    public $timestamps = false;

}