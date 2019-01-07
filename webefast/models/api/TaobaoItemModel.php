<?php
/**
 * 淘宝商品相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('prm');

class TaobaoItemModel extends TbModel {
	function get_table() {
		return 'api_taobao_item';
	}

	/*
	 * 根据条件查询数据
	 */
	function get_by_page($filter) {
		//print_r($filter);
		$sql_join = "";
		$sql_main = "FROM {$this->table} rl $sql_join WHERE 1";
		//商品标题
		if (isset($filter['title']) && $filter['title'] != '') {
			$sql_main .= " AND rl.title LIKE :title ";
			$sql_values[':title'] = $filter['title'].'%';
		}
		//状态
		if (isset($filter['approve_status']) && $filter['approve_status'] != '') {
			$sql_main .= " AND rl.approve_status = :approve_status ";
			$sql_values[':approve_status'] = $filter['approve_status'];
		}
		//商品外部ID
		if (isset($filter['outer_id']) && $filter['outer_id'] != '') {
			$sql_main .= " AND rl.outer_id = :outer_id ";
			$sql_values[':outer_id'] = $filter['outer_id'];
		}
		// 店铺
		if (isset($filter['shop_code']) && $filter['shop_code'] != '') {
			$shop_code_arr = explode(',',$filter['shop_code']);
			if(!empty($shop_code_arr)){
				$sql_main .= " AND (";
				foreach($shop_code_arr as $key=> $value){
					$param_brand = 'param_brand'.$key;
					if($key == 0){
						$sql_main .= " rl.shop_code = :{$param_brand} ";
					}else{
						$sql_main .= " or rl.shop_code = :{$param_brand} ";
					}
						
					$sql_values[':'.$param_brand] = $value;
				}
				$sql_main .= ")";
			}
				
		}
		$select = 'rl.Id,rl.outer_id,rl.title,rl.num_iid,rl.price,rl.is_sale,rl.approve_status';
		//echo $sql_main;
		$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
		foreach ($data['data'] as $key => $value){
			$sql = "select id,sku_id,quantity,outer_id,is_wlb FROM api_taobao_sku where num_iid ='{$value[num_iid]}'";
			$rs = $this->db->get_all($sql);
			foreach ($rs as $k => $v){
				$data['data'][$key]['xq'][] = $v;
			}
		}
		//print_r($data);
		$ret_status = OP_SUCCESS;
		$ret_data = $data;
		
		return $this->format_ret($ret_status, $ret_data);
		exit;
		$html = '';
		
		$html .= "<tr class='bui-grid-header-row'>";
		$html .= "<td class=' grid-td-grid-hd1' width='100' style='height:0'></td>";
		$html .= "<td class=' grid-td-grid-hd2' width='100' style='height:0'></td>";
		$html .= "<td class=' grid-td-grid-hd3' width='100' style='height:0'></td>";
		$html .= "<td class=' grid-td-grid-hd3' width='100' style='height:0'></td>";
		$html .= "<td class=' grid-td-grid-hd3' width='100' style='height:0'></td>";
		$html .= "<td class=' grid-td-grid-hd3' width='100' style='height:0'></td>";
		$html .= "<td class=' grid-td-grid-hd3' width='100' style='height:0'></td>";
		$html .= "<td class=' grid-td-grid-hd3' width='100' style='height:0'></td>";
		$html .= "<td class=' grid-td-grid-hd3' width='100' style='height:0'></td>";
		$html .= "<td class=' grid-td-grid-hd3' width='100' style='height:0'></td>";
		$html .= "<td class=' grid-td-grid-hd3' width='100' style='height:0'></td>";
		$html .= "<td class=' grid-td-grid-hd4' width='100' style='height:0'></td>";
		$html .= "<td class=' grid-td-grid-hd4' width='100' style='height:0'></td>";
		$html .= "<td class=' grid-td-grid-hd4' width='100' style='height:0'></td>";
		$html .= "<td class='bui-grid-cell bui-grid-cell-empty'> </td>";
		$html .= "</tr>";
		
		foreach ($data['data'] as $key => $value){
			$html .= "<tr class='bui-grid-row bui-grid-row-odd'>";
			$html .= "<td class=' bui-grid-cell grid-td-grid-hd1' data-column-field='brand_code' data-column-id='grid-hd1'>";
			$html .= "<div class='bui-grid-cell-inner'>";
			$html .= "<span class='bui-grid-cell-text ' title=''>{$value['title']}</span>";
			$html .= "</div>";
			$html .= "</td>";
			$html .= "<td class=' bui-grid-cell grid-td-grid-hd2' data-column-field='brand_name' data-column-id='grid-hd2'>";
			$html .= "<div class='bui-grid-cell-inner'>";
			$html .= "<span class='bui-grid-cell-text ' title=''>{$value['outer_id']}</span>";
			$html .= "</div>";
			$html .= "</td>";
			$html .= "<td class=' bui-grid-cell grid-td-grid-hd3' data-column-field='remark' data-column-id='grid-hd3'>";
			$html .= "<div class='bui-grid-cell-inner'>";
			$html .= "<span class='bui-grid-cell-text ' title=''>{$value['num_iid']}</span>";
			$html .= "</div>";
			$html .= "</td>";
			
			$html .= "<td class=' bui-grid-cell grid-td-grid-hd3' data-column-field='remark' data-column-id='grid-hd3'>";
			$html .= "<div class='bui-grid-cell-inner'>";
			$html .= "<span class='bui-grid-cell-text ' title=''>{$value['approve_status']}</span>";
			$html .= "</div>";
			$html .= "</td>";
			
			$html .= "<td class=' bui-grid-cell grid-td-grid-hd3' data-column-field='remark' data-column-id='grid-hd3'>";
			$html .= "<div class='bui-grid-cell-inner'>";
			$html .= "<span class='bui-grid-cell-text ' title=''>{$value['price']}</span>";
			$html .= "</div>";
			$html .= "</td>";
			
			$html .= "<td class=' bui-grid-cell grid-td-grid-hd3' data-column-field='remark' data-column-id='grid-hd3'>";
			$html .= "<div class='bui-grid-cell-inner'>";
			$html .= "<span class='bui-grid-cell-text ' title=''>库存同步</span>";
			$html .= "</div>";
			$html .= "</td>";
			
			$html .= "<td class=' bui-grid-cell grid-td-grid-hd3' data-column-field='remark' data-column-id='grid-hd3'>";
			$html .= "<div class='bui-grid-cell-inner'>";
			$html .= "<span class='bui-grid-cell-text ' title=''>{$value['is_sale']}</span>";
			$html .= "</div>";
			$html .= "</td>";
			
			$html .= "<td><table  border='1'  style='width: 700px;'   cellspacing='0'  >";
			
			$sql = "select id,sku_id,quantity,outer_id FROM api_taobao_sku where num_iid ='{$value[num_iid]}'";
			$rs = $this->db->get_all($sql);
			
			foreach ($rs as $k => $v){
			$html .= "<tr>";
			$html .= "<td  style='width: 103px;' class=' bui-grid-cell grid-td-grid-hd3' data-column-field='remark' data-column-id='grid-hd3'>";
			$html .= "<div class='bui-grid-cell-inner'>";
			$html .= "<span class='bui-grid-cell-text ' title=''>{$v['sku_id']}</span>";
			$html .= "</div>";
			$html .= "</td>";
			$html .= "<td style='width: 104px;' class=' bui-grid-cell grid-td-grid-hd3' data-column-field='remark' data-column-id='grid-hd3'>";
			$html .= "<div class='bui-grid-cell-inner'>";
			$html .= "<span class='bui-grid-cell-text ' title=''>{$v['outer_id']}</span>";
			$html .= "</div>";
			$html .= "</td>";
			$html .= "<td style='width: 104px;' class=' bui-grid-cell grid-td-grid-hd3' data-column-field='remark' data-column-id='grid-hd3'>";
			$html .= "<div class='bui-grid-cell-inner'>";
			$html .= "<span class='bui-grid-cell-text ' title=''>{$v['quantity']}</span>";
			$html .= "</div>";
			$html .= "</td>";
			$html .= "<td style='width: 105px;' class=' bui-grid-cell grid-td-grid-hd3' data-column-field='remark' data-column-id='grid-hd3'>";
			$html .= "<div class='bui-grid-cell-inner'>";
			$html .= "<span class='bui-grid-cell-text ' title=''>aaa</span>";
			$html .= "</div>";
			$html .= "</td>";
			$html .= "<td style='width: 105px;' class=' bui-grid-cell grid-td-grid-hd3' data-column-field='remark' data-column-id='grid-hd3'>";
			$html .= "<div class='bui-grid-cell-inner'>";
			$html .= "<span class='bui-grid-cell-text ' title=''>aaa</span>";
			$html .= "</div>";
			$html .= "</td>";
			$html .= "<td style='width: 105px;' class=' bui-grid-cell grid-td-grid-hd3' data-column-field='remark' data-column-id='grid-hd3'>";
			$html .= "<div class='bui-grid-cell-inner'>";
			$html .= "<span class='bui-grid-cell-text ' title=''>aaa</span>";
			$html .= "</div>";
			$html .= "</td>";
			$html .= "<td style='width: 100px;' class=' bui-grid-cell grid-td-grid-hd4' data-column-field='_operate' data-column-id='grid-hd4'>";
			$html .= "<div class='bui-grid-cell-inner'>";
			$html .= "<span class='bui-grid-cell-text ' title=''>";
			$html .= '<span class="grid-command edit" onClick="store_synchro(\''.$v['quantity'].'\');">库存同步</span>';
			$html .= "<span class='grid-command delete'>移除</span>";
			$html .= "</span>";
			$html .= "</div>";
			$html .= "</td>";
			$html .= "<td class='bui-grid-cell bui-grid-cell-empty'> </td>";
			$html .= "</tr>";
			}
			
			$html .= " </table></td>";
			$html .= "</tr>";
			
		}
		
		echo $html;
		exit;
	}
	
	//店铺
	function get_shop(){
		$sql = "select id,shop_code,shop_name FROM base_shop ";
		$rs = $this->db->get_all($sql);
		return $rs;
	}
}