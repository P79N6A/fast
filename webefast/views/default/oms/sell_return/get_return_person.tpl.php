<?php


if ($app['scene'] == 'edit') {
    $addr_select = '<select name="return_country" id="return_country">

	                <option value ="">国家</option>';
    $list = oms_tb_all('api_taobao_area', array('type' => '1'));
    foreach ($list as $k => $v) {
        $addr_select .= '<option value="' . $v['id'] . '">' . $v['name'] . '</option>';
    }
    $addr_select .='</select><select name="return_province" id="return_province"><option>省</option></select>
	            <select name="return_city" id="return_city">
	                <option>市</option>
	            </select>
	            <select name="return_district" id="return_district">
	                <option>区</option>
	            </select>
	            <select name="return_street" id="return_street">
	                <option>街道</option>
	            </select><input id="return_addr" name="return_addr" type="text" value="' . $response['data']['return_person']['return_addr'] . '">';
}else{
    safe_return_data($response['data']['return_person'],1);
}
$buyer_name = "<span>" . $response['data']['return_person']['buyer_name'] . "</span>";
$wangwang_html = '';
if ($response['data']['return_person']['sale_channel_code'] == 'taobao') {
    $wangwang_html = '<span class="wangwang"><span class="ww-light ww-small"><a href="javascript:launch_ww(' . "'{$response['data']['return_person']['sell_return_code']}'" . ')" class="ww-inline ww-online" title="点此可以直接和买家交流。"><span>旺旺在线</span></a></span></span>';
} else {
    $wangwang_html = '';
}
render_control('FormTable', 'return_person_form', array(
    'conf' => array(
        'fields' => array(
	          array('title' => '买家昵称', 'type' => 'html', 'field' => 'buyer_name', 'html' => $buyer_name . $wangwang_html),
                array('title' => '邮编', 'type' => 'input', 'field' => 'return_zip_code'),
            array('title' => '退货人', 'type' => 'input', 'field' => 'return_name'),
            array('title' => '地址', 'type' => 'html', 'field' => 'return_address', 'html' => ($app['scene'] == 'edit') ? $addr_select : $response['data']['return_person']['return_address']),
            array('title' => '手机', 'type' => 'input', 'field' => 'return_mobile'),
            array('title' => '电话', 'type' => 'input', 'field' => 'return_phone'),
        ),
        'hidden_fields' => array(
            array('field' => 'sell_return_code', 'value' => $response['data']['sell_return_code']),
        ),
    ),
    'act_edit' => 'oms/sell_return/do_edit&app_fmt=json',
    'col' => 2,
    'per' => '0.3',
    'buttons' => array(),
    'data' => $response['data']['return_person'],
));
?>

<?php if ($app['scene'] == 'edit') { ?>
    <script type="text/javascript">
        $(function () {
            var url = '<?php echo get_app_url('base/store/get_area'); ?>';
            $('#return_country').change(function () {
                var parent_id = $(this).val();
                my_areaChange('return', parent_id, 0, url);
            });
            $('#return_province').change(function () {
                var parent_id = $(this).val();
                my_areaChange('return', parent_id, 1, url);
            });
            $('#return_city').change(function () {
                var parent_id = $(this).val();
                my_areaChange('return', parent_id, 2, url);
            });
            $('#return_district').change(function () {
                var parent_id = $(this).val();
                my_areaChange('return', parent_id, 3, url);
            });

            $("#return_country").val("<?php echo $response['data']['return_person']['return_country']; ?>");
            my_areaChange('return', $("#return_country").val(), 0, url, function () {
                $("#return_province").val("<?php echo $response['data']['return_person']['return_province']; ?>");
                my_areaChange('return', $("#return_province").val(), 1, url, function () {
                    $('#return_city').val("<?php echo $response['data']['return_person']['return_city']; ?>");
                    my_areaChange('return', $("#return_city").val(), 2, url, function () {
                        $('#return_district').val("<?php echo $response['data']['return_person']['return_district']; ?>");
                        my_areaChange('return', $("#return_district").val(), 3, url, function () {
                            $('#return_street').val("<?php echo $response['data']['return_person']['return_street']; ?>");
                        });
                    });
                });
            });
        });
    </script>
<?php } ?>
<script>
function launch_ww(record_code){
    var url = "?app_act=oms/sell_record/link_wangwang&type=1&record_code="+record_code;
    window.open(url);
}
</script>