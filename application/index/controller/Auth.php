<?php
namespace app \index \controller ;
use think\Hook;
use app\index\controller\Acl;
use app\index\model\User;
use app\index\model\Auth as Auths;
class Auth extends Acl {
    //数据授权
    //---------------(^_^)---------------//
    //数据授权视图
    public function main(){
        return $this->fetch();
    }
    //数据授权列表
    public function auth_list(){
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
    //获取数据授权信息
    public function get_auth(){
        $input=input('post.');
        if(isset_full($input,'id')){
            $info=Auths::where(['pid'=>$input['id']])->select();
            //重构数据
            foreach ($info as $key=>$vo) {
                $info[$key]['info']=gets_selectpage($vo['name'],$vo['info']);
            }
            $resule=$info;
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //设置数据授权信息
    public function set_auth(){
        $input=input('post.',null,'html_entity_decode');
        if(isset_full($input,'id') && isset($input['arr'])){
            Auths::where(['pid'=>$input['id']])->delete();//初始化数据授权
            $user_info=user_info($input['id']);//获取用户信息
            $arr=json_decode($input['arr'],true);//解码兼容空数组
            foreach ($arr as $vo) {
                $sql=[];
                $sql['pid']=$input['id'];
                $sql['name']=$vo['name'];
                $sql['info']=$vo['info'];
                //处理权限数据-开始
                //排除管理员
                if($user_info['type']!=1){
                    if($vo['name']=='user' && !empty($vo['info']) && !in_array($user_info['id'],$vo['info'])){
                        array_push($sql['info'],$user_info['id']);//制单人加入自身
                    }elseif ($vo['name']=='merchant' && !empty($vo['info']) && !in_array($user_info['merchant'],$vo['info'])) {
                        array_push($sql['info'],$user_info['merchant']);//商户加入自身
                    }
                }
                //处理权限数据-结束
                Auths::create($sql);
            }
            push_log('设置数据授权[ '.$user_info['name'].' ]');//日志
            $resule=['state'=>'success'];
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
}