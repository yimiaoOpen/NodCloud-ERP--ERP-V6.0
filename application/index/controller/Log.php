<?php
namespace app \index \controller ;
use app\index\controller\Acl;
use app\index\model\Log as Logs;
class Log extends Acl {
    //操作日志模块
    //---------------(^_^)---------------//
    //操作日志视图
    public function main(){
        return $this->fetch();
    }
    //操作日志列表
    public function log_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit')){
            $sql=get_sql($input,[
                'text'=>'full_like',
                'user'=>'full_division_in',
                'start_time'=>'stime',
                'end_time'=>'etime',
            ],'log');//构造SQL
            $sql['merchant']=['in',get_auth_merchant()];//补全授权商户数据
            $sql=auth('log',$sql);//数据鉴权
            $count = Logs::where ($sql)->count();//获取总条数
            $arr = Logs::with('merchantinfo,userinfo')->where($sql)->page($input['page'],$input['limit'])->order('id desc')->select();//查询分页数据
            $resule=[
                'code'=>0,
                'msg'=>'获取成功',
                'count'=>$count,
                'data'=>$arr
            ];//返回数据
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //清空操作日志信息
    public function empty_log(){
        Logs::where(['id'=>['gt',0]])->delete();
        push_log('清空操作日志');//日志
        $resule=['state'=>'success'];
        return json($resule);
    }
}