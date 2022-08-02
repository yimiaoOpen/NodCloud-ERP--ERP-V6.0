<?php
namespace addons\pagemore\controller;
use think\Controller;
use addons\pagemore\model\Pagemore;
class Main extends Controller{
    //入口函数
    public function run($params){
        if(is_array($params)){
            $request=request();
            $sql['name']=count($params)>1?$params[1]:implode('|',$request->dispatch()['module']);
            $sql['type']=$params[0];
            $info=Pagemore::where($sql)->find();
            if(!empty($info)){
                echo $info['info'];
            }
        }else{
            return json(['state'=>'error','info'=>'传入参数不完整!']);
        }
    }
    //报表数据
    public function form_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit')){
            $sql=get_sql($input,[
                'name'=>'full_like',
                'type'=>'full_eq',
                'data'=>'full_like'
            ],'pagemore');//构造SQL
            $count = Pagemore::where ($sql)->count();//获取总条数
            $arr = Pagemore::where($sql)->page($input['page'],$input['limit'])->select();//查询分页数据
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
    //新增|更新数据
    public function set(){
        $input=input('post.',null,'html_entity_decode');//兼容XSS防护
        if(isset($input['id'])){
            $validate = new \addons\pagemore\validate\Pagemore();//实例化验证器
            if(empty($input['id'])){
                //新增
                $vali = $validate->check($input);
                if($vali===true){
                    Pagemore::create(syn_sql($input,'pagemore'));
                    $resule=['state'=>'success'];
                }else{
                    $resule=['state'=>'error','info'=>$validate->getError()];
                }
            }else{
                //更新
                $vali = $validate->scene('update')->check($input);
                if($vali===true){
                    Pagemore::update(syn_sql($input,'pagemore'));
                    $resule=['state'=>'success'];
                }else{
                    $resule=['state'=>'error','info'=>$validate->getError()];
                }
            }
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //获取数据
    public function get_info(){
        $input=input('post.');
        if(isset_full($input,'id')){
            $resule=Pagemore::where(['id'=>$input['id']])->find();
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //删除数据
    public function del_info(){
        $input=input('post.');
        if(isset_full($input,'arr') && is_array($input['arr'])){
            Pagemore::where(['id'=>['in',$input['arr']]])->delete();
            $resule=['state'=>'success'];
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
}