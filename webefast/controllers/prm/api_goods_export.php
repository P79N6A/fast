<?php
/**
 * 商品控制器相关业务
 * @author dfr
 *
 */
require_lib ( 'util/web_util', true );
class api_goods_export {
	function do_list(array & $request, array & $response, array & $app){
		//仓库
		$response['shop'] = ds_get_select('shop',2,array('sale_channel_code'=>'taobao'));
		//print_r($response);
	}
	//导出商品信息
	function export(array &$request, array &$response, array &$app) {

        $ret = load_model('prm/ApiGoodsEexportModel')->export($request);
        //print_r($ret);

        $file_str = '';
        foreach ($ret as $v) {
            $fenge = explode(';', $v['sku_properties_name']);
            $fen1 = explode(':', $fenge[0]);
            $fen2 = explode(':', $fenge[1]);
            $spec1 = $fen1[3];
            $spec2 = $fen2[3];

            foreach ($v as $k => $sv) {
                $v[$k] = str_replace(array(chr(7), chr(10), chr(13), ','), array('', '', '', '，'), $sv);
            }


            $file_str .= $v['goods_code'] . ',' . $v['goods_name'] . ',,,,,,,,,' . $v['price'] . ',,,,,' . $spec1 . ',' . $spec2 . ',' . $v['goods_barcode'] . ',,,,,,,,,,,,,,,,,,,,,' . "\n";
        }

        $str = load_model('prm/GoodsImportModel')->hunhe_bie();
        $str1 = "商品编码*,商品名称*,商品简称,商品分类*#,商品品牌*#,商品季节#,商品年份#,商品属性,商品状态,商品重量(克),吊牌价,成本价,批发价,进货价,简单描述,规格1,规格2,商品条形码," . $str . "\n";
        $header_str = iconv("utf-8", 'gbk', "导入商品列表（*号为必填项，#号表示若匹配不成功会重新建立） \n");
        $header_str .= iconv("utf-8", 'gbk', $str1);

        $filename = "hunhedaoru.csv";
        $filename = iconv('UTF-8', 'GBK', $filename);
        //header( "Cache-Control: public" );
        header('Cache-Control:   must-revalidate,   post-check=0,   pre-check=0');
        header("Pragma: public");
        header("Content-type:application/vnd.ms-excel");
        //header("Content-type: text/csv");
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Content-Type:APPLICATION/OCTET-STREAM');
        //$file_str=  iconv("utf-8",'gbk',$file_str);
        //$file_str = iconv('UTF-8', 'GBK', $file_str);
        $file_str = mb_convert_encoding($file_str, "GBK", "UTF-8");
        ob_end_clean();

        echo $header_str;
        echo $file_str;
        die;
    }

}
