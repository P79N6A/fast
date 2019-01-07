<?php

/**
 * Description of TbItemDataModel
 *
 * @author wq
 */
use Top\schema\value\ComplexValue;

class TbItemDataModel {

    private $data;
    private $request;
    private $field_data = array();

    function update_item(&$request, &$data) {
        $this->request = $request;
        foreach ($data['itemRule']['field'] as $key => &$val) {
            $method = $val['@attributes']['type'];
            $id = $val['@attributes']['id'];

            if (( method_exists($this, $method) && isset($request[$id]) && !empty($request[$id]) ) || $method == 'complex') {
                $this->$method($val, $request[$id]);
            }
        }
        return array('field' => $this->field_data);
    }

    function update_item_top(&$request, &$data) {
        $this->request = $request;
        $id_arr = array('item_images', 'location', 'sku');
        foreach ($data as $id => &$val) {
            if (in_array($id, $id_arr)) {
                if ($id == 'location') {
                    $location_cv = new ComplexValue();
                    $val->getField('prov')->setValue('浙江');
                    $location_cv->put($val->getField('prov'));
                    $val->getField('city')->setValue('杭州');
                    $location_cv->put($val->getField('city'));
                    $val->setComplexValue($location_cv);
                }
                if ($id == 'item_images') {
                    $image_cv = new ComplexValue();
                    $img_src = str_replace('http://img.alicdn.com/imgextra/', '', $request['item_image_0']);
                    $val->getField('item_image_0')->setValue($img_src);
                    $image_cv->put($val->getField('item_image_0'));
                    $val->setDefaultComplexValue($image_cv);

                    // $val['city']->setDefaultValue('杭州');
                }
                if ($id == 'sku') {


//                    $sku_data = $request['sku'] ;
//                     $sku_cv  =    new ComplexValue();
//
//                    foreach($sku_data as $key=>$v){
//                        $val->getField($key)->setDefaultValue($v);
//                        $sku_cv->put($val->getField($key));
//                    }
//                    $val->addDefaultComplexValues($sku_cv);
                }
            } else if (isset($request[$id]) && !empty($request[$id])) {
                $val->setDefaultValue($request[$id]);
                //       $val->setValue($request[$id]);
            }
        }
    }

    private function singleCheck(&$field, $param_val) {
        $new_field['@attributes'] = $field['@attributes'];
        $new_field['value'] = $param_val;
        $this->field_data[] = $new_field;
    }

    private function input(&$field, $param_val) {
        $new_field['@attributes'] = $field['@attributes'];
        $new_field['value'] = $param_val;
        $this->field_data[] = $new_field;
    }

    private function multiCheck(&$field, $param_val) {
        $new_field['@attributes'] = $field['@attributes'];
        foreach ($param_val as $val) {
            $new_field['values']['value'][] = $val;
        }
        $this->field_data[] = $new_field;
    }

    private function complex(&$field, $param_val) {
        $new_field['@attributes'] = $field['@attributes'];
        if ($field['@attributes']['id'] == 'item_images') {
            for ($i = 0; $i < 5; $i++) {
                $img_id = 'item_image_' . $i;
                if (isset($this->request[$img_id]) && !empty($this->request[$img_id])) {
                    //http://img.alicdn.com/imgextra/
                    $img_src = str_replace('http://img.alicdn.com/imgextra/', '', $this->request[$img_id]);
                    $new_field['complex-values']['field'][] = array(
                        '@attributes' => array(
                            'id' => $img_id,
                            'type' => 'input',
                        ),
                        'value' => $img_src,
                    );
                }
            }
        }
        if ($field['@attributes']['id'] == 'location') {//设置默认值
            $new_field['complex-values']['field'][] = array(
                '@attributes' => array(
                    'id' => 'city',
                    'type' => 'input',
                ),
                'value' => $param_val['city'],
            );
            $new_field['complex-values']['field'][] = array(
                '@attributes' => array(
                    'id' => 'prov',
                    'type' => 'input',
                ),
                'value' => $param_val['prov'],
            );
        }



        $this->field_data[] = $new_field;
    }

    private function multiComplex(&$field, $param_val) {
        $id = $field['@attributes']['id'];
        if ($id == 'sku') {
            $this->set_sku_val($field, $param_val);
        }
    }

    private function set_sku_val(&$field, $param_val) {
        $new_field['@attributes'] = $field['@attributes'];
        $attr_arr = array();
        foreach ($field['fields']['field'] as $f) {
            unset($f['@attributes']['name']);
            $attr_arr[$f['@attributes']['id']]['@attributes'] = $f['@attributes'];
        }


        foreach ($param_val as $sku_list) {
            $sku_data = array();
            foreach ($sku_list as $key => $val) {
                $sku_val = $attr_arr[$key];
                $sku_val['value'] = $val;
                $sku_data [] = $sku_val;
            }

            $new_field[] = array('complex-values' => array('field' => $sku_data));
        }
        $this->field_data[] = $new_field;
    }

}
