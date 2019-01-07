<style>
    #grid{width:790px;}
    #inner-grid{padding-left: 5px;}
</style>
<div class="demo-content">
    <div class="row">
        <div class="span16">
            <div id="grid">
            </div>
        </div>
    </div>
</div>
<div class="button button-primary close-widow" style="position:absolute;left:50%;top:90%">关闭</div>
<script type="text/javascript">
    BUI.use(['bui/grid','bui/data'],function(Grid,Data){
        var Grid = Grid,
            Store = Data.Store,
            columns = [{
                title : '平台交易号',
                dataIndex :'a',
                width:150
            },{
                title : '店铺',
                dataIndex :'b',
                width:100
            },{
                title : '卖家昵称',
                dataIndex : 'c',
                width:120
            },{
                title : '商品类别数',
                dataIndex : 'd',
                width:80
            },{
                title : '商品数量',
                dataIndex : 'e',
                width:50
            },{
                title : '金额',
                dataIndex : 'f',
                width:80
            },{
                title : '信息',
                dataIndex : 'g',
                width:150
            }],
            columns_detail = [{
                title : '商品编码',
                dataIndex :'a',
                width:80
            },{
                title : '商品名称',
                dataIndex :'b',
                width:200
            },{
                title : '商品条形码',
                dataIndex : 'c',
                width:100
            },{
                title : '数量',
                dataIndex : 'd',
                width:70
            },{
                title : '金额',
                dataIndex : 'e',
                width:70
            },{
                title : '赠品',
                dataIndex : 'f',
                width:70
            }],
            data = <?php echo $response["data"]["data"];?>;
        // 实例化 Grid.Plugins.Cascade 插件
        var cascade = new Grid.Plugins.Cascade({
                renderer : function(record){
                    return '<div class="inner-grid"></div>';	//生成简单表格的容器
                }
            }),
            //简单表格的配置信息
            simpleGridConfig = {
                autoRender:true,                 //自动生成
                columns:BUI.cloneObject(columns_detail)//这里为了简单起见，复制了配置信息，应用中需要自己配置
            };

        var store = new Store({
                data : data,
                autoLoad:true
            }),
            grid = new Grid.Grid({
                render:'#grid',
                columns : columns,
                store: store,
                plugins: [cascade]	// Grid.Plugins.Cascade 插件
            });

        grid.render();

        cascade.on('expand',function(ev){
            var data = ev.record,
                row = ev.row,
                sgrid = $(row).data('sub-grid');
            if(!sgrid){
                var container = $(row).find('.inner-grid'),
                    gridConfig = BUI.cloneObject(simpleGridConfig);
                gridConfig.render = container;

                sgrid = new Grid.SimpleGrid(gridConfig);
                sgrid.showData(data.records);
                $(row).data('sub-grid',sgrid);
            }
        });

        cascade.on('removed',function(ev){
            var data = ev.record,
                row = ev.row,
                sgrid = $(row).data('sub-grid');
            if(sgrid){
                sgrid.destroy();
            }
        });
        $('body').on('click','#btnExpand',function(){
            cascade.expandAll();
            $('#btnExpand').parent().addClass('bui-grid-cascade-expand');
            $('#btnExpand').parent().html('<i class="bui-grid-cascade-icon" id="btnCollapse"></i>');
        })
        $('body').on('click','#btnCollapse',function(){
            cascade.collapseAll();
            $('#btnCollapse').parent().removeClass('bui-grid-cascade-expand');
            $('#btnCollapse').parent().html('<i class="bui-grid-cascade-icon" id="btnExpand"></i>');
        })
        $('body').css('position','absolute')
    });
    $('th.bui-grid-hd:nth-child(1) > div:nth-child(1) > span:nth-child(1)').html('<i class="bui-grid-cascade-icon" id="btnExpand"></i>');
    $('.close-widow').click(function(){
        ui_closePopWindow("<?php echo $request['ES_frmId'] ?>")
    })
</script>
