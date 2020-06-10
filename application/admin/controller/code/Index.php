<?php

namespace app\admin\controller\code;

use app\admin\model\AuthRule;
use app\common\controller\Backend;
use fast\Tree;
use think\Cache;

class Index extends Backend
{

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Code');
    }

    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {

            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model

                ->count();
            $list = $this->model

                ->limit($offset, $limit)
                ->select();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    public function add(){
        if ($this->request->isPost()) {
            $this->token();
            $params = $this->request->post("row/a", [], 'strip_tags');
            if ($params){
                for($i=0;$i<$params['num'];$i++){
                    $arr[]=[
                        'code'=>$this->randCode($params['index']),
                        'state'=>0
                    ];
                }
                $result = $this->model->saveAll($arr);
                $this->success();
            }
            $this->error();
        }
        return $this->view->fetch();
    }
     public function randCode($length = 5, $type = 0) {
        $arr = array(1 => "0123456789", 2 => "abcdefghijklmnopqrstuvwxyz", 3 => "ABCDEFGHIJKLMNOPQRSTUVWXYZ", 4 => "~@#$%^&*(){}[]|");
        if ($type == 0) {
            array_pop($arr);
            $string = implode("", $arr);
        } elseif ($type == "-1") {
            $string = implode("", $arr);
        } else {
            $string = $arr[$type];
        }
        $count = strlen($string) - 1;
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $string[rand(0, $count)];
        }
        return $code;
    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        $row = $this->model->get(['id' => $ids]);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        if ($this->request->isPost()) {

            $params = $this->request->post("row/a", [], 'strip_tags');
            if ($params) {
                $result = $row->save($params);
                if ($result === false) {
                    $this->error($row->getError());
                }
                Cache::rm('__menu__');
                $this->success();
            }
            $this->error();
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }
}