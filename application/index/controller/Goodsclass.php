<?php
namespace app \index \controller ;
use think\Hook;
use app\index\controller\Acl;
use app\index\model\Goodsclass as Goodsclasss;
use app\index\controller\Formfield;
class Goodsclass extends Acl {
    //商品分类模块
    //---------------(^_^)---------------//
    //商品分类设置
    public function main(){
        $list=Goodsclasss::select();
        if(!empty($list)){
            $tree=new \org\tree();
            $list=$tree::vTree($list);//按照关联排序
        }
        $this->assign('list',$list);
        return $this->fetch();
    }
    //新增|更新商品分类信息  
    public function set_goodsclass(){
        $input=input('post.');
        if(isset($input['id'])){
            if(empty($input['id'])){
                //新增
                $create_info=Goodsclasss::create(syn_sql($input,'goodsclass'));
                Hook::listen('create_goodsclass',$create_info);//商品分类新增行为
                $resule=['state'=>'success'];
            }else{
                //更新
                //所属商品分类不可等于或包含当前所属商品分类
                if(in_array($input['pid'],find_tree_arr('goodsclass',[$input['id']]))){
                    $resule=['state'=>'error','info'=>'所属商品分类选择不正确!'];
                }else{
                    $update_info=Goodsclasss::update(syn_sql($input,'goodsclass'));
                    Hook::listen('update_goodsclass',$update_info);//商品分类更新行为
                    $resule=['state'=>'success'];
                }
            }
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //获取商品分类信息
    public function get_goodsclass(){
        $input=input('post.');
        if(isset_full($input,'id')){
            $resule=Goodsclasss::with('pidinfo')->where(['id'=>$input['id']])->find();
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //删除商品分类信息
    public function del_goodsclass(){
        $input=input('post.');
        if(isset_full($input,'id')){
            //查询数据是否存在
            $exist=more_table_find([
            	['table'=>'goods','where'=>['class'=>$input['id']]]
            ]);
            //判断数据是否存在
            if(!$exist){
            	$find=Goodsclasss::where(['pid'=>$input['id']])->find();
                if(empty($find)){
                    Goodsclasss::where(['id'=>$input['id']])->delete();
                    Hook::listen('del_goodsclass',$input['id']);//商品分类删除行为
                    $resule=['state'=>'success'];
                }else{
                    $resule=['state'=>'exist_data','info'=>'存在子数据,删除失败!'];
                }
            }else{
            	$resule=['state'=>'error','info'=>'存在数据关联,删除失败!'];
            }
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
}