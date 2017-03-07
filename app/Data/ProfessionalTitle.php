<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/2/23
 * Time: 16:12
 */

namespace App\Data;


use Illuminate\Database\Eloquent\Model;

class ProfessionalTitle extends Model
{

    protected $table = 'professional_title';
    protected $primaryKey = 'id';
    public $timestamps = false;
}