<?php

/**
 * 淘宝商品发布模版数据处理
 * @author wq
 * @modify wmh
 */
class TbTemplateModel {

    private $opt_type = 'add';
    private $data_group = array();
    private $child_data = array();
    private $base_list = array();
    private $item_list = array();
    private $sku_list = array();

    /**
     * 获取更新规则数据
     * @param array $tb_arr 淘宝规则数据
     * @param string $opt_type edit:编辑，add:添加
     * @return array 数据集
     */
    public function get_data($tb_arr, $opt_type) {
        $this->opt_type = $opt_type;
        foreach ($tb_arr['itemRule']['field'] as $val) {
            $method = $val['@attributes']['type'];
            if (method_exists($this, $method)) {
                $this->$method($val);
            }
        }

        if ($opt_type == 'edit') {
            return array('data_group' => $this->data_group);
        }

        return array(
            'base_list' => $this->base_list,
            'sku_list' => $this->sku_list,
            'item_list' => $this->item_list,
        );
    }

    private function input(&$field) {
        $id = &$field['@attributes']['id'];
        $title = &$field['@attributes']['name'];
        $default_value = isset($field['default-value']) ? $field['default-value'] : '';
        if ($this->opt_type == 'edit') {
            if (!empty($default_value)) {
//                if (strpos($id, 'in_prop') === 0) {
//                    $id = substr($id, 3);
//                }
                $this->data_group[$id] = $default_value;
            }
            return;
        }

        if (isset($field['rules']['rule'])) {
            $rule = $this->check_rule($field['rules']['rule']);
        }

        if (isset($field['rules']['rule'][0]['depend-group']['depend-express']['@attributes']['fieldId'])) {
            return ''; //暂时不处理
        }

        $rule['required'] = isset($rule['required']) ? $rule['required'] : NULL;
        $data = array('title' => $title, 'value' => $default_value, 'option' => array(), 'type' => 'input', 'rule' => $rule['required']);

        if (strpos($id, 'prop') === 0) {
            $this->item_list[$id] = $data;
        } else {
            $this->base_list[$id] = $data;
        }

        return array('id' => $id, 'title' => $title, 'rule' => $rule['required']);
    }

    private function singleCheck(&$field) {
        $id = &$field['@attributes']['id'];
        $title = &$field['@attributes']['name'];
        $default_value = isset($field['default-value']) ? $field['default-value'] : '';
        if ($this->opt_type == 'edit') {
            if (!empty($default_value)) {
                $this->data_group[$id] = $default_value;
            }
            return;
        }

        $rule = $this->check_rule($field['rules']['rule']);
        $rule['required'] = isset($rule['required']) ? $rule['required'] : FALSE;
        $options = &$field['options']['option'];
        if (isset($options['@attribute'])) {
            $options = array($options);
        }


        if (isset($rule['child'])) {
            $this->child_data[$rule['child']['pid']][$rule['child']['pid']] = array(
                'id' => $id,
                'type' => 'select',
                'option' => $options,
                'value' => $default_value,
            );
            //子元素直接返回，JS初始化
            return '';
        }

        $option_arr = array();
        foreach ($options as $val) {
            $option_arr[] = $val['@attributes'];
        }

        $rule['required'] = isset($rule['required']) ? $rule['required'] : NULL;
        $data = array('title' => $title, 'value' => $default_value, 'option' => $option_arr, 'type' => 'singleCheck', 'rule' => $rule['required']);

        if (strpos($id, 'prop') === 0) {
            $this->item_list[$id] = $data;
        } else {
            $this->base_list[$id] = $data;
        }

        return array('id' => $id, 'title' => $title, 'rule' => $rule['required']);
    }

    private function multiCheck(&$field, $type = 'item') {
        $title = &$field['@attributes']['name'];
        $id = &$field['@attributes']['id'];
        $default_value = array();
        if (isset($field['default-values']['default-value'])) {
            $default_value = is_array($field['default-values']['default-value']) ? $field['default-values']['default-value'] : array($field['default-values']['default-value']);
        }

        if ($this->opt_type == 'edit') {
            if (!empty($default_value)) {
                $this->data_group[$id] = $default_value;
            }
            return;
        }

        $rule = $this->check_rule($field['rules']['rule']);
        $options = &$field['options']['option'];
        if (isset($options['@attribute'])) {
            $options = array($options);
        }

        if (isset($rule['child'])) {
            $this->child_data[$rule['child']['pid']][$rule['child']['pid']] = array(
                'id' => $id,
                'type' => 'checkbox',
                'option' => $options,
                'value' => $default_value,
            );
            //子元素直接返回，JS初始化
            return '';
        }
        $list_data = array();

        $option_arr = array();
        foreach ($options as $val) {
            $option_arr[] = $val['@attributes'];
            $list_data[$val['@attributes']['value']] = $val['@attributes']['displayName'];
        }

        $rule['required'] = isset($rule['required']) ? $rule['required'] : NULL;
        $data = array('title' => $title, 'value' => $default_value, 'option' => $option_arr, 'type' => 'multiCheck', 'rule' => $rule['required']);
        if ($type != 'sku') {
            if (strpos($id, 'prop') === 0) {
                $this->item_list[$id] = $data;
            } else {
                $this->base_list[$id] = $data;
            }
        }
        return array('id' => $id, 'title' => $title, 'rule' => $rule['required'], 'list_data' => $list_data);
    }

    private function multiComplex(&$field) {
        $id = &$field['@attributes']['id'];
        if ($id == 'sku') {
            $this->set_sku($field);
        }
    }

    private function set_sku(&$field) {
        $default_data = isset($field['default-values']['default-complex-values']['field']) ? array($field['default-values']['default-complex-values']['field']) : $field['default-values']['default-complex-values'];

        foreach ($default_data as $val) {
            $sku_info = array();
            foreach ($val['field'] as $v) {
                $sku_info[$v['@attributes']['id']] = $v['value'];
            }
            $this->data_group['sku'][] = $sku_info;
        }
        if ($this->opt_type == 'edit') {
            return;
        }

        $fields = $field["fields"];

        $spec_select = array();

        foreach ($fields['field'] as $val) {
            $id = $val['@attributes']['id'];
            if ($val['@attributes']['type'] == 'singleCheck') {
                $spec_data = $this->multiCheck($val, 'sku');
                $spec_select[$id] = array_unique($spec_data);
            } else if ($val['@attributes']['type'] != 'singleCheck') {
                $this->sku_list['attr'][$id]['name'] = $val['@attributes']['name'];
            }
        }
        $this->sku_list['spec_list'] = $spec_select;
    }

    private function complex(&$field) {
        if ($this->opt_type != 'edit') {
            return;
        }
        $id = &$field['@attributes']['id'];
        if ($id == 'location') {
            $this->set_location($field);
        }
        if ($id == 'item_images') {
            $this->set_item_images($field);
        }
    }

    private function set_item_images($field) {
        $default_data = isset($field['default-complex-values']) ? $field['default-complex-values'] : array();
        $img_arr = array();
        foreach ($default_data as $def) {
            if (!empty($def['value'])) {
                $img_arr[$def['@attributes']['id']] = $def['value'];
            }
        }
        if ($this->opt_type == 'edit') {
            if (!empty($img_arr)) {
                $this->data_group['item_iamges'] = $img_arr;
            }
            return;
        }

        foreach ($field['fields']['field'] as $val) {
            $id = $val['@attributes']['id'];
            $title = $val['@attributes']['name'];
            $d_value = isset($img_arr[$id]) ? $img_arr[$id] : '';
            $data = array('title' => $title, 'value' => $d_value, 'option' => array(), 'type' => 'input', 'rule' => true);
            $this->base_list['item_imagess'][$id] = $data;
        }
    }

    private function set_location($field) {
        $default_value = array();
        foreach ($field['default-complex-values']['field'] as $val) {
            $default_value[$val['@attributes']['id']] = $val['value'];
        }

        if ($this->opt_type == 'edit') {
            if (!empty($default_value)) {
                $this->data_group = array_merge($this->data_group, $default_value);
            }
            return;
        }

        foreach ($field['fields']['field'] as $val) {
            $id = $val['@attributes']['id'];
            $title = $val['@attributes']['name'];
            $d_value = isset($default_value[$id]) ? $default_value[$id] : '';
            $data = array('title' => $title, 'value' => $d_value, 'option' => array(), 'type' => 'input', 'rule' => true);
            $this->base_list[$id] = $data;
        }
    }

    private function check_rule(&$rule) {
        $rule_arr = array();

        if (isset($rule['@attributes'])) {
            $rule = array($rule);
        }
        foreach ($rule as $r) {
            if ($r['@attributes']['name'] == 'requiredRule' && $r['@attributes']['value'] == 'true') {
                $rule_arr['required'] = true;
            } else if ($r['@attributes']['name'] == 'maxLengthRule') {
                $rule_arr['maxlength'] = $r['@attributes']['value'];
            } else if (isset($r['depend-group']['depend-express']['@attributes'])) {
                $rule_arr['child'] = array(
                    'pid' => $r['depend-group']['depend-express']['@attributes']['fieldId'],
                    'pvalue' => $r['depend-group']['depend-express']['@attributes']['value'],
                    'symbol' => $r['depend-group']['depend-express']['@attributes']['symbol']
                );
            }
        }
        return $rule_arr;
    }

}
