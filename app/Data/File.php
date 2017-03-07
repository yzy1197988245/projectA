<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/2/7
 * Time: 22:41
 */

namespace App\Data;


use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $table = 'file';
    protected $primaryKey = 'id';
    public $timestamps = false;
}