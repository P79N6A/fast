<?php

/**
 * sync_mode = incr 增量 | all 全量 | fix 上传出错的再次上传
 */
require_model('wms/WmsBaseModel');

class YdwmsArchiveModel extends WmsBaseModel {

    var $sync_mode = '';
    var $sync_success = 0;
    var $goods_spec;
    function __construct($efast_store_code) {
        parent::__construct();
        $this->get_wms_cfg($efast_store_code);
    }
    
    function sync(){
        $this->sync_barocde();
    }

    function sync_barocde($sync_success = 1) {
        $this->sync_success  = $sync_success;
        $barocde_num = $this->get_barocde_num();
        $limit = 20;
        $page = 1;
        $this->set_spec_name();
        $page_count = ceil($barocde_num / $limit) + 1;
        while ($page < $page_count) {
            $data = $this->get_barocde($page, $limit,$sync_success);
            $up_data = array();
            $log_data = array();
            foreach ($data as $barcode_info){
                $this->set_upload_info($barcode_info,$up_data,$log_data);
                //break;
            }
            
            if(!empty($up_data)){
               // $this->insert_multi_duplicate('wms_archive', $log_data, " tbl_changed = VALUES(tbl_changed),is_success = VALUES(is_success) ");

                $ret = $this->biz_req('putSKUData', $up_data);
                $ret_data = array();
                
                if(isset($ret['data']['return']['returnCode'])&&$ret['data']['return']['returnCode']=='0000'){
                    $ret_data = $ret['data']['return']['succInfos']['succInfo'];
                }else if(isset($ret['data']['return']['resultInfo'])){
                    $ret_data = $ret['data']['return']['resultInfo'];
                }
          
                    $new_log = array();
                  foreach($ret_data as $val){
                        if(!empty($val['SKU'])&&isset($log_data[$val['SKU']])){
                            $sub_log = $log_data[$val['SKU']];
                            $sub_log['api_code'] = $val['itemId']; 
                            $sub_log['is_success'] = 1; 
                            $new_log[] = $sub_log;
                        }
                  }
                  if(!empty($new_log)){
                        $this->insert_multi_duplicate('wms_archive', $new_log, " tbl_changed = VALUES(tbl_changed),is_success = VALUES(is_success) ,api_code = VALUES(api_code) ");
                  }
       
            }
            
            if(count($data)<$limit){
             //   break;
            }
            $page++;
        }
        $this->sync_success = $this->sync_success -1;
        if($this->sync_success>-1){
            $this->sync_barocde($this->sync_success);
        }

    }
    
    function update_is_success($barcode_arr) {
        $barcode_str = "'".implode("','", $barcode_arr)."'";

        $sql = "update wms_archive  set is_success=1  where efast_store_code = '{$this->wms_cfg['efast_store_code']}' AND type ='goods_barcode' AND  code in({$barcode_str})  ";
        $this->db->query($sql);
        //echo $sql;die;
    }
	function get_tbl_changed($type){
            static  $tbl_changed = null;
            if(empty($tbl_changed)){
            $sql = "select tbl_changed from wms_archive where api_product = :api_product and type = :type order by tbl_changed desc";
		$tbl_changed = ctx()->db->getOne($sql,array(':api_product'=>'ydwms',':type'=>$type));
		$tbl_changed = empty($tbl_changed) ? '0000-00-00 00:00:00' : $tbl_changed;
            }
		return $tbl_changed;
        }
    function get_barocde($page = 1, $limit = 10,$sync_success=1) {
        $sql_values = array();
        $type ='goods_barcode';
        if($sync_success==1){ //update
            $tbl_changed = $this->get_tbl_changed($type);
             $sql = "select b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode,b.lastchanged,b.gb_code,wa.api_code  from goods_sku b ";
             $sql.=" INNER JOIN   wms_archive wa ON wa.code = b.sku    ";
             $sql.=" WHERE 1 AND b.lastchanged>'{$tbl_changed}' and wa.type ='{$type}' and  api_code<>'' ";
        }else if($sync_success==0){ //add
           $sql = "select b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode,b.lastchanged,b.gb_code 
                from goods_sku b 
                  WHERE b.sku  not in( SELECT code from wms_archive  where type=:type  and  api_code<>'' ) "   ;
                $sql_values =array(':type'=>$type);
        }
       //$sql.=" AND wa.code='101012002' ";
        $start = ($page - 1) * $limit;
        $sql.=" LIMIT {$start},$limit ";
      //  echo $sql;
     //   return $this->db->get_all($sql,$sql_values);
         return $this->db->get_limit($sql,$sql_values,$limit,$start);
        
    }
    
    
    

    function get_barocde_num() {
        return $this->db->get_value("select count(1) from goods_sku");
    }

    function set_upload_info($barocde_info,&$up_data,&$log_data) {
//        $goods_info = $this->get_goods_info($barocde_info['goods_code']);
//        $spec1_info = $this->get_spec1_info($barocde_info['spec1_code']);
//        $spec2_info = $this->get_spec2_info($barocde_info['spec2_code']);
        
        
         $key_arr = array('sku_id','goods_code','barcode','spec1_code','spec2_code','spec1_name','spec2_name','goods_name','brand_name','year_name','season_name','category_name');
         $sku_info =  load_model('goods/SkuCModel')->get_sku_info($barocde_info['sku'],$key_arr);

        
        $info = array('CustomerID' => $this->wms_cfg['customerid']);
 
        $info['actionType'] = 'add';
    
        if(isset($barocde_info['api_code'])&&!empty($barocde_info['api_code'])){
            $info['itemId'] = $barocde_info['api_code'];
            $info['actionType'] = 'update';
        }
        
        $info['SKU'] = $barocde_info['barcode'];
        $info['Active_Flag'] = 'Y';
        $info['Descr_C'] = $sku_info['goods_name'] . ":";
        $info['Descr_C'] .= $this->goods_spec['goods_spec1'] . ":" . $sku_info['spec1_name'] . ",";
        $info['Descr_C'] .= $this->goods_spec['goods_spec2'] . ":" . $sku_info['spec2_name'];
        $info['SKU_Group1'] = empty($sku_info['goods_code'])?'':$sku_info['goods_code'];
        $info['SKU_Group2'] =  empty($sku_info['spec1_name'])?'':$sku_info['spec1_name'];
        $info['SKU_Group3'] = empty($sku_info['spec2_name'])?'':$sku_info['spec2_name'];
        $info['SKU_Group4'] = empty($sku_info['brand_name'])?'':$sku_info['brand_name'];
        $info['SKU_Group5'] = empty($sku_info['year_name'])?'':$sku_info['year_name'];
        $info['SKU_Group6'] = empty($sku_info['season_name'])?'':$sku_info['season_name'];
        $info['SKU_Group7'] =  empty($sku_info['category_name'])?'':$sku_info['category_name'];
        //国标码
        //$info['Alternate_SKU1'] = $barocde_info['gb_code'];
       $child_data =  $this->get_child_barcode_by_barcode($barocde_info['barcode']);
       if(!empty($child_data)){
           $i = 1;
           foreach ($child_data as  $val){
               if($i<6){
                   $info['Alternate_SKU'.$i] = $val['barcode'];
               }else{
                   break;
               }
               $i++;
           }
       }
        $up_data[]=array('header'=>$info);
        //todo: 待检查完善
        $log_data[$barocde_info['barcode']] = array('api_product'=>$this->wms_cfg['api_product'],'wms_config_id'=>$this->wms_cfg['wms_config_id'],'efast_store_code'=>$this->wms_cfg['efast_store_code'],'type'=>'goods_barcode','code'=>$barocde_info['sku'],'sys_code'=>$barocde_info['barcode'],'tbl_changed'=>$barocde_info['lastchanged'],'is_success'=>0);
    }
    function get_child_barcode_by_barcode($barcode){
        $sql = "select c.barcode from goods_barcode_child c
                    INNER JOIN goods_sku p ON c.sku=p.sku
                    where p.barcode='{$barcode}'";
        return $this->db->get_all($sql);

    }
    
    function get_goods_info($goods_code) {
        static $goods_arr;
        if (!isset($goods_arr[$goods_code])) {
            $sql = "select g.goods_code,g.goods_name,c.category_name,b.brand_name,s.season_name,y.year_name from base_goods g
                                    LEFT JOIN base_category c ON g.category_code = c.category_code
                                    LEFT JOIN base_brand b ON g.brand_code = b.brand_code
                                    LEFT JOIN base_season s ON g.season_code = s.season_code
                                    LEFT JOIN base_year y on g.year_code = y.year_code 
                                    WHERE goods_code='{$goods_code}'";
            $goods_arr[$goods_code] = $this->db->get_row($sql);
        }
        return $goods_arr[$goods_code];
    }

    function get_spec1_info($spec1_code) {
        static $spec1_arr;
        if (!isset($spec1_arr[$spec1_code])) {
            $spec1_arr[$spec1_code] = $this->db->get_row("select spec1_code,spec1_name from base_spec1 WHERE spec1_code='{$spec1_code}' ");
        }
        return $spec1_arr[$spec1_code];
    }

    function get_spec2_info($spec2_code) {
        static $spec2_arr;
        if (!isset($spec2_arr[$spec2_code])) {
            $spec2_arr[$spec2_code] = $this->db->get_row("select spec2_code,spec2_name from base_spec2 WHERE spec2_code='{$spec2_code}' ");
        }
        return $spec2_arr[$spec2_code];
    }

    function set_spec_name() {
        $this->goods_spec = load_model('sys/SysParamsModel')->get_val_by_code(array('goods_spec1', 'goods_spec2'));
    }

}
