<?php
//通用函数库
//获取系统版本号
function get_ver(){
    return file_get_contents($_SERVER['DOCUMENT_ROOT'].DS.'application'.DS.'index'.DS.'ver');
}
//获取文件夹大小  
function get_dir_size($dir){
    static $sizeResult = 0;
    $handle = opendir($dir);
    while (false!==($FolderOrFile = readdir($handle)))  {   
        if($FolderOrFile != "." && $FolderOrFile != "..")   {   
            if(is_dir("$dir/$FolderOrFile")){   
                $sizeResult += get_dir_size("$dir/$FolderOrFile");   
            }else{   
                $sizeResult += filesize("$dir/$FolderOrFile");   
            }  
        }      
    }  
    closedir($handle);  
    return round(($sizeResult/1048576),2);
}
//二维数组返回指定键名集合
function array_field($arr,$key){
    $data=array();
    foreach ($arr as $arr_vo) {
        $arr=array();
        foreach ($key as $key_vo) {
            if(isset($arr_vo[$key_vo])){
                $arr[$key_vo]=$arr_vo[$key_vo];
            }
        }
        $data[]=$arr;
    }
    return $data;
}
//递归获取指定ID树状数组结构
function find_tree_arr($mode,$arr){
    static $tree=array();
    foreach ($arr as $vo) {
        $sub=db($mode)->where(['pid'=>$vo])->select()->toarray();
        array_push($tree,$vo);
        if(!empty($sub)){
            $more=find_tree_arr($mode,array_column($sub,'id'));
        }
    }
    return $tree;
}
//获取文件目录列表,该方法返回数组
function getDir($dir){
    $dirArray[]=NULL;
    if (false!=($handle=opendir($dir))){
        $i=0;
        while(false!==($file = readdir($handle))){
            //去掉.|..|以及带.xxx后缀的文件
            if($file!="."&&$file!=".."&&!strpos($file,".")){
                $dirArray[$i]=$file;
                $i++;
            }
        }
        closedir ($handle);//关闭句柄
    }
    return $dirArray;
}
//数组指定条件搜索
//$data数据内容,$arr搜索条件['key|key1'=>['in|eq','val']]
//in是包含,eq是等于
function searchdata($data,$arr){
    $info=[];
    if(is_array($data) && is_array($arr)){
        foreach ($data as $data_vo){
            $nod=true;//初始化状态
            foreach ($arr as $key=>$arr_vo){
                //判断多键值
                $val='nod_initial';
                foreach (explode('|',$key) as $key_vo) {
                    $val=$val==='nod_initial'?$data_vo[$key_vo]:$val[$key_vo];
                }
                //val取值成功
                if($val!=='nod_initial'){
                    if($arr_vo[0]=='in'){
                        //包含判断
                        strstr($val, $arr_vo[1])||($nod=false);
                    }else if($arr_vo[0]=='eq'){
                        //相等判断
                        $val==$arr_vo[1]||($nod=false);
                    }
                }
            }
            $nod&&(array_push($info,$data_vo));//加入数据
        }
    }
    return $info;
}
//多表多条件find查询
//$arr [['table'=>'plug','where'=>['only'=>1]]]]
function more_table_find($arr){
    $resule=false;//默认未找到
    foreach ($arr as $vo) {
        $find=db($vo['table'])->where($vo['where'])->find();
        if(!empty($find)){
            $resule=true;//找到数据
            break;
        }
    }
    return $resule;
}
//删除目录  
function removedir($dirName){
    if(!is_dir($dirName)){
        return false;
    }
    $handle=@opendir($dirName);
    while(($file=@readdir($handle))!==false){
        if($file!='.'&&$file != '..'){
            $dir=$dirName.DS.$file;
            is_dir($dir)?removedir($dir):@unlink($dir);
        }
    }
    closedir($handle);
    return rmdir($dirName);
}
//生成插件秘钥
function get_plug_key($time){
    $key=config('api_key');//私有秘钥
    return md5($time.'|'.$key);
}
//HTML代码压缩
function compress_html($string) {
    $string = str_replace("\r\n", '', $string);
    $string = str_replace("\n", '', $string);
    $string = str_replace("\t", '', $string);
    $pattern = ["/> *([^ ]*) *</","/[\s]+/","/<!--[^!]*-->/","/\" /","/ \"/","'/\*[^*]*\*/'"];
    $replace = [">\\1<"," ","","\"","\"",""];
    return preg_replace($pattern, $replace, $string);
}
//CSS代码压缩
function compress_css($string) {
    $string = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $string);
    $string = str_replace(["", "\r", "\n", "\t", '  ', '    ', '    '], '', $string);
    return $string;
}
//构造SQL-返回设置项的指定处理方式
//$info原始数据内容,$set设置项,$model数据表,$full是否允许为空(默认不允许)
//md5:MD5加密|like:包含查询|full_like:不为空包含查询|in:包含查询(传入数组)
//full_dec_1:不为空内容减1|stime和etime:时间区间查询|full_eq:不为空等于数据
//full_name_py_link:不为空扩展包含查询|full_division_in:不为空分割集合查询
//full_goodsclass_tree_sub:不为空商品分类集合查询//continue:不处理跳过
function get_sql($info,$set=[],$model,$full=true){
    $sql=[];//预设返回数据
    $field=db($model)->getTableFields();//读取数据表字段信息
    array_push($field,'start_time','end_time');//加入时间字段
    //循环数据
    foreach ($info as $info_key=>$info_vo) {
        //判断数据字段是否存在
        if(!in_array($info_key,$field)){
            continue;
        }
        //判断字段是否需要单独处理
        if(isset($set[$info_key])){
            //需要处理,判断类型
            if($set[$info_key]=='md5'){
                //md5加密
                $sql[$info_key]=md5($info_vo);
            }elseif($set[$info_key]=='like'){
                //包含查询
                $sql[$info_key]=['like','%'.$info_vo.'%'];
            }elseif($set[$info_key]=='full_like'){
                //不为空包含查询
                empty($info_vo)||($sql[$info_key]=['like','%'.$info_vo.'%']);
            }elseif($set[$info_key]=='in'){
                //包含查询
                $sql[$info_key]=['in',$info_vo];
            }elseif($set[$info_key]=='full_dec_1'){
                //不为空内容减1
                empty($info_vo)||($sql[$info_key]=$info_vo-1);
            }elseif($set[$info_key]=='stime' || $set[$info_key]=='etime'){
                //时间查询
                if(!isset($sql['time'])){
                    $time_key='time';//时间字段KEY
                    $start_time=$info[array_search("stime",$set)];//取出开始时间KEY并读取数值
                    $end_time=$info[array_search("etime",$set)];//取出结束时间KEY并读取数值
                    $egt=['egt',strtotime($start_time)];//大于等于
                    $elt=['elt',strtotime($end_time)+86399];//小于等于(加当天)
                    if(!empty($start_time) && empty($end_time)){
                        //开始时间不为空,结束时间为空
                        $sql[$time_key]=$egt;
                    }elseif(!empty($end_time) && empty($start_time)){
                        //结束时间不为空,开始时间为空.
                        $sql[$time_key]=$elt;
                    }elseif(!empty($end_time) && !empty($start_time)){
                        //开始时间不为空,结束时间不为空
                        $sql[$time_key]=[$egt,$elt];
                    }
                }
            }elseif($set[$info_key]=='full_eq'){
                //不为空等于数据
                empty($info_vo)||($sql[$info_key]=$info_vo);
            }elseif($set[$info_key]=='full_name_py_link'){
                //不为空扩展包含查询
                empty($info_vo)||($sql['name|py']=['like','%'.$info_vo.'%']);
            }elseif($set[$info_key]=='full_division_in'){
                //不为空分割集合查询
                empty($info_vo)||($sql[$info_key]=['in',explode(",",$info_vo)]);
            }elseif($set[$info_key]=='full_goodsclass_tree_sub'){
                //不为空分割集合查询
                empty($info_vo)||($sql[$info_key]=['in',tree_sub('goodsclass',$info_vo)]);
            }elseif($set[$info_key]=='continue'){
                //不处理跳过
                continue;
            }
        }else{
            //无需单独处理
            //判断是否允许空值
            if($full){
                if(is_array($info_vo)||!preg_match('/^\s*$/',$info_vo)){
                    $sql[$info_key]=$info_vo;
                }
            }else{
                $sql[$info_key]=$info_vo;
            }
        }
    }
    return $sql;
}
//同步SQL字段
//$exclude:需要排除的字段
function syn_sql($info,$model,$exclude=[]){
    $sql=[];
    //读取数据表字段信息
    if(empty(Session('syn_sql_'.$model))){
        $field=db($model)->getTableFields();
        Session('syn_sql_'.$model,$field);
    }else{
        $field=Session('syn_sql_'.$model);
    }
    foreach ($info as $key=>$vo) {
        //判断数据字段是否存在
        if(in_array($key,$field) && !in_array($key,$exclude)){
            //排除ID等于0的
            if($key=='id' && empty($vo)){
                continue;
            }else{
                $sql[$key]=$vo;
            }
        }else{
            continue;
        }
    }
    return $sql;
}
//判断字段存在并不为空
function isset_full($arr,$key){
    if(isset($arr[$key])&&!empty($arr[$key])){
        return true;
    }else{
        return false;
    }
}
//计算多维数组最多数组数量
function CalArrMaxCount($arr){
    static $nums = 0;
    //对多维数组进行循环
    foreach ($arr as $vo) {
        if(is_array($vo)){
            $count=count($vo);
            //判断是否多维数组
            if ($count==count($vo,1)) {
                $count > $nums&&($nums=$count);
            }else{
                CalArrMaxCount($vo);
            }
        }
    }
    return $nums;
}
//删除超时文件|危险功能-慎用
//$path='skin/upload/xlsx/'del_time_file
function  del_time_file($path,$time=30){
	$filesnames=scandir($path);//获取文件目录
	$now=time();//当前时间
	foreach ($filesnames as $key=>$name){
		//排除掉..
		if ($key>1){
			$nod=$path.$name;//文件路径
			if ($now-filectime($nod)>$time){
				unlink($nod);
			}
		}
	}
}
//优化小数位
function opt_decimal($val){
    $val=bcsub($val,0,config('decimal'));//统一小数位
    $arr=explode('.',$val);
    if(count($arr)>1){
        if($arr[1]=='00'){
            $val=$arr[0];
        }else{
            $nod=str_split($arr[1]);
            foreach (array_reverse($nod,true) as $key=>$vo) {
                if($vo=='0'){
                    unset($nod[$key]);
                }else{
                    break;
                }
            }
            $val=$arr[0].'.'.implode('',$nod);
        }
    }
    return $val;
}
//计算二维数组字段总和
//$tab_data,['total','actual','money']
function get_sums($arr,$keys){
    $resule=[];
    foreach ($keys as $key) {
        $list = array_column($arr, $key);
        $resule[$key]=array_sum($list);
    }
   return $resule;
}
//导出EXCEL
function export_excel($file_name,$data,$down=true){
    vendor("Execl.PHPExcel");
    $PHPExcel=new PHPExcel();//实例化
    $cellname=['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ'];//列标识
    $shell=$PHPExcel->getActiveSheet (0);//当前工作簿
	$shell->setTitle ('NODCLOUD.COM');//工作簿名称
    $shell->getDefaultColumnDimension()->setWidth(13);//设置默认行宽
    $shell->getDefaultRowDimension()->setRowHeight(16);//设置默认行高
    $shell->getDefaultStyle()->getFont()->setName('宋体');//设置默认字体
    $shell->getPageMargins ()->setTop (0.2);//设置上边距
	$shell->getPageMargins ()->setBottom (0.2);//设置下边距
	$shell->getPageMargins ()->setLeft (0.2);//设置左边距
	$shell->getPageMargins ()->setRight (0.2);//设置右边距
    //循环加入数据
    $rownums=1;//初始化行数
    $max_cell=CalArrMaxCount($data);//获取多维数组最多数组数量
    //循环增加数据
    foreach ($data as $data_vo) {
        //判断数据类型
        if($data_vo['type']=='title'){
            //标题行
            $cellnums=0;//初始化列数
            $shell->mergeCells ($cellname[$cellnums].$rownums.':'.$cellname[$max_cell-1].$rownums);//合并单元格
            $shell->setCellValue ($cellname[$cellnums].$rownums,$data_vo['info'])->getStyle ($cellname[$cellnums].$rownums)->applyFromArray ([
                'font'=>['bold'=>true,'size'=>12],
                'alignment'=>['horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER]
            ]);//设置内容|居中|粗体|12号
            $shell->getRowDimension($rownums)->setRowHeight(28);//设置行高
            $rownums++;//自增行数
        }elseif($data_vo['type']=='node'){
            //节点行
            $cellnums=0;//初始化列数
            //设置背景色
            $shell->getStyle($cellname[$cellnums].$rownums.':'.$cellname[$max_cell-1].$rownums)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('e7e6e6');;//设置背景颜色;
            foreach ($data_vo['info'] as $data_info) {
                $shell->setCellValue ($cellname[$cellnums].$rownums,$data_info);
                $cellnums++;//自增列数
            }
            $shell->getRowDimension($rownums)->setRowHeight(16);//设置行高
            $rownums++;//自增行数
        }elseif($data_vo['type']=='table'){
            //表格数据
            $key_arr=[];
            //循环增加表头
            $cellnums=0;//初始化列数
            foreach ($data_vo['info']['cell'] as $cell_key=>$cell_vo) {
                $shell->setCellValue ($cellname[$cellnums].$rownums,$cell_vo)->getStyle ($cellname[$cellnums].$rownums)->applyFromArray ([
                    'font'=>['bold'=>true],
                    'alignment'=>['horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER]
                ]);//设置内容|居中|粗体;
                array_push($key_arr,$cell_key);//加入键值
                $cellnums++;//自增列数
            }
            $shell->getRowDimension($rownums)->setRowHeight(16);//设置标题行高
            $rownums++;//自增行数
            //循环增加表格数据头
            foreach ($data_vo['info']['data'] as $data_vo) {
                $cellnums=0;//初始化列数
                $RowHeight=16;//是否存在扩展信息
                foreach ($key_arr as $key_vo) {
                    if(is_array($data_vo[$key_vo])){
                        //扩展信息
                        if($data_vo[$key_vo]['type']=='img'){
                            //图像
                            $drawing=new PHPExcel_Worksheet_Drawing();
                            $drawing->setPath ($data_vo[$key_vo]['info']);//设置图像路径
                            $drawing->setOffsetX (3);//设置X偏移距离
							$drawing->setOffsetY (3);//设置Y偏移距离
							$drawing->setWidth (98);//设置图像宽度
							$drawing->setCoordinates ($cellname[$cellnums].$rownums)->setWorksheet ($shell);//设置内容
							$imginfo=getimagesize($data_vo[$key_vo]['info']);//读取图像信息
							$NodHeight=$imginfo[1]/($imginfo[0]/86);//计算行高|按照宽度缩放比例缩放
							$NodHeight>16&&($RowHeight=$NodHeight);//最小高度16
                        }
                    }else{
                        //文本信息
                        $shell->setCellValueExplicit($cellname[$cellnums].$rownums,$data_vo[$key_vo],PHPExcel_Cell_DataType::TYPE_STRING);//设置内容并指定文本格式
                    }
                    $cellnums++;//自增列数
                }
                $shell->getRowDimension($rownums)->setRowHeight($RowHeight);//设置数据行高
                $rownums++;//自增行数
            }
        }
	}
	//设置边框
	$shell->getStyle ('A1:'.$cellname[$max_cell-1].($rownums-1))->applyFromArray ([
        'borders'=>['allborders'=>['style'=>PHPExcel_Style_Border::BORDER_THIN]],
        'alignment'=>['vertical'=>PHPExcel_Style_Alignment::VERTICAL_CENTER]
    ]);
	//输出文件
	ob_get_contents()&&(ob_end_clean());//清除缓冲区,避免乱码
    $writer=PHPExcel_IOFactory::createWriter ($PHPExcel,'Excel2007');
    //判断文件操作
    if($down==true){
        //直接下载
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$file_name.'.xlsx"');
        header("Content-Disposition:attachment;filename=$file_name.xlsx");//attachment新窗口打印inline本窗口打印
        $writer->save ('php://output');
        exit;
    }else{
        //保存文件
        $file_path=ROOT_PATH.'skin/file/xlsx/'.$file_name.'.xlsx';
    	$writer->save ($file_path);
	    return $file_path;//返回文件路径
    }
}
//获取xlsx文件数据
function  get_xlsx($file){
	vendor("Execl.PHPExcel");
	$reader=PHPExcel_IOFactory::createReader ('Excel2007')->setReadDataOnly (true)->load ($file);//简易方式加载xlsx文件
	$resule=$reader->getSheet (0)->toArray (null,false,false,true);//获取首个工作簿信息并转为数组
	//过滤空白行
	foreach ($resule as $key=>$vo) {
	    if(count(array_unique($vo))==1){
	        unset($resule[$key]);
	    }
	}
	array_walk_recursive($resule,function(&$nod){$nod===null?($nod=''):$nod=htmlentities($nod);});//NULL转空白字符|拦截XSS
	return $resule;
}
//获取通用正则
function get_regex($nod){
    $regex=[
        'empty'=>"/^\s*$/g",//空判断
        'tel'=>"/^1\d{10}$/",//手机号判断
        'phone'=>"/^(\(\d{3,4}\)|\d{3,4}-|\s)?\d{7,14}$/",//座机号判断
        'tax'=>"/^[A-Z0-9]{15}$|^[A-Z0-9]{17}$|^[A-Z0-9]{18}$|^[A-Z0-9]{20}$/",//税号判断
        'number'=>"/^[0-9]*$/",//数字组合判断
        'plus'=>"/^\d+(\.\d{1,2})?$/",//含0正数判断最多2位小数
        'email'=>"/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/",//邮箱判断
        'time'=>"/^(19|20)\d{2}-(0?\d|1[012])-(0?\d|[12]\d|3[01])$/",//时间正则
        "numerical"=>"/^(\-)?\d+(\.\d{1,2})?$/",//正负数值2位小数
    ];
    return $regex[$nod];
}
//汉字转拼音
//$type[head:首字母|all:全拼音]
function zh2py($text,$type='head'){
    $nod=new \org\zh2py();
    $resule=$nod::encode($text,$type);
    return strtolower($resule);//返回结果转小写
    
}
//生成条形码
//$type[true:直接输出|false:保存文件]
function  txm($text,$type=true){
	$file_name=time().'_'.mt_rand();
	//当前时间戳加随机数
	$file_path=$_SERVER['DOCUMENT_ROOT'].'/skin/images/code/'.$file_name.'.png';
	$root=$_SERVER['DOCUMENT_ROOT'];
	require_once($root.'/vendor/Barcode/BCGFontFile.php');
	require_once($root.'/vendor/Barcode/BCGColor.php');
	require_once($root.'/vendor/Barcode/BCGDrawing.php');
	// 条形码的编码格式
	require_once($root.'/vendor/Barcode/BCGcode128.barcode.php');
	// 加载字体大小
	$font=new \BCGFontFile ($_SERVER['DOCUMENT_ROOT'].'/vendor/Barcode/Arial.ttf',18);
	//颜色条形码
	$color_black=new \BCGColor (0,0,0);
	$color_white=new \BCGColor (255,255,255);
	$drawException=null;
	try {
		$code=new \BCGcode128 ();
		$code->setScale (2);
		$code->setThickness (30);
		// 条形码的厚度
		$code->setForegroundColor ($color_black);
		// 条形码颜色
		$code->setBackgroundColor ($color_white);
		// 空白间隙颜色
		$code->setFont ($font);
		// 
		$code->parse ($text);
		// 条形码需要的数据内容
	}
	catch (Exception $exception){
		$drawException=$exception;
	}
	//根据以上条件绘制条形码
	$drawing=new \BCGDrawing ('',$color_white);
	if ($drawException){
		$drawing->drawException ($drawException);
	}else {
		$drawing->setBarcode ($code);
		$drawing->draw ();
	}
	// 生成PNG格式的图片
	if ($type){
		$drawing->finish (\BCGDrawing::IMG_FORMAT_PNG ,$file_path,$type);
		exit;
	}else {
		$drawing->finish (\BCGDrawing::IMG_FORMAT_PNG ,$file_path,$type);
		return $file_path;
	}
}
//生成二维码
//$type[true:直接输出|false:返回文件地址]
function ewm ($text,$type=true){
	$file_name=time().'_'.mt_rand();
	//当前时间戳加随机数
	vendor ("phpqrcode.phpqrcode");
	$size='6';
	$level='H';
	$padding=2;
	$nod=$_SERVER['DOCUMENT_ROOT'].'/skin/images/code/'.$file_name.'.png';
	$re=QRcode::png ($text,$nod,$level,$size,$padding);
	if ($type){
		ob_end_clean();
		//清除缓冲区,避免乱码
		header('Content-Type:image/png');
		imagepng(imagecreatefromstring(file_get_contents($nod)));
		exit ;
	}else {
		return $nod;
	}
}
//递归查询数据表树结构归属数据
function tree_sub ($model,$id){
    static $resule=[];
    $db=db($model);
	$info=$db->where(['pid'=>$id])->column ('id');
	foreach ($info as $vo) {
	    $sub=$db->where(['pid'=>$vo])->column ('id');
	    if(empty($sub)){
	        array_push($resule,$vo);
	    }else{
	        tree_sub($model,$vo);
	    }
	}
	array_push($resule,$id);
	return $resule;
}
//多维数组重组指定字段一维数组
function arraychange($arr,$key){
	return array_unique(array_column($arr,$key));
}
//查询数组指定字段合并赋值
//$type merge 组合 intersect 交集 
function sql_assign(&$sql,$field,$data,$type='merge'){
    if(isset($sql[$field])){
        if($type=='merge'){
            $arr=array_merge($sql[$field][1],$data);
        }elseif($type=='intersect'){
            $arr=array_intersect($sql[$field][1],$data);
        }
        $data=array_unique($arr);
    }
    $sql[$field]=['in',$data];
}
//合并单个二维数组
function arr_merge($arr){
    $resule=[];
    foreach ($arr as $vo) {
        $resule=array_merge($resule,$vo);
    }
    return array_unique($resule);
}
//压缩文件为ZIP并下载
function file_to_zip($zip_name,$file_arr,$down=true){
    del_time_file('skin/file/zip/');
    empty($file_arr)&&(die('[ 文件数据为空 ]'));//空数据检验
    $path="skin/file/zip/".$zip_name.".zip";
    $zip=new ZipArchive();
    if ($zip->open($path,ZIPARCHIVE::CREATE)!==TRUE) {
        exit('创建压缩文件失败!');
    }
    foreach ($file_arr as $file_vo) {
        $zip->addFile($file_vo,basename($file_vo));
    }
    $zip->close();
    if($down){
        header("Cache-Control: max-age=0");
        header("Content-Description: File Transfer");
        header('Content-disposition: attachment; filename='.basename($path)); //文件名
        header("Content-Type: application/zip"); //zip格式的
        header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
        header('Content-Length: '.filesize($path)); //告诉浏览器，文件大小
        @readfile($path);//输出文件;
        exit;
    }else{
        return true;
    }
}
//二维数组删除KEY指定条件数组
function arrs_key_del(&$arr,$condition){
    foreach ($arr as $key=>$vo) {
        //匹配数据
        if(isset($vo[$condition[0]]) && $vo[$condition[0]]==$condition[1]){
            unset($arr[$key]);
        }
    }
}
//GET POST提交
function http($url,$param,$action="GET"){
	$ch=curl_init();
	$config=array(CURLOPT_RETURNTRANSFER=>true,CURLOPT_URL=>$url);	
	if($action=="POST"){
		$config[CURLOPT_POST]=true;		
	}
	$config[CURLOPT_POSTFIELDS]=http_build_query($param);
	curl_setopt_array($ch,$config);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	$result=curl_exec($ch);	
	curl_close($ch);
	return $result;
}