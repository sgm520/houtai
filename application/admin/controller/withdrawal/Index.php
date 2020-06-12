<?php

namespace app\admin\controller\withdrawal;

use app\common\controller\Backend;
use think\Cache;
use think\Db;

class Index extends Backend
{

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Withdrawal');
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
                ->with('user')
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
            if($row->type ==1){
                    if($params['state'] ==3){
                        $blance=Db::name('user')->where('id',$row['withdrawer'])->value('balance');
                        Db::name('user')->where('id',$row['withdrawer'])->update(['balance'=>$blance+$row['charge']]);
                    }
            };
            if($row->type ==2){
                if($params['state'] ==3){
                    $blance=Db::name('user')->where('id',$row['withdrawer'])->value('balance');
                    Db::name('user')->where('id',$row['withdrawer'])->update(['balance'=>$blance+$row['charge']]);
                }
                if($params['state'] ==2){
                    $blance=Db::name('user')->where('mobile',$row['name'])->value('balance');
                    Db::name('user')->where('mobile',$row['name'])->update(['balance'=>$blance+$row['charge']]);
                }
            };
            if ($params) {
                $result = $row->save($params);
                if ($result === false) {
                    $this->error($row->getError());
                }
                if($params)
                $this->success();
            }
            $this->error();
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }
}