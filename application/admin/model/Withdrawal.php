<?php


namespace app\admin\model;


use think\Model;

class Withdrawal extends  Model
{
    protected $autoWriteTimestamp = true;


    public function user()
    {
        return $this->hasOne('User','id','withdrawer');
    }
}