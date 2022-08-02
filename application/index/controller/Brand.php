<?php
namespace app \index \controller ;
use think\Hook;
use app\index\controller\Acl;
use app\index\model\Brand as Brands;
use app\index\controller\Formfield;
class Brand extends Acl {
    //品牌模块
    //---------------(^_^)---------------//
    //品牌视图
    public function main(){
        return $this->fetch();
    }
    //品牌列表
    public function brand_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit')){
            $sql=get_sql($input,[
                'name'=>'full_name_py_link',
                'number'=>'full_like',
                'data'=>'full_like'
            ],'brand');//构造SQL
            $count = Brands::where ($sql)->count();//获取总条数
            $arr = Brands::where($sql)->page($input['page'],$input['limit'])->order('id desc')->select();//查询分页数据
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
    //新增|更新品牌信息
    public function set_brand(){
        $input=input('post.');
        if(isset($input['id'])){
            if(empty($input['id'])){
                //新增
                $vali = $this->validate($input,'brand');
                if($vali===true){
                    $input['py']=zh2py($input['name']);//首拼信息
                    $create_info=Brands::create(syn_sql($input,'brand'));
                    Hook::listen('create_brand',$create_info);//品牌新增行为
                    push_log('新增品牌信息[ '.$create_info['name'].' ]');//日志
                    $resule=['state'=>'success'];
                }else{
                    $resule=['state'=>'error','info'=>$vali];
                }
            }else{
                //更新
                $vali = $this->validate($input,'brand.update');
                if($vali===true){
                    $input['py']=zh2py($input['name']);//首拼信息
                    $update_info=Brands::update(syn_sql($input,'brand'));
                    Hook::listen('update_brand',$update_info);//品牌更新行为
                    push_log('更新品牌信息[ '.$update_info['name'].' ]');//日志
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
    //获取品牌信息
    public function get_brand(){
        $input=input('post.');
        if(isset_full($input,'id')){
            $resule=Brands::where(['id'=>$input['id']])->find();
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //删除品牌信息
    public function del_brand(){
        $input=input('post.');
        if(isset_full($input,'arr') && is_array($input['arr'])){
            //查询数据是否存在
            $exist=more_table_find([
            	['table'=>'goods','where'=>['brand'=>['in',$input['arr']]]]
            ]);
            //判断数据是否存在
            if(!$exist){
            	$info=db('brand')->where(['id'=>['in',$input['arr']]])->select();//获取删除信息
                foreach ($info as $info_vo) {
                    push_log('删除品牌信息[ '.$info_vo['name'].' ]');//日志
                    Hook::listen('del_brand',$info_vo['id']);//品牌删除行为
                }
                Brands::where(['id'=>['in',$input['arr']]])->delete();
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