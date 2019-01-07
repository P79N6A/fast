<?php render_control('PageHead', 'head1',
array('title'=>'产品补丁升级',
	'links'=>array(
	),
	'ref_table'=>'table'  //ref_table 表示是否刷新父页面
));?>
<?php render_control( 'SearchForm', 'searchForm', array(
   'buttons' =>array( 
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type'=>'submit'
        ),
    ),
    'fields' => array (
        /*array (
            'label' => '产品',
            'title' => '产品',
            'type' => 'select',
            'id' => 'product',
            'data'=>ds_get_select('chanpin',1)
        ),
        array (
            'label' => '版本',
            'title' => '版本',
            'type' => 'select',
            'id' => 'version_no',
            'data'=>array(array('0'=>'','请选择')),
        ),
        array (
            'label' => '补丁',
            'title' => '补丁',
            'type' => 'select',
            'id' => 'version_patch',
            'data'=>array(array('0'=>'','请选择')),
        ),*/
        array (
            'label' => '绑定客户',
            'title' => '是否绑定客户',
            'type' => 'select',
            'id' => 'is_bindkh', 
            'data'=>ds_get_select_by_field('boolstatus'),
        ),
        array (
            'label' => '客户',
            'title' => '客户名称/客户代码',
            'type' => 'input',
            'id' => 'kehu', 
        ),
    ) 
));
?>
<div class="row">
    <div class="span24">
        <b>所属产品</b>
        <select name="product" id="product" class="input-normal">
            <option value=''>请选择</option>
            <?php 
                $retchanpin=ds_get_select('chanpin');
                foreach ($retchanpin as $chanpin){
                    echo '<option value='.$chanpin['cp_id'].'>'.$chanpin['cp_name'].'</option>';
                }
            ?>
        </select>
        <b style="margin-left: 10px">产品版本</b>
        <select name="version_no" id="version_no" class="input-normal">
            <option value=''>请选择</option>
        </select>
        <b style="margin-left: 10px">产品版本补丁</b>
        <select name="version_patch" id="version_patch" class="input-normal">
            <option value=''>请选择</option>
        </select>
        <button style="margin-left: 10px" class="button button-success" id="upgrade_but">批量升级</button>
    </div>
</div>
<hr/>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '数据库名称',
                'field' => 'rem_db_name',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'checkbox',
                'show' => 1,
                'title' => '绑定客户',
                'field' => 'rem_db_is_bindkh',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '客户名称',
                'field' => 'rem_db_khid_name',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '版本号',
                'field' => 'rem_db_version_code',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '当前补丁',
                'field' => 'rem_db_version_patch',
                'width' => '200',
                'align' => ''
            ),
            array (
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '150',
                'align' => '',
                'buttons' => array (),
            )
        )
    ),
    'dataset' => 'patch/PatchModel::get_by_page',
    'queryBy' => 'searchForm',
    'params' => array('filter' => array()),
    'idField' => 'rem_db_id',
    'CheckSelection' => true,
));
?>
<script type="text/javascript">
    $(function(){
        
        $("#upgrade_but").click(function() {
            if($("#version_patch").val()==""){
                BUI.Message.Alert('请选择补丁','warning');
                return;
            }
            var itemlist=tableGrid.getSelection();
            if(itemlist.length!=0){
                var dbs = [];
                BUI.each(itemlist,function(item){
		    dbs.push(item.rem_db_id);
                });
                alert(dbs);
                $.ajax({type: 'POST', dataType: 'json',
                     url: '<?php echo get_app_url('patch/patch/do_upgrade_all'); ?>',
                     data: {dbs: dbs,version_patch:$("#version_patch").val(),version_no:$("#version_no").val()},
                     success: function(ret) {
                         var type = ret.status == 1 ? 'success' : 'error';
                         if (type == 'success') {
                             BUI.Message.Alert(ret.message, type);
                             tableStore.load();
                         } else {
                             BUI.Message.Alert(ret.message, type);
                         }
                     }
                 }); 
            }else{
                BUI.Message.Alert('请选择数据库','warning');
                return;
            }
        });
        
        //产品选择事件
        $("#product").change(function(){
            var product=$(this).val();
            if(product==""){
                //清空产品版本和产品补丁列表
                $("#version_no").empty();
                $("#version_no").append("<option value=''>请选择</option>");
                $("#version_patch").empty();
                $("#version_patch").append("<option value=''>请选择</option>");
                return;
            }
            //获取产品版本
            $.ajax({type: 'POST', dataType: 'json',
                url: "<?php echo get_app_url('patch/patch/get_product_version'); ?>",
                data: {cpid: product,},
                success: function(ret) {
                    var type = ret.status == 1 ? 'success' : 'error';
                    if (type == 'success') {
                        $("#version_no").empty();
                        //重新绑定产品版本字段
                        $("#version_no").append("<option value=''>请选择</option>");
                        $.each(ret.data, function(i,item) {
                            $("#version_no").append("<option value='" + item.pv_id + "'>" + item.pv_bh + "</option>");
                        });
                        $("#version_no").change(function() {
                            //bind_version_patch();
                            if ($("#version_no").val() == "") {
                                $("#version_patch").empty();
                                $("#version_patch").append("<option value=''>请选择</option>");
                                return;
                            }
                            $.ajax({type: 'POST', dataType: 'json',
                                url: "<?php echo get_app_url('patch/patch/get_version_patch'); ?>",
                                data: {cpid: product,ver_no: $("#version_no").find("option:selected").text(),},
                                success: function(ret) {
                                    var type = ret.status == 1 ? 'success' : 'error';
                                    if (type == 'success') {
                                        $("#version_patch").empty();
                                        $("#version_patch").append("<option value=''>请选择</option>");
                                        $.each(ret.data, function(i,item) {
                                            $("#version_patch").append("<option value='" + item.id + "'>" + item.version_patch + "</option>");
                                        });
                                    } else {
                                        $("#version_patch").empty();
                                        $("#version_patch").append("<option value=''>请选择</option>");
                                    }
                                }
                            });
                        });
                    } else {
                        $("#version_no").empty();
                        $("#version_no").append("<option value=''>请选择</option>");
                    }
                }
            });
        });
    });
</script>