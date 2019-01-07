<?php

/*
 * 库存差异对照报表
 */
 class inventory_control{
     function do_list(array & $request, array & $response, array & $app){
         
     }
     
     //下载报表
     function down_compare_data(array &$request, array &$response, array &$app){
         load_model('rpt/SellGoodsReportModel')->down_compare_data($request['compare_code'], $request['store_code']);
     }
 }