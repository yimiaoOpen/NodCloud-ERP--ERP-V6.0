<?php
namespace app \index \controller ;
use think\Hook;
use app\index\controller\Acl;
use app\index\model\Attribute as Attributes;
use app\index\controller\Formfield;
class Attribute extends Acl {
    //辅助属性模块
    //---------------(^_^)---------------//
    //辅助属性视图
    public function main(){
        return $this->fetch();
    }
    //辅助属性列表
    public function attribute_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit')){
            $sql=get_sql($input,[
                'name'=>'full_like',
                'data'=>'full_like'
            ],'attribute');//构造SQL
            $sql['pid']=['eq',0];//排除扩展属性
            $count = Attributes::where ($sql)->count();//获取总条数
            $arr = Attributes::where($sql)->page($input['page'],$input['limit'])->order('id desc')->select();//查询分页数据
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
    //新增|更新辅助属性信息
    public function set_attribute(){
        $input=input('post.');
        if(isset($input['id'])){
            if(empty($input['id'])){
                //新增
                $vali = $this->validate($input,'attribute');
                if($vali===true){
                    $create_info=Attributes::create(syn_sql($input,'attribute'));
                    Hook::listen('create_attribute',$create_info);//辅助属性新增行为
                    push_log('新增辅助属性[ '.$create_info['name'].' ]');//日志
                    $resule=['state'=>'success'];
                }else{
                    $resule=['state'=>'error','info'=>$vali];
                }
            }else{
                //更新
                $vali = $this->validate($input,'attribute.update');
                if($vali===true){
                    $update_info=Attributes::update(syn_sql($input,'attribute'));
                    Hook::listen('update_attribute',$update_info);//辅助属性更新行为
                    push_log('更新辅助属性[ '.$update_info['name'].' ]');//日志
                    $resule=['state'=>'success'];
                }else{
                    $resule=['state'=>'error','info'=>$vali];
                }
            }
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //获取辅助属性信息
    public function get_attribute(){
        $input=input('post.');
        if(isset_full($input,'id')){
            $resule=Attributes::with('subinfo')->where(['id'=>$input['id']])->find();
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //删除辅助属性信息
    public function del_attribute(){
        $input=input('post.');
        if(isset_full($input,'arr') && is_array($input['arr'])){
            $info=Attributes::with('subinfo')->where(['id'=>['in',$input['arr']]])->select()->ToArray();//获取删除信息
            foreach ($info as $info_vo) {
                if(!empty($info_vo['subinfo'])){
                    return json(['state'=>'error','info'=>'辅助属性[ '.$info_vo['name'].' ]存在扩展属性,删除失败!']);
                }
            }
            foreach ($info as $info_vo) {
                push_log('删除辅助属性[ '.$info_vo['name'].' ]');//日志
                Hook::listen('del_attribute',$info_vo['id']);//辅助属性删除行为
            }
            Attributes::where(['id'=>['in',$input['arr']]])->delete();
            $resule=['state'=>'success'];
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //添加扩展属性
    public function add_attr(){
        $input=input('post.');
        if(isset_full($input,'pid') && isset_full($input,'name')){
            $check=db('attribute')->where($input)->find();
            if(empty($check)){
                $create_info=Attributes::create(syn_sql($input,'attribute'));
                Hook::listen('create_attr',$create_info);//辅助属性扩展属性新增行为
                push_log('新增辅助属性扩展属性[ '.$create_info['name'].' ]');//日志
                $resule=['state'=>'success','info'=>$create_info['id']];
            }else{
                $resule=['state'=>'error','info'=>'扩展属性['.$input['name'].']已存在!'];
            }
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //删除扩展属性
    public function del_attr(){
        $input=input('post.');
        if(isset_full($input,'id')){
            $check=true;
            $attr=db('attr')->select()->ToArray();//获取所有辅助属性
            foreach ($attr as $vo) {
                $nod=explode('_',$vo['nod']);//拆分数据
                if(in_array($input['id'],$nod)){
                    $resule=['state'=>'error','info'=>'存在数据关联,删除失败!'];
                    $check=false;
                }
            }
            if($check){
                $info=db('attribute')->where(['id'=>$input['id']])->find();//获取删除信息
                Attributes::where(['id'=>$input['id']])->delete();
                Hook::listen('del_attr',$input['id']);//辅助属性删除行为
                push_log('删除辅助属性扩展属性[ '.$info['name'].' ]');//日志
                $resule=['state'=>'success'];
            }
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
}