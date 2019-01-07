<?php
require_once "cls_mysql.php";
global $sysdb;
//配置自己的数据库
$sysdb = new cls_mysql('127.0.0.1','root','root','efast5_b2','utf8');


$request = $_REQUEST ;
$request['act'] = !isset($request['act'])?'list':$request['act'];
$file_arr = array();

if($request['act']=='list'){    
    $file_path = __DIR__ . DIRECTORY_SEPARATOR . "update";
    $file_arr = get_file($file_path);   
}else if($request['act']=='run_file'){    
    run_file($request['filename'],$request['is_fz'] );
    
}
$run_info = array();

function run_one_sql($request){
        require_once "update/".$file;
        list($version_patch,$file_kz)  = explode(".", $file); 
       $key_str =  $request['key'] ;
       $key = $key_str;
       $_key = '';
       if(strpos($key_str, "_")!=false){
           list($key,$_key) = explode("_", $key_str);
       }       
       $sql_content = $u[$key] ;
       if(!empty($_key)){
          $sql_content = $u[$key][$_key] ;
       }
        $sql_data['is_fz'] = $request['is_fz'];
        $sql_data['version_patch'] = $version_patch;
        $sql_data['task_sn'] = $key."_".$_key;
        $sql_data['content'] = $sql_content;
        
        if( $is_fz==1 ){
              $db = new cls_mysql('192.168.150.30','efast','efast','efast5_b2','utf8');
         }else{
              $db = new cls_mysql('192.168.164.205','efast','efast','efast5.0.1','utf8');
         }
         
         
        $ret = run_sql($db,$sql_data);
      //  echo json_encode($ret);die;
}

function set_result_html(&$run_info){
    $html = "";
    foreach($run_info as $ret){
     $html .=  "<div id=\"{$ret['data']['task_sn']}\">";
        $html .=  set_result_one($ret);
         $html .=  "</div>";
    }
    return $html;
}
function set_result_one($ret){
    $g = "<div>==========================================</div>";
    $html = "sql:<br>{$ret['data']['content']}<br />执行结果：<br/>";
    
    if($ret['status']<0){
        $html ="<div style=\"color:red\">".$html."执行失败（{$ret['message']}）<br/>";
    }else{
         $html ="<div>".$html."执行成功"; 
    }
   return $g.$html."</div>";   
    
}


function run_file($file,$is_fz = 1){
    
     require_once "update/".$file;
     list($version_patch,$file_kz)  = explode(".", $file);
     
     if( $is_fz==1 ){
          $db = new cls_mysql('192.168.150.30','efast','efast','efast5_b2');
     }else if( $is_fz==0){
          $db = new cls_mysql('192.168.164.205','efast','efast','efast5.0.1');
     }else if( $is_fz==2){
         global $sysdb;
          $db = $sysdb;
     }
   //  var_dump($u);die;
     foreach($u as $key=>$sql_data){
             foreach($sql_data as $_key=>$sql_content){
                 $sql_data['is_fz'] = $is_fz;
                 $sql_data['version_patch'] = $version_patch;
                 $sql_data['task_sn'] = $key."_".$_key;
                 $sql_data['content'] = $sql_content;
                 $sql_data['sql_code'] = md5($sql_content);
                 $ret = run_sql($db,$sql_data);
                 $run_info[] = $ret;
             }
     }
    echo  set_result_html($run_info);
     die;
}



function run_sql($db,$sql_data,$is_check = true){
     $is_exec = TRUE;
    if($is_check===true){
        $is_exec = check_sql($sql_data);
    }
    $ret = array('status'=>1,'data'=>$sql_data);
    if($is_exec===TRUE){
        try{
            $db->query($sql_data['content']);
            save_run_data($sql_data);
        }  catch (Exception $e){
           $ret['status'] = -1;
           $ret['message'] = $e->getMessage();
        }
    }else{
          $ret['status'] = -1;
          $ret['message'] = "已经执行过"; 
    }
    return $ret;
}
function save_run_data($sql_data){
    global $sysdb;
    if($sql_data['is_fz']<2){
          $sysdb->insert("osp_version_patch_sql", $sql_data);
    }
   
}

function check_sql($sql_data){
    if($sql_data['is_fz']>1){
        return true;
    }
    
    $sql = "select * from osp_version_patch_sql where version_patch='{$sql_data['version_patch']}'
    AND sql_code='{$sql_data['sql_code']}' AND task_sn='{$sql_data['task_sn']}' AND is_fz='{$sql_data['is_fz']}'";
    global $sysdb;
    $data = $sysdb->getRow($sql);
    if(!empty($data)){
        return false;
    }
    return true;
}


function get_file($path){
      $current_dir = opendir($path);
    //readdir()返回打开目录句柄中的一个条目
        $arr = array();
 	while(($file = readdir($current_dir)) !== false)
 	{
	    //构建子目录路径
 		$sub_dir = $path . DIRECTORY_SEPARATOR . $file;
 		if($file == '.' || $file == '..' || $file == '.svn'){
 			continue;
 		}elseif(is_dir($sub_dir)){
 			//如果是目录,进行递归
 			//echo 'Directory ' . $file . ':<br>';
 			//traverse($sub_dir);
 		}else{
 			//如果是文件,直接输出
 			if(preg_match("/[\w]+\.php$/", $file) == 0){
				//echo "文件:".$path .DIRECTORY_SEPARATOR. $file."不拷贝<br />";
 				continue;

                    }else{
 				$arr[] = $file;
			}
 		}
 	}
        rsort($arr);
        return $arr;
    }   
    
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-Type" content="text/html; charset=UTF-8" />
<title> sql脚本执行 </title>

<script type="text/javascript" src="../../webpub/js/jquery-1.8.1.min.js"></script>
</head>
<body style="overflow-x:hidden;">
    <div style="padding-top: 100px;padding-left: 300px;">
        选择脚本
        <select id="filename" name="filename"> 
            <?php foreach($file_arr as $val):?>
            <option values="<?php echo $val?>"><?php echo $val?></option>
            <?php endforeach; ?>
        </select>
        &nbsp; &nbsp; &nbsp;
        <!--button id="btn-fz"    name="fz_run"  type="button">分支执行</button-->
   &nbsp; &nbsp; &nbsp;
            <!--button id="btn-zx"   name="zx_run"  type="button">主线执行</button-->
            
                     &nbsp; &nbsp; &nbsp;
                    <button id="btn-hj"   name="zx_run"  type="button">本地执行</button>
    </div>
    <div style="padding-left: 150px;padding-top: 30px;padding: 0 auto; text-align: center;">
  
      <div id="result" style="background-color:#DFE7EF; width:800px; height: 500px;overflow:auto;text-align: left;"  >
         
      </div>
    </div>
    

    <script>
    var fz = 1;
    function run(is_fz){
         var data = {};
         fz = is_fz;
         data.act ='run_file';
         data.is_fz = is_fz;
         data.filename = $('#filename').val();
         $.post("?act=run_file",data,function(result){
             $('#result').html(result);
        });
    }
        function run_sql(key){
         var data = {};
         data.act ='run_sql';
         data.is_fz = fz;
         data.key = key;
         data.filename = $('#filename').val();
         $.post("?act=run_sql",data,function(result){
               $('#'+key).html(result); 
        });
    }
    $(function(){
		/*
        $("#btn-fz").click(function(){
            run(1);
        });
        $("#btn-zx").click(function(){
             run(0);
        }); 
		*/
         $("#btn-hj").click(function(){
             run(2);
        });  
    });
    </script>
    

 </body>
</html>