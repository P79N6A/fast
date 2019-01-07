<?php echo load_js("baison.js",true);?>
<script>
function down(){
	ajax_post({
        url: "?app_act=sys/task/get_express",
        async:false,
        alert:false,
        callback:function(data){
            if(data.status == "1"){
                alert("下载成功");
                refresh();
            }
        }
    })
}
</script>
<?php
render_control('PageHead', 'head1', array('title' => '物流',
    'links' => array(
        array('type' => 'js', 'title' => '下载', 'js' => "down()"),
    ),
));
?>
<?php
render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'id' => 'btn-search'
    ),
    'fields' => array(
        array(
            'label' => '关键词',
            'title' => '关键词',
            'type' => 'input',
            'id' => 'keyword'
        ),
    )
));
?>

<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
	        array(
                'type' => 'text',
                'show' => 1,
                'title' => '代码',
                'field' => 'code',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '名称',
                'field' => 'name',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '正则',
                'field' => 'reg',
                'width' => '400',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'api/taobao/ExpressModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'api_express_id',
));
?>