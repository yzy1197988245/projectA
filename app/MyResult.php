<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2016/12/28
 * Time: 21:19
 */

namespace App;


class MyResult
{
    public $code = '200';
    public $data = [];
    public $message = '';

    public function error($message) {
        $this->code = 100;
        $this->message = $message;
        return response()->json($this);
    }

    public function success($message) {
        $this->code = 100;
        $this->message = $message;
        return response()->json($this);
    }
}