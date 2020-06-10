<?php

namespace app\admin\controller\withdrawal;

use app\common\controller\Backend;
use think\Cache;

class Setconfig extends Backend
{

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Profitsetconfig');
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
                $this->success();
            }
            $this->error();
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }
}