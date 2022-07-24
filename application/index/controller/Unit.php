<?php
namespace app \index \controller ;
use think\Hook;
use app\index\controller\Acl;
use app\index\model\Unit as Units;
use app\index\controller\Formfield;
class Unit extends Acl {
    //计量单位模块
    //---------------(^_^)---------------//
    //计量单位视图
    public function main(){
        return $this->fetch();
    }
    //计量单位列表
    public function unit_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit')){
            $sql=get_sql($input,[
                'name'=>'full_name_py_link',
                'number'=>'full_like',
                'data'=>'full_like'
            ],'unit');//构造SQL
            $count = Units::where ($sql)->count();//获取总条数
            $arr = Units::where($sql)->page($input['page'],$input['limit'])->order('id desc')->select();//查询分页数据
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
    //新增|更新计量单位信息
    public function set_unit(){
        $input=input('post.');
        if(isset($input['id'])){
            if(empty($input['id'])){
                //新增
                $vali = $this->validate($input,'unit');
                if($vali===true){
                    $input['py']=zh2py($input['name']);//首拼信息
                    $create_info=Units::create(syn_sql($input,'unit'));
                    Hook::listen('create_unit',$create_info);//计量单位新增行为
                    push_log('新增计量单位信息[ '.$create_info['name'].' ]');//日志
                    $resule=['state'=>'success'];
                }else{
                    $resule=['state'=>'error','info'=>$vali];
                }
            }else{
                //更新
                $vali = $this->validate($input,'unit.update');
                if($vali===true){
                    $input['py']=zh2py($input['name']);//首拼信息
                    $update_info=Units::update(syn_sql($input,'unit'));
                    Hook::listen('update_unit',$update_info);//计量单位更新行为
                    push_log('更新计量单位信息[ '.$update_info['name'].' ]');//日志
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
    //获取计量单位信息
    public function get_unit(){
        $input=input('post.');
        if(isset_full($input,'id')){
            $resule=Units::where(['id'=>$input['id']])->find();
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //删除计量单位信息
    public function del_unit(){
        $input=input('post.');
        if(isset_full($input,'arr') && is_array($input['arr'])){
            //查询数据是否存在
            $exist=more_table_find([
            	['table'=>'goods','where'=>['unit'=>['in',$input['arr']]]],
            ]);
            //判断数据是否存在
            if(!$exist){
            	$info=db('unit')->where(['id'=>['in',$input['arr']]])->select();//获取删除信息
                foreach ($info as $info_vo) {
                    push_log('删除计量单位信息[ '.$info_vo['name'].' ]');//日志
                    Hook::listen('del_unit',$info_vo['id']);//计量单位删除行为
                }
                Units::where(['id'=>['in',$input['arr']]])->delete();
                $resule=['state'=>'success'];
            }else{
            	$resule=['state'=>'error','info'=>'存在数据关联,删除失败!'];
            }
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
}