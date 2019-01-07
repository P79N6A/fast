<?php
class CsvImport extends BaseModel{

 var $upload_path;
  var $is_iconv = 1;
 function  __construct() {
	$this->upload_path = ROOT_PATH . ctx()->app_name . '/uploads/';
 }
 function get_upload($file_fld = ''){
	  $file_fld = empty($file_fld) ? 'fileData' : '';
	  $err_code = $_FILES[$file_fld]['error'];
	  if($err_code >0){
	    return $this->format_ret(-1,'','文件上传失败,err_code='.$err_code);
	  }
	  $ext = pathinfo($_FILES[$file_fld]['name'], PATHINFO_EXTENSION);
	  
	  if (strtolower($ext)!='csv'){
	    return $this->format_ret(-1,'','请上传csv格式的文件');
	  }
	  $new_file = 'import_sell_record_'.uniqid().'.csv';
	  $t = move_uploaded_file($_FILES[$file_fld]['tmp_name'],$this->upload_path.$new_file);
	  if (!$t){
	    return $this->format_ret(-1,'','保存上传文件失败');	  
	  }
	  return $this->format_ret(1,$new_file);
 }

  function get_csv_data($upload_file_name,$head_line = 2,$skip_line = 0,$batch_line = -1){
   $upload_file = $this->upload_path.$upload_file_name;
   $fp = fopen($upload_file,'r');

   $line = 1;
   $head = array();   
   if ($head_line>0){
	    while(1){
		    $t = fgetcsv ( $fp ,  1000 ,  "," );
		    $t = $this->my_iconv($t);
			if ($line == $head_line){
				$head = $t;
				break;
			}
			$line++;
	    }
   }
   
   $data_arr = array();

   while(1){  
    $t = fgetcsv ( $fp ,  1000 ,  "," );
	if ($skip_line>0 && $line<=$skip_line){
		$line++;	
		continue;
	}
    if(empty($t)){
      break;
    }else{
 
	  $t = $this->my_iconv($t);
            
      if(!empty($head)){
        $row = array();
        foreach($head as $hk=>$hv){
           $row[$hv] = $t[$hk];
        }
        $data_arr[] = $row;
      }else{
        $data_arr[] = $t;
      }
    }
    $line++;
    if($line>=$batch_line && $batch_line>-1){//&& $batch_line>-1
      break;
    }
   }
   
   $end_flag = $line<$batch_line ? 1 : 0;
   $arr = array('data'=>$data_arr,'next_line'=>$line);
   return $this->format_ret(1,$arr);
  }

  function my_iconv($arr){
	  $result = array();
          if($this->is_iconv==1){
            foreach($arr as $k=>$v){
                  $result[$k] = iconv('gbk','utf-8',$v);
            }
          }else{
              $result =  $arr;
          }
	  return $result;
  }
  
}