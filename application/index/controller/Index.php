<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use think\Db;

class Index extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    public function index()
    {
        $newList = Db::name('news')->limit(20)->select();
        $this->assign('newlist', $newList);

        return $this->view->fetch();
    }

}
