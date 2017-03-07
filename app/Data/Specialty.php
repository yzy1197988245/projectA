<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/2/26
 * Time: 16:30
 */

namespace App\Data;


use Illuminate\Database\Eloquent\Model;

class Specialty extends Model
{
    protected $table = 'specialty';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function school() {
        return $this->belongsTo('App\Data\School', 'school_id');
    }
}