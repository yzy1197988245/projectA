<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/2/26
 * Time: 16:27
 */

namespace App\Data;


use Illuminate\Database\Eloquent\Model;

class ClassInfo extends Model
{

    protected $table = 'class';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function specialty() {
        return $this->belongsTo('App\Data\Specialty', 'specialty_id');
    }
}