<?php
namespace app \index \controller ;
use think\Hook;
use think\Request;
use app\index\controller\Acl;
use app\index\model\User as Users;
use app\index\controller\Formfield;
use app\index\model\Log;
class User extends Acl {
    //职员模块
    //---------------(^_^)---------------//
    //职员视图
    public function main(){
        return $this->fetch();
    }
    //职员列表
    public function user_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit')){
            $sql=get_sql($input,[
                'user'=>'full_like',
                'name'=>'full_name_py_link',
                'merchant'=>'full_division_in'
            ],'user');//构造SQL
			config(['app_debug'])||($sql['type']=0);//普通用户
            $sql=auth('user',$sql);//数据鉴权
            $count = Users::where ($sql)->count();//获取总条数
            $arr = Users::with('merchantinfo')->where($sql)->page($input['page'],$input['limit'])->order('id desc')->select();//查询分页数据
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
    //新增|更新职员信息
    public function set_user(){
        $input=input('post.');
        if(isset($input['id'])){
            if(empty($input['id'])){
                //新增
                $vali = $this->validate($input,'User');
                if($vali===true){
                    $input['py']=zh2py($input['name']);//首拼信息
                    $create_info=Users::create(syn_sql($input,'user'));
                    Hook::listen('create_user',$create_info);//职员新增行为
                    push_log('新增职员信息[ '.$create_info['user'].' ]');//日志
                    $resule=['state'=>'success'];
                }else{
                    $resule=['state'=>'error','info'=>$vali];
                }
            }else{
                //更新
                if(isset($input['pwd']) && empty($input['pwd'])){
                    unset($input['pwd']);//留空不修改密码
                }
                $vali = $this->validate($input,'User.update');
                if($vali===true){
                    $input['py']=zh2py($input['name']);//首拼信息
                    $update_info=Users::update(syn_sql($input,'user'));
                    Hook::listen('update_user',$update_info);//职员更新行为
                    push_log('更新职员信息[ '.$update_info['user'].' ]');//日志
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
    //获取职员信息
    public function get_user(){
        $input=input('post.');
        if(isset_full($input,'id')){
            $resule=Users::with('merchantinfo')->where(['id'=>$input['id']])->find();
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //删除职员信息
    public function del_user(){
        $input=input('post.');
        if(isset_full($input,'arr') && is_array($input['arr'])){
            //查询数据是否存在
            $exist=more_table_find([
            	['table'=>'accountinfo','where'=>['user'=>['in',$input['arr']]]],
            	['table'=>'allocationclass','where'=>['user'=>['in',$input['arr']]]],
            	['table'=>'cashierclass','where'=>['user'=>['in',$input['arr']]]],
            	['table'=>'eftclass','where'=>['user'=>['in',$input['arr']]]],
            	['table'=>'exchangeclass','where'=>['user'=>['in',$input['arr']]]],
            	['table'=>'gatherclass','where'=>['user'=>['in',$input['arr']]]],
            	['table'=>'integral','where'=>['user'=>['in',$input['arr']]]],
            	['table'=>'itemorderbill','where'=>['user'=>['in',$input['arr']]]],
            	['table'=>'itemorderclass','where'=>['user'=>['in',$input['arr']]]],
            	['table'=>'opurchaseclass','where'=>['user'=>['in',$input['arr']]]],
            	['table'=>'otgatherclass','where'=>['user'=>['in',$input['arr']]]],
            	['table'=>'otpaymentclass','where'=>['user'=>['in',$input['arr']]]],
            	['table'=>'otpurchaseclass','where'=>['user'=>['in',$input['arr']]]],
            	['table'=>'otsaleclass','where'=>['user'=>['in',$input['arr']]]],
            	['table'=>'paymentclass','where'=>['user'=>['in',$input['arr']]]],
            	['table'=>'purchasebill','where'=>['user'=>['in',$input['arr']]]],
            	['table'=>'purchaseclass','where'=>['user'=>['in',$input['arr']]]],
            	['table'=>'recashierclass','where'=>['user'=>['in',$input['arr']]]],
            	['table'=>'repurchasebill','where'=>['user'=>['in',$input['arr']]]],
            	['table'=>'repurchaseclass','where'=>['user'=>['in',$input['arr']]]],
            	['table'=>'resalebill','where'=>['user'=>['in',$input['arr']]]],
            	['table'=>'resaleclass','where'=>['user'=>['in',$input['arr']]]],
            	['table'=>'rpurchasebill','where'=>['user'=>['in',$input['arr']]]],
            	['table'=>'rpurchaseclass','where'=>['user'=>['in',$input['arr']]]],
            	['table'=>'salebill','where'=>['user'=>['in',$input['arr']]]],
            	['table'=>'saleclass','where'=>['user'=>['in',$input['arr']]]],
            ]);
            //判断数据是否存在
            if(!$exist){
            	$info=db('user')->where(['id'=>['in',$input['arr']]])->select();//获取删除信息
                foreach ($info as $info_vo) {
                    push_log('删除职员信息[ '.$info_vo['name'].' ]');//日志
                    Hook::listen('del_user',$info_vo['id']);//职员删除行为
                }
                Users::where(['id'=>['in',$input['arr']]])->delete();
                Log::where(['user'=>['in',$input['arr']]])->delete();//删除日志
                $resule=['state'=>'success'];
            }else{
                $resule=['state'=>'error','info'=>'存在数据关联,删除失败!'];
            }
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //上传职员头像
    public function upload_img(Request $request){
		$file=$request->file('file');//获取表单上传文件
		if (empty($file)){
		    $resule=['state'=>'error','info'=>'传入数据不完整!'];
		}else{
            //单文件限制2MB
            $nod=$file->validate (['size'=>2097152,'ext'=>'png,gif,jpg,jpeg,bmp'])->rule ('uniqid')->move (ROOT_PATH .'skin'.DS .'upload'.DS .'user');
            if ($nod){
                $file_name=$nod->getSaveName ();
                $file_path='/skin/upload/user/'.$file_name;
                $resule=['state'=>'success','info'=>$file_path];
            }else {
                $resule=['state'=>'error','info'=>$file->getError()];//返回错误信息
            }
		}
		return json ($resule);
    }
}