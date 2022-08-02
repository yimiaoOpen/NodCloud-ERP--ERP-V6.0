<?php
namespace addons\speed\controller;
use think\Controller;
use app\index\model\Plug;
class Main extends Controller{
    private $plug;
    //初始化
    public function _initialize(){  
        $this->plug = Plug::where(['only'=>'speed'])->find();
        $this->check_cache_file();
    }
    // 输出页面数据
    public function ViewFilter(&$content){
        //1.压缩HTML
        $nod=compress_html($content);
        //2.提取合并CSS
        $css_regular = '/<link[^>]+href=(?>\'|")(.*?\.css)(?>\'|").*?>/i';
        //匹配<link开头|匹配首个非>(闭合标记)|匹配href=|匹配单双引号|匹配CSS路径|匹配单双引号|匹配任意字符|匹配闭合标签>|i不区分大小
        preg_match_all($css_regular,$nod,$match_css);//正则匹配
        //判断是否匹配到数据
        if(!empty($match_css[1])){
            $exclude_css=$this->plug['config']['exclude_css'];//获取过滤的CSS
            $diff_css=array_diff($match_css[1],$exclude_css);//取CSS数据交集
            if(!empty($diff_css)){
                $css_file_name=substr(md5(implode('|', $diff_css)),8,16).'.css';//CSS文件16位MD5名称
                $css_file_nod=DS.'skin'.DS.'speed'.DS.'css'.DS.$css_file_name;//CSS文件路径
                $css_cache=file_exists(ROOT_PATH.$css_file_nod)?true:false;//判断缓存文件是否存在
                $css_cache||($css_file_info="");//缓存不存在|初始化CSS文件数据内容
                //开始循环添加数据
                foreach ($diff_css as $diff_css_key=>$diff_css_vo) {
                    $css_cache||($css_file_path=strpos($diff_css_vo,'//')?$diff_css_vo:rtrim(ROOT_PATH,DS).$diff_css_vo);//缓存不存在|如果存在'//'则为外部路径否则拼接本地路径
                    $css_cache||($css_file_info.=compress_css(file_get_contents($css_file_path)));//缓存不存在|拼接读取CSS数据并压缩
                    $nod=str_replace($match_css[0][$diff_css_key],'',$nod);//删除HTML该CSS标签数据
                }
                $css_cache||(file_put_contents(ROOT_PATH.$css_file_nod,$css_file_info));//缓存不存在|写入缓存文件
                $nod=str_replace('</head>','<link rel="stylesheet" href="'.$css_file_nod.'" media="all"/></head>',$nod);//HTML删除该标签数据
            }
        }
        //3.提取合并JS
        $js_regular = '/<script[^>]+src=(?>\'|")(.*?\.js)(?>\'|").*?<\/script>/i';
        //匹配<script开头|匹配首个非>(闭合标记)|匹配src=|匹配单双引号|匹配JS路径|匹配单双引号|匹配任意字符|匹配闭合标签</script>|i不区分大小
        preg_match_all($js_regular,$nod,$match_js);//正则匹配
        //判断是否匹配到数据
        if(!empty($match_js[1])){
            $exclude_js=$this->plug['config']['exclude_js'];//获取过滤的JS
            $diff_js=array_diff($match_js[1],$exclude_js);//取JS数据交集
            if(!empty($diff_js)){
                $js_file_name=substr(md5(implode('|', $diff_js)),8,16).'.js';//JS文件16位MD5名称
                $js_file_nod=DS.'skin'.DS.'speed'.DS.'js'.DS.$js_file_name;//JS文件路径
                $js_cache=file_exists(rtrim(ROOT_PATH,DS).$js_file_nod)?true:false;//判断缓存文件是否存在
                $js_cache||($js_file_info="");//缓存不存在|初始化JS文件数据内容
                //开始循环添加数据
                foreach ($diff_js as $diff_js_key=>$diff_js_vo) {
                    $js_cache||($js_file_path=strpos($diff_js_vo,'//')?$diff_js_vo:rtrim(ROOT_PATH,DS).$diff_js_vo);//缓存不存在|如果存在'//'则为外部路径否则拼接本地路径
                    if(!$js_cache){
                        //缓存不存在|拼接读取JS数据并压缩
                        $jsmin=new \org\jsmin();
                        $js_file_info.=';'.$jsmin::minify(file_get_contents($js_file_path));
                    }
                    $nod=str_replace($match_js[0][$diff_js_key],'',$nod);//删除HTML该JS标签数据
                }
                $js_cache||(file_put_contents(rtrim(ROOT_PATH,DS).$js_file_nod,$js_file_info));//缓存不存在|写入缓存文件
                $nod=str_replace('</body>','<script src="'.$js_file_nod.'" type="text/javascript" charset="utf-8"></script></body>',$nod);//HTML删除该标签数据
            }
        }
        //4.赋值新数据
        $content=$nod;
    }
    //检查清理缓存文件
    public function check_cache_file(){
        $cache_time=$this->plug['config']['cache_time'];//获取文件过期小时数
        //读取css文件列表
        $css_path=ROOT_PATH.'skin'.DS.'speed'.DS.'css'.DS;
        $css_list=scandir($css_path);
        foreach ($css_list as $css_list_vo) {
            if(!in_array($css_list_vo,['.','..'])){
                $create_time=filectime($css_path.$css_list_vo);//获取文件创建时间
                //判断文件时间是否超时
                if($create_time+$cache_time*3600<time()){
                    @unlink($css_path.$css_list_vo);//超时删除文件
                }
            }
        }
        //读取JS文件列表
        $js_path=ROOT_PATH.'skin'.DS.'speed'.DS.'js'.DS;
        $js_list=scandir($js_path);
        foreach ($js_list as $js_list_vo) {
            if(!in_array($js_list_vo,['.','..'])){
                $create_time=filectime($js_path.$js_list_vo);//获取文件创建时间
                //判断文件时间是否超时
                if($create_time+$cache_time*3600<time()){
                    @unlink($js_path.$js_list_vo);//超时删除文件
                }
            }
        }
    }
    //保存参数
    public function save(){
        $input=input('post.',null,'html_entity_decode');//兼容XSS防护
        if(isset_full($input,'cache_time')){
            Plug::where(['only'=>'speed'])->update(['config'=>json_encode($input)]);
            return json (['state'=>'success']);
        }else{
            return json(['state'=>'error','info'=>'传入参数不完整!']);
        }
    }
}