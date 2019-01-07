<?php
//use Top\schema\factory\SchemaReader;
//require_lib('Top/schema/enums/FieldType', true);
//require_lib('Top/schema/enums/RuleType', true);
//require_lib('Top/schema/property/Property', true);
//require_lib('Top/schema/option/Option', true);
//require_lib('Top/schema/label/Label', true);
//require_lib('Top/schema/label/LabelGroup', true);
//require_lib('Top/schema/depend/DependExpress', true);
//require_lib('Top/schema/depend/DependGroup', true);
//require_lib('Top/schema/value/ComplexValue', true);
//require_lib('Top/schema/exception/TopSchemaException', true);
//require_lib('Top/schema/exception/TopSchemaException', true);
//
//
//require_lib('Top/schema/util/StringUtil', true);
//
//require_lib('Top/schema/factory/SchemaFactory', true);
//require_lib('Top/schema/field/SingleCheckField', true);
//require_lib('Top/schema/field/OptionsField', true);
//namespace Top\schema;

//require_lib('Top/schema/factory/SchemaWriter', true);
//require_lib('Top/schema/factory/SchemaReader', true);
use Top\schema\factory\SchemaWriter;
use Top\schema\factory\SchemaReader;

require_lib('util/web_util', true);
require_model('api/item/TbItemModel');



function __autoload($class) {
   
  if(strpos($class,"\\")!==false){
    $class = ROOT_PATH."lib/".$class;

    //$class = str_replace("\", "/", $class) .'.php';
    $class = str_replace('\\','/',$class).'.php';
    if(file_exists($class)){
        require_once($class); 
    }
  }
}
class tbitem {

    function view(array &$request, array &$response, array &$app) {
//        $filename = ROOT_PATH . CTX()->app_name . "/item.xml";
//        $xml = file_get_contents($filename);
        
        $shop_code = $request['shop_code'];
        $param['item_id'] = $request['item_id'];
        $mod = new TbItemModel($shop_code);

        $xml = $mod->get_update_schema($param);
        
        
       // $ret = $mod->save_update_schema($param) 
        require_lib('tb_xml');
        $xmlobj = new tb_xml();
        $return = array();
        $xmlobj->xml2array($xml, $return);
        $response  = load_model("api/item/TbTemplateModel")->create_html($return);
 

    }
    function add(array &$request, array &$response, array &$app) {
        $shop_code = $request['shop_code'];
        $param['category_id'] = isset($request['cid'])?$request['cid']:0;
        $mod = new TbItemModel($shop_code);

        $xml = $mod->get_add_schema($param);
        require_lib('tb_xml');
        $xmlobj = new tb_xml();
        $return = array();
        $xmlobj->xml2array($xml, $return);
        $response  = load_model("api/item/TbTemplateModel")->create_html($return);


    }
    function update_item(array &$request, array &$response, array &$app) {
        $shop_code = $request['shop_code'];
        $param['category_id'] = isset($request['cid'])?$request['cid']:0;
        $mod = new TbItemModel($shop_code);

        $xml = $mod->get_add_schema($param);
        require_lib('tb_xml');
        $xmlobj = new tb_xml();
        $return = array();
        $xmlobj->xml2array($xml, $return);
 
          
         load_model("api/item/TbItemDataModel")->update_item($request,$return);

        $param['xml'] = $xmlobj->array2xml($return);
        $param['name'] = $request['title']; 
        
      
  
        
    }
        function up2(array &$request, array &$response, array &$app) {
            
            $shop_code = 'shopping_attb';
            $mod = new TbItemModel($shop_code);
            $param = array('item_id'=>'43741665495');
            $data =   $mod->get_increment_update_schema($param);
            var_dump($data);
        }
    
    
    function up(array &$request, array &$response, array &$app) {
        $shop_code = 'shopping_attb';
        $mod = new TbItemModel($shop_code);
        $param = array();
        $param['item_id'] = '43741665495';
        $xml = $mod->get_update_schema($param);
        $schemaReader = new SchemaReader();
        $return = $schemaReader->readXmlForMap($xml);
        $return['descForPC']->setValue('纯色风衣年中大特卖促销活动，便宜实惠');
          $schemaWriter = new SchemaWriter();
       $param['xml'] = $schemaWriter->writeRuleXml($return);
        
   
        $ret = $mod->save_update_schema($param);
        
        
        
        var_dump($ret);
    }
        function ad_new(array &$request, array &$response, array &$app) {
        $shop_code = 'shopping_attb';
        $mod = new TbItemModel($shop_code);
         $param['category_id'] = '';

        $data =   $mod->add_item($param);
        
        
        var_dump($ret);
    }
    
        function add_item(array &$request, array &$response, array &$app) {

        $shop_code = $request['shop_code'];
        $param['category_id'] = isset($request['cid'])?$request['cid']:0;
        $mod = new TbItemModel($shop_code);

    //   $schemaReader = new SchemaReader();
        $xml = $mod->get_add_schema($param);
        require_lib('tb_xml');
        $xmlobj = new tb_xml();
        $return = array();
        $xmlobj->xml2array($xml, $return);
   //$return = $schemaReader->readXmlForMap($xml);
 //fixed
        
       // $return['dealType']->setValue('fixed');
   //     $return['dealType']->setDefaultValue('fixed');
       
        
       $itemParam = load_model("api/item/TbItemDataModel")->update_item($request,$return);
//     load_model("api/item/TbItemDataModel")->update_item_top($request,$return);
//         $schemaWriter = new SchemaWriter();
//         $param['xml'] = $schemaWriter->writeRuleXml($return);
//         

        $param['xml'] = $xmlobj->array2xml($itemParam,'itemParam');
        $param['name'] = $request['title']; 
      
        $data =   $mod->add_item($param);
  
        var_dump($data);die;
    }
    

    function get_user_category(array &$request, array &$response, array &$app) {


              $shop_code = $request['shop_code'];
        $param['parent_cid'] = isset($request['cid'])?$request['cid']:0;
        $mod = new TbItemModel($shop_code);

        $ret = $mod->get_itemcats($param);

        var_dump($ret);
        die;
    }
    function get_img_all(array &$request, array &$response, array &$app) {


        $shop_code = $request['shop_code'];
        $page = isset($request['page'])?$request['page']:1;
        
        $param = array('current_page'=>$page);
       // $param['parent_cid'] = isset($request['cid'])?$request['cid']:0;
        $mod = new TbItemModel($shop_code);

        $response = $mod->get_pictures($param);

    }
    function rxml(array &$request, array &$response, array &$app) {
        $filename = ROOT_PATH . CTX()->app_name . "/item.xml";
        $xml = file_get_contents($filename);
        require_lib('tb_xml');
        $xmlobj = new tb_xml();
        $return = array();
        $xmlobj->xml2array($xml, $return);
        var_dump($return);
        die;
//      //  $d = array2xml($return['itemRule'], 'itemRule');
//  $d = $xmlobj->get_tb_xml($return);
//        
        //   var_dump($d);die;

        die;
    }

    function t(array &$request, array &$response, array &$app) {
        $shop_code = 'tb021';

        $mod = new TbItemModel($shop_code);
        $param['item_id'] = '43672930724';
        $ret = $mod->get_update_schema($param);
        var_dump($ret);
        die;
    }

    function t2(array &$request, array &$response, array &$app) {
        $param['shop_code'] = 'tb024';
        $ret = load_model("api/item/TbItemModel")->get_user_category($param);
        var_dump($ret);
        die;
    }

    function t3(array &$request, array &$response, array &$app) {
        $param['shop_code'] = 'tb024';
        $ret = load_model("api/item/TbItemModel")->get_user_category($param);
        var_dump($ret);
        die;
    }

}
