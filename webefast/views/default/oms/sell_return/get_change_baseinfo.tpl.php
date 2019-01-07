<?php

$change_express_default = '';
if ($app['scene'] == 'edit') {
    $addr_select = '<select name="change_country" id="change_country">
	                <option value ="">国家</option>';
    $list = oms_tb_all('api_taobao_area', array('type' => '1'));
    foreach ($list as $k => $v) {
        $addr_select .= '<option value="' . $v['id'] . '">' . $v['name'] . '</option>';
    }
    $addr_select .='</select><select name="change_province" id="change_province"><option>省</option></select>
	            <select name="change_city" id="change_city">
	                <option>市</option>
	            </select>
	            <select name="change_district" id="change_district">
	                <option>区</option>
	            </select>
	            <select name="change_street" id="change_street">
	                <option>街道</option>
	            </select><input id="change_addr" name="change_addr" type="text" value="' . $response['data']['change_baseinfo']['change_addr'] . '">';
    if (empty($response['data']['change_baseinfo']['change_express_code'])) {
        $change_express_default = $response['data']['change_baseinfo']['relation_record_express_code'];
    } else {
        $change_express_default = $response['data']['change_baseinfo']['change_express_code'];
    }
}else{
    safe_return_data($response['data']['change_baseinfo'],1);
}

render_control('FormTable', 'change_baseinfo_form', array(
    'conf' => array(
        'fields' => array(
            array('title' => '换货单号', 'type' => 'html', 'field' => 'change_record', 'html' => "<a href=\"javascript:openPage('订单详情','?app_act=oms/sell_record/view&sell_record_code={$response['data']['change_baseinfo']['change_record']}','订单详情')\">{$response['data']['change_baseinfo']['change_record']}</a>"),
            array('title' => '快递公司', 'type' => 'select', 'field' => 'change_express_code', 'data' => ds_get_select('shipping'), 'active' => $change_express_default),
            array('title' => '收货人', 'type' => 'input', 'field' => 'change_name'),
            array('title' => '手机', 'type' => 'input', 'field' => 'change_mobile'),
            array('title' => '收货地址', 'type' => 'html', 'field' => 'change_address', 'html' => ($app['scene'] == 'edit') ? $addr_select : $response['data']['change_baseinfo']['change_address']),
            array('title' => '联系电话', 'type' => 'input', 'field' => 'change_phone'),
            array('title' => '换货仓库', 'type' => 'select', 'field' => 'change_store_code', 'data' => load_model('base/StoreModel')->get_purview_store()),
            array('title' => '', 'type' => '', 'field' => '', 'data' => ''),
        ),
        'hidden_fields' => array(
            array('field' => 'sell_return_code', 'value' => $response['data']['sell_return_code']),
        ),
    ),
    'act_edit' => 'oms/sell_return/do_edit&app_fmt=json',
    'col' => 2,
    'buttons' => array(),
    'per' => '0.3',
    'data' => $response['data']['change_baseinfo'],
));
?>

<?php if ($app['scene'] == 'edit') : ?>
    <script type="text/javascript">
        $(function () {
            var url = '<?php echo get_app_url('base/store/get_area'); ?>';
            $('#change_country').change(function () {
                var parent_id = $(this).val();
                my_areaChange('change', parent_id, 0, url);
            });
            $('#change_province').change(function () {
                var parent_id = $(this).val();
                my_areaChange('change', parent_id, 1, url);
            });
            $('#change_city').change(function () {
                var parent_id = $(this).val();
                my_areaChange('change', parent_id, 2, url);
            });
            $('#change_district').change(function () {
                var parent_id = $(this).val();
                my_areaChange('change', parent_id, 3, url);
            });

            $("#change_country").val("<?php echo $response['data']['change_baseinfo']['change_country']; ?>");
            my_areaChange('change', $("#change_country").val(), 0, url, function () {
                $("#change_province").val("<?php echo $response['data']['change_baseinfo']['change_province']; ?>");
                my_areaChange('change', $("#change_province").val(), 1, url, function () {
                    $('#change_city').val("<?php echo $response['data']['change_baseinfo']['change_city']; ?>");
                    my_areaChange('change', $("#change_city").val(), 2, url, function () {
                        $('#change_district').val("<?php echo $response['data']['change_baseinfo']['change_district']; ?>");
                        my_areaChange('change', $("#change_district").val(), 3, url, function () {
                            $('#change_street').val("<?php echo $response['data']['change_baseinfo']['change_street']; ?>");
                        });
                    });
                });
            });
        });
    </script>
<?php endif; ?>
