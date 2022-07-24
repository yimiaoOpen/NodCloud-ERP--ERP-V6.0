<?php
namespace app\index\controller;
use app\index\controller\Acl;
use app\index\model\Goodsclass;
use app\index\model\Often;
use app\index\model\Room;
class Main extends Acl{
    //系统主页
    public function index(){
        $menu=db('menu')->order('sort asc')->select()->ToArray();
        $menu=get_root_menu($menu);
        $this->assign('menu',$menu);
        $this->assign('sys_name',get_sys(['sys_name']));
        $this->assign('user_info',user_info());
        return $this->fetch();
    }
    //首页
    public function home(){
        //1.获取常用功能
        $often=Often::select()->ToArray();
        //常用操作权限判断
        foreach ($often as $often_key=>$often_vo) {
            if(isset($often_vo['root'])){
                if(!get_root($often_vo['root'])){
                    unset($often[$often_key]);
                }
            }
        }
        //2.获取库存预警
        $roomwarning_nums=0;//初始化库存预警数量
        $room=Room::with('goodsinfo')->where(auth('room',[]))->select();
        foreach ($room as $room_vo) {
            if(bccomp($room_vo['nums'],$room_vo['goodsinfo']['stocktip'],config('decimal'))==-1){
                $roomwarning_nums++;
            }
        }
        //3.获取系统配置
        $sys=get_sys(['form_day','notice']);
        //4.获取报表基础数据
        $day_arr=sum_old_day($sys['form_day']);
        //5.获取报表数据
        $echarts_data=[
            'purchase'=>get_echarts_info('purchaseclass','total',$day_arr),
            'rpurchase'=>get_echarts_info('rpurchaseclass','total',$day_arr),
            'sale'=>get_echarts_info('saleclass','total',$day_arr),
            'cashier'=>get_echarts_info('cashierclass','total',$day_arr),
            'itemorder'=>get_echarts_info('itemorderclass','total',$day_arr),
        ];
        $this->assign('often',array_chunk($often,8));
        $this->assign('sys',$sys);
        $this->assign('account',db('account')->where(auth('account',[]))->sum('balance'));
        $this->assign('customer',db('customer')->where(auth('customer',[]))->count());
        $this->assign('supplier',db('supplier')->where(auth('supplier',[]))->count());
        $this->assign('room',db('room')->where(auth('room',[]))->sum('nums'));
        $this->assign('user',db('user')->where(auth('user',[]))->count());
        $this->assign('roomwarning_nums',$roomwarning_nums);
        $this->assign('day_arr',$day_arr);
        $this->assign('echarts_data',$echarts_data);
        return $this->fetch();
    }
    //基础商品信息页面
    public function base_goods(){
        $goodsclass=Goodsclass::select();
        if(!empty($goodsclass)){
            $tree=new \org\tree();
            $goodsclass=$tree::vTree($goodsclass);//按照关联排序
        }
        $this->assign('goodsclass',$goodsclass);
        return $this->fetch();
    }
    //仓储商品信息页面
    public function room_goods(){
        $goodsclass=Goodsclass::select();
        if(!empty($goodsclass)){
            $tree=new \org\tree();
            $goodsclass=$tree::vTree($goodsclass);//按照关联排序
        }
        $this->assign('goodsclass',$goodsclass);
        return $this->fetch();
    }
    //服务信息页面
    public function serve(){
        return $this->fetch();
    }
    //库存查询
    public function room(){
        $goodsclass=Goodsclass::select();
        if(!empty($goodsclass)){
            $tree=new \org\tree();
            $goodsclass=$tree::vTree($goodsclass);//按照关联排序
        }
        $this->assign('goodsclass',$goodsclass);
        return $this->fetch();
    }
    //库存详情
    public function room_info(){
        return $this->fetch();
    }
    //库存预警
    public function room_warning(){
        $goodsclass=Goodsclass::select();
        if(!empty($goodsclass)){
            $tree=new \org\tree();
            $goodsclass=$tree::vTree($goodsclass);//按照关联排序
        }
        $this->assign('goodsclass',$goodsclass);
        return $this->fetch();
    }
    //库存盘点
    public function room_check(){
        $goodsclass=Goodsclass::select();
        if(!empty($goodsclass)){
            $tree=new \org\tree();
            $goodsclass=$tree::vTree($goodsclass);//按照关联排序
        }
        $this->assign('goodsclass',$goodsclass);
        return $this->fetch();
    }
    //统计初始化
    public function summary(){
        return $this->fetch();
    }
    //商品利润表
    public function goods_profit(){
        $goodsclass=Goodsclass::select();
        if(!empty($goodsclass)){
            $tree=new \org\tree();
            $goodsclass=$tree::vTree($goodsclass);//按照关联排序
        }
        $this->assign('goodsclass',$goodsclass);
        return $this->fetch();
    }
    //销售利润表
    public function bill_profit(){
        return $this->fetch();
    }
    //串码跟踪表
    public function serial(){
        return $this->fetch();
    }
    //串码跟踪详情表
    public function serial_info(){
        return $this->fetch();
    }
    //往来单位欠款表
    public function arrears(){
        return $this->fetch();
    }
    //系统升级
    public function about(){
        $this->assign('now_ver',get_ver());
        $this->assign('new_ver',new_ver());
        echo $this->fetch();
    }
}