<?php
namespace addons\loginerror\controller;
use addons\loginerror\model\loginerror;
class Main{
    // 登录失败
    public function loginError($info){
        loginerror::create([
            'time'=>time(),
            'ip'=>request()->ip(),
            'user'=>$info['user'],
            'pwd'=>$info['pwd'],
        ]);
    }
    //数据列表
    public function loginerror_list(){
		$input=input ('post.');
		if(isset_full($input,'page')&&isset_full($input,'limit')){
		    $sql=get_sql($input,['ip'=>'full_like','user'=>'full_like','pwd'=>'full_like','start_time'=>'stime','end_time'=>'etime'],'loginerror');
    		$count=loginerror::where ($sql)->count();//获取总条数
    		$arr=loginerror::where ($sql)->page($input['page'],$input['limit'])->order('id desc')->select();//查询分页数据
    		$resule['code']=0;
    		$resule['msg']='获取成功';
    		$resule['count']=$count;
    		$resule['data']=$arr;
    		return json ($resule);
		}else{
		    return json(['state'=>'error','info'=>'传入参数不完整!']);
		}
    }
    //清空数据
    public function empty_loginerror(){
        loginerror::where(['id'=>['neq',0]])->delete();
        return json (['state'=>'success']);
    }
}