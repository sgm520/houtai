<?php

namespace app\index\controller;

use addons\wechat\model\WechatCaptcha;
use app\common\controller\Frontend;
use app\common\library\Ems;
use app\common\library\Sms;
use think\Config;
use think\Cookie;
use think\Db;
use think\Hook;
use think\Session;
use think\Validate;

/**
 * 会员中心
 */
class User extends Frontend
{
    protected $layout = 'default';
    protected $noNeedLogin = ['login', 'register', 'third'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $auth = $this->auth;

        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'));
        }

        //监听注册登录注销的事件
        Hook::add('user_login_successed', function ($user) use ($auth) {
            $expire = input('post.keeplogin') ? 30 * 86400 : 0;
            Cookie::set('uid', $user->id, $expire);
            Cookie::set('token', $auth->getToken(), $expire);
        });
        Hook::add('user_register_successed', function ($user) use ($auth) {
            Cookie::set('uid', $user->id);
            Cookie::set('token', $auth->getToken());
        });
        Hook::add('user_delete_successed', function ($user) use ($auth) {
            Cookie::delete('uid');
            Cookie::delete('token');
        });
        Hook::add('user_logout_successed', function ($user) use ($auth) {
            Cookie::delete('uid');
            Cookie::delete('token');
        });
    }

    /**
     * 空的请求
     * @param $name
     * @return mixed
     */
    public function _empty($name)
    {
        $data = Hook::listen("user_request_empty", $name);
        foreach ($data as $index => $datum) {
            $this->view->assign($datum);
        }
        return $this->view->fetch('user/' . $name);
    }

    /**
     * 会员中心
     */
    public function index()
    {
        $this->view->assign('title', __('User center'));
        return $this->view->fetch();
    }

    /**
     * 注册会员
     */
    public function register()
    {
        $account = $this->request->post('phone');
        $password = $this->request->post('password');
        $rule = [
            'mobile'   => 'require|length:3,50',
            'password'  => 'require|length:6,30',
        ];

        $msg = [
            'mobile.require'  => 'Account can not be empty',
            'mobile.length'   => 'Account must be 3 to 50 characters',
            'password.require' => 'Password can not be empty',
            'password.length'  => 'Password must be 6 to 30 characters',
        ];
        $data = [
            'mobile'   => $account,
            'password'  => $password,
        ];
        $validate = new Validate($rule, $msg);
        $result = $validate->check($data);
        if (!$result) {
            return json(['code'=>0,'msg'=>$validate->getError()]);
        }
        $checkuser=Db::name('user')->where('mobile',$account)->find();
        if(!empty($checkuser)){
            return json(['code'=>0,'msg'=>'手机号码已被注册']);
        }
        $data['status']='normal';
        $data['level']=1;
        $id=Db::name('user')->insertGetId($data);
        if(!empty($id)){
            return json(['code'=>1,'msg'=>'注册成功']);
        }

    }

    /**
     * 会员登录
     */
    public function login()
    {

            $account = $this->request->post('phone');
            $password = $this->request->post('password');
            $rule = [
                'account'   => 'require|length:3,50',
                'password'  => 'require|length:6,30',
            ];

            $msg = [
                'account.require'  => 'Account can not be empty',
                'account.length'   => 'Account must be 3 to 50 characters',
                'password.require' => 'Password can not be empty',
                'password.length'  => 'Password must be 6 to 30 characters',
            ];
            $data = [
                'account'   => $account,
                'password'  => $password,
            ];
            $validate = new Validate($rule, $msg);
            $result = $validate->check($data);
            if (!$result) {
                return json(['code'=>0,'msg'=>$validate->getError()]);
            }
            $result=$this->auth->login($account, $password);
            if($result['code'] !=0){
                session('userInfo',$result['data']);
            }
            return  json($result);
    }

    /**
     * 注销登录
     */
    public function logout()
    {
        session('userInfo', null);
        return  json(['code'=>1,'msg'=>'退出成功']);
    }

    /**
     * 个人信息
     */
    public function profile()
    {
        $this->view->assign('title', __('Profile'));
        return $this->view->fetch();
    }

    /**
     * 修改密码
     */
    public function changepwd()
    {
        if ($this->request->isPost()) {
            $oldpassword = $this->request->post("oldpassword");
            $newpassword = $this->request->post("newpassword");
            $renewpassword = $this->request->post("renewpassword");
            $token = $this->request->post('__token__');
            $rule = [
                'oldpassword'   => 'require|length:6,30',
                'newpassword'   => 'require|length:6,30',
                'renewpassword' => 'require|length:6,30|confirm:newpassword',
                '__token__'     => 'token',
            ];

            $msg = [
            ];
            $data = [
                'oldpassword'   => $oldpassword,
                'newpassword'   => $newpassword,
                'renewpassword' => $renewpassword,
                '__token__'     => $token,
            ];
            $field = [
                'oldpassword'   => __('Old password'),
                'newpassword'   => __('New password'),
                'renewpassword' => __('Renew password')
            ];
            $validate = new Validate($rule, $msg, $field);
            $result = $validate->check($data);
            if (!$result) {
                $this->error(__($validate->getError()), null, ['token' => $this->request->token()]);
                return false;
            }

            $ret = $this->auth->changepwd($newpassword, $oldpassword);
            if ($ret) {
                $this->success(__('Reset password successful'), url('user/login'));
            } else {
                $this->error($this->auth->getError(), null, ['token' => $this->request->token()]);
            }
        }
        $this->view->assign('title', __('Change password'));
        return $this->view->fetch();
    }

    public function watchnew(){
        $id = $this->request->post('id');
        if(empty($id)){
            return  json(['code'=>1,'msg'=>'参数不能为空']);
        }
        $data = Db::name('news')
            ->where('id', $id)
            ->find();
        return  json(['code'=>1,'msg'=>'成功','data'=>$data]);
    }
    public function getprofitlist(){
        if(session('userInfo')){
            $data = Db::name('profitdetail')
                ->alias('a')
                ->join('news w','a.new_id = w.id')
                ->field('w.type,a.money,a.time')
                ->where('user_id', session('userInfo.id'))
                ->whereTime('time', 'today')->select();
            return  json(['code'=>1,'msg'=>'成功','data'=>$data]);
        }
        else{
            return  json(['code'=>0,'msg'=>'用户没有登录']);
        }
    }
    public function getUserInfo(){
        if(session('userInfo')){
            $data = Db::name('user')
                ->where('id', session('userInfo.id'))
                ->find();
            return  json(['code'=>1,'msg'=>'成功','data'=>$data]);
        }
        else{
            return  json(['code'=>0,'msg'=>'用户没有登录']);
        }
    }

    public function getWithdrawerCode(){
        if(session('userInfo')){
            $data = Db::name('withdrawal')
                ->where('withdrawer', session('userInfo.id'))
                ->select();
            return  json(['code'=>1,'msg'=>'成功','data'=>$data]);
        }
        else{
            return  json(['code'=>0,'msg'=>'用户没有登录']);
        }
    }

    public function transferMoney(){
        if(session('userInfo')){
            $data = $this->request->post();

            if(!is_numeric($data['charge'])){
                return  json(['code'=>0,'msg'=>'提现金额必须为数字']);
            }
            if(empty($data['name'])){
                return  json(['code'=>0,'msg'=>'对方账户不能为空']);
            }
            if(($data['charge'])<1){
                return  json(['code'=>0,'msg'=>'提现金额不能小于1元']);
            }
            if(session('userInfo.mobile') ==$data['name']){
                return  json(['code'=>0,'msg'=>'自己不能给自己转账']);
            }
            $receiveer=Db::name('user')->where('mobile',$data['name'])->find();
            if(empty($receiveer)){
                return  json(['code'=>0,'msg'=>'该用户不存在']);
            }
            $balance=Db::name('user')->where('id',session('userInfo.id'))->value('balance');

            $receiveerbalance=Db::name('user')->where('mobile',$data['name'])->value('balance');
            if($data['charge']>$balance){
                return  json(['code'=>0,'msg'=>'提现金额大于了余额']);
            }
            $data['withdrawer']=session('userInfo.id');
            $data['withdrawal_time']=date("Y-m-d H:i:s",time());
            $data['state']=1;
            $result = Db::name('withdrawal')
                ->insertGetId($data);
            if(!empty($result)){
                Db::name('user')->where('id',session('userInfo.id'))->update(['balance'=>$balance-$data['charge']]);
                return  json(['code'=>1,'msg'=>'申请提现成功']);
            }
            else{
                return  json(['code'=>0,'msg'=>'申请提现失败']);
            }

        }
        else{
            return  json(['code'=>0,'msg'=>'用户没有登录']);
        }
    }

    public function actionMoney(){
        if(session('userInfo')){
            $data = $this->request->post();
            $profitsetconfig=Db::name('profitsetconfig')->find();
            if(!is_numeric($data['charge'])){
                return  json(['code'=>0,'msg'=>'提现金额必须为数字']);
            }
            if($data['charge']<=$profitsetconfig['min_amount']){
                return  json(['code'=>0,'msg'=>'提现金额不能小于'.$profitsetconfig['min_amount']]);
            }
            if($profitsetconfig['switch_integer'] ==1){
                if(is_float($data['charge'])){
                    return  json(['code'=>0,'msg'=>'提现金额必须为整数']);
                }
            }
            $balance=Db::name('user')->where('id',session('userInfo.id'))->value('balance');
            if($data['charge']>$balance){
                return  json(['code'=>0,'msg'=>'提现金额大于了余额']);
            }
            $data['withdrawer']=session('userInfo.id');
            $data['withdrawal_time']=date("Y-m-d H:i:s",time());
            $data['state']=1;
            $result = Db::name('withdrawal')
                ->insertGetId($data);
            if(!empty($result)){
                 Db::name('user')->where('id',session('userInfo.id'))->update(['balance'=>$balance-$data['charge']]);
                return  json(['code'=>1,'msg'=>'申请提现成功']);
            }
            else{
                return  json(['code'=>0,'msg'=>'申请提现失败']);
            }

        }
        else{
            return  json(['code'=>0,'msg'=>'用户没有登录']);
        }
    }
    public function addbank(){
        if(session('userInfo')){
            $data = $this->request->post();
            $data['user_id']=session('userInfo.id');
            $id = Db::name('bank')->insertGetId($data);
            if(!empty($id)){
                return  json(['code'=>1,'msg'=>'成功']);
            }
            else{
                return  json(['code'=>0,'msg'=>'失败']);
            }
        }
        else{
            return  json(['code'=>0,'msg'=>'用户没有登录']);
        }
    }
    public function getProfit(){
        $new_id = $this->request->post("new_id");
        if(empty($new_id)){
            return  json(['code'=>0,'msg'=>'文章id不能为空']);
        }
        $profit=Db::name('profit')->find();
        if(session('userInfo')){
            $data= Db::name('profitdetail')
                ->where('new_id','eq',$new_id)
                ->where('user_id','eq',session('userInfo.id'))
                ->whereTime('time', 'today')->find();
            if(!empty($data)){
                return  json(['code'=>0,'msg'=>'该新闻今日已收益']);
            }
            $count=Db::name('profitdetail')->where('user_id',session('userInfo.id'))->whereTime('time', 'today')->count();
            if(session('userInfo.level')==2){ //会员
                if($count>=$profit['vip_reading_num']){
                    return  json(['code'=>0,'msg'=>'今日已达到上限阅读']);
                }
                else if($profit['vip_reading_charge'] ==0){
                    return  json(['code'=>0,'msg'=>'会员不会产生收益']);
                }
                else{
                    $data['time']=time();
                    $data['user_id']=session('userInfo.id');
                    $data['money']=$profit['vip_reading_charge'];
                    $data['new_id']=$new_id;
                   $id= Db::where('profitdetail')->insertGetId($data);
                   if(!empty($id)){
                       return  json(['code'=>1,'msg'=>'收益成功']);
                   }
                   else{
                       return  json(['code'=>0,'msg'=>'收益失败']);
                   }
                }
            }
            else{
                if($count>=$profit['member_reading_num']){
                    return  json(['code'=>0,'msg'=>'今日已达到上限阅读']);
                }
                else if($profit['member_reading_charge'] ==0){
                    return  json(['code'=>0,'msg'=>'会员不会产生收益']);
                }
                else{
                    $data['time']=date("Y-m-d H:i:s",time());
                    $data['user_id']=session('userInfo.id');
                    $data['money']=$profit['member_reading_charge'];
                    $data['new_id']=$new_id;
                    $id= Db::name('profitdetail')->insertGetId($data);
                    if(!empty($id)){
                       $balance=Db::name('user')->where('id',session('userInfo.id'))->value('balance');
                       $result= Db::name('user')->where('id',session('userInfo.id'))->update(['balance'=>$profit['member_reading_charge']+$balance]);
                       return  json(['code'=>1,'msg'=>'收益成功']);
                    }
                    else{
                        return  json(['code'=>0,'msg'=>'收益失败']);
                    }
                }
            }
        }
        else{
            return  json(['code'=>0,'msg'=>'用户没有登录']);
        }
    }
}
