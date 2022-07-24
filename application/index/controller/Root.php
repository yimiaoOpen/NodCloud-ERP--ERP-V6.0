<?php
namespace app \index \controller ;
use think\Hook;
use app\index\controller\Acl;
use app\index\model\User;
use app\index\model\Root as Roots;
class Root extends Acl {
    //权限设置
    //---------------(^_^)---------------//
    //权限视图
    public function main(){
        return $this->fetch();
    }
    //权限列表
    public function root_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit')){
            $sql=get_sql($input,[
                'user'=>'full_like',
                'name'=>'full_name_py_link',
                'merchant'=>'full_division_in'
            ],'user');//构造SQL
            $sql['type']=0;//普通用户
            $sql=auth('user',$sql);//数据鉴权
            $count = User::where ($sql)->count();//获取总条数
            $arr = User::with('merchantinfo')->where($sql)->page($input['page'],$input['limit'])->order('id desc')->select();//查询分页数据
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
    //获取权限信息
    public function get_root(){
        $input=input('post.');
        if(isset_full($input,'id')){
            $resule=Roots::where(['pid'=>$input['id']])->select();
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //设置权限信息
    public function set_root(){
        $input=input('post.');
        if(isset_full($input,'id') && isset($input['arr'])){
            Roots::where(['pid'=>$input['id']])->delete();//初始化权限
            $insert_sql=[];
            foreach ($input['arr'] as $vo) {
                $arr=[];
                $arr['pid']=$input['id'];
                $arr['name']=$vo['name'];
                $arr['info']=$vo['info'];
                array_push($insert_sql,$arr);
            }
            Roots::insertAll($insert_sql);
            $user_info=user_info($input['id']);
            push_log('设置功能权限[ '.$user_info['name'].' ]');//日志
            $resule=['state'=>'success'];
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
}