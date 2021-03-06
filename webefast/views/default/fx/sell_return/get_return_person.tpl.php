<?php
if ($app['scene'] == 'edit'){
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
}

render_control('FormTable', 'return_person_form', array(
    'conf' => array(
        'fields' => array(
            array('title' => '买家昵称', 'type' => 'label', 'field' => 'buyer_name'),
            array('title' => 'Email', 'type' => 'input', 'field' => 'return_email'), 
                   
            array('title' => '退货人', 'type' => 'input', 'field' => 'return_name'),
            array('title' => '邮编', 'type' => 'input', 'field' => 'return_zip_code'),

        	array('title' => '地址', 'type' => 'html', 'field' => 'return_address', 'html' => ($app['scene'] == 'edit') ? $addr_select : $response['data']['return_person']['return_address']),

            array('title' => '手机', 'type' => 'input', 'field' => 'return_mobile'),
            array('title' => '电话', 'type' => 'input', 'field' => 'return_phone'),
            array('title' => '&nbsp;', 'type' => '', 'field' => ''),
        ),
        'hidden_fields' => array(
            array('field' => 'sell_return_code','value'=>$response['data']['sell_return_code']),
        ),
    ),
    'act_edit'=>'fx/sell_return/do_edit&app_fmt=json',
    'col' => 2,
    'per' => '0.3',
    'buttons' => array(),
    'data' => $response['data']['return_person'],
));
?>

<?php if ($app['scene'] == 'edit'){ ?>
<script type="text/javascript">
<!--
	$(document).ready(function(){
        var url = '<?php echo get_app_url('base/store/get_area');?>';
        $('#return_country').change(function(){
            var parent_id = $(this).val();
            my_areaChange('return',parent_id,0,url);
        });
        $('#return_province').change(function(){
            var parent_id = $(this).val();
            my_areaChange('return',parent_id,1,url);
        });
        $('#return_city').change(function(){
            var parent_id = $(this).val();
            my_areaChange('return',parent_id, 2, url);
        });
        $('#return_district').change(function(){
            var parent_id = $(this).val();
            my_areaChange('return',parent_id, 3, url);
        });

        $("#return_country").val("<?php echo $response['data']['return_person']['return_country'];?>");
        my_areaChange('return',$("#return_country").val(),0,url,function(){
            $("#return_province").val("<?php echo $response['data']['return_person']['return_province'];?>");
            my_areaChange('return',$("#return_province").val(),1,url,function(){
                $('#return_city').val("<?php echo $response['data']['return_person']['return_city'];?>");
                my_areaChange('return',$("#return_city").val(),2,url,function(){
                    $('#return_district').val("<?php echo $response['data']['return_person']['return_district'];?>");
                    my_areaChange('return',$("#return_district").val(),3,url,function(){
                        $('#return_street').val("<?php echo $response['data']['return_person']['return_street'];?>");
                    });
                });
            });
        })

    })
//-->
</script>
<?php } ?>