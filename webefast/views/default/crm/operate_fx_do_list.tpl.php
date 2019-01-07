<?php echo load_js('comm_util.js') ?>
<style type="text/css">
 .ac-tooltip{
    position:absolute;
    visibility:hidden;
    border : 1px solid #efefef;
    background-color: white;
    opacity: .8;
    padding: 5px;
 
    transition: top 200ms,left 200ms;
    -moz-transition:  top 200ms,left 200ms;  /* Firefox 4 */
    -webkit-transition:  top 200ms,left 200ms; /* Safari 和 Chrome */
    -o-transition:  top 200ms,left 200ms;
  }
 
  .ac-tooltip .ac-title{
    margin: 0;
    padding: 0;
  }
 
  .ac-tooltip .ac-list{
    margin: 0;
    padding: 0;
    list-style: none;
  }
  .ac-tooltip li{
    line-height:  18px;
  }


.tab-div table {
	float: left;
	background: #eee;
	border-collapse: collapse;
	margin-left: 10px;
}
.tab-div td {
	text-align: left;
	background: #fff;
	padding: 5px;
	border-collapse: collapse;
	border: 1px solid #c8c8c8;
}
.tab-div td.label1 {
	text-align: right;
}
.title1 {
	background-color: #C4D5E7;
}
.detail-section{ min-width:1080px;}
.detail-section .canvas{ width:33%; float:left; margin-bottom:55px; text-align:center;}
.table_wrap{ width:33%; min-width:340px; float:left; clear:right; height:350px; margin-bottom:55px;}
#sort {
	width:100%;
	margin-top: 22px;
	margin-bottom:8px;
	font-size: 14px;
	color: #666;
}
#sort td.sort_btn {
	border: 1px solid #dddddd;
	padding: 2px 0;
	text-align: center;
	cursor: pointer;
	position:relative;
}
#sort td.sort_btn .icon{ display:block; width:10px; height:4px; background:url(assets/images/sort_icon.png) no-repeat; position:absolute; left:50%; margin-left:-5px; bottom:-5px; display:none;}
#sort td.sort_btn.curr{
	background-color:#1695ca;color:#fff;
	border-color:#1695ca;
	}
#sort td.sort_btn.curr .icon{ display:block;}

.table_wrap .rxph{ width:100%; border-collapse:collapse; text-align:center; display:none;}
.table_wrap .rxph th{ border:1px solid #ddd; color:#666; background:#f5f5f5; padding:2px 0; min-width:60px;}
.table_wrap .rxph td.label1{ border:1px solid #ddd; color:#666; padding:1px 0;}
.table_wrap .legend{ text-align:center; padding-top:18px; color:#A7A7A7; font-size:12px;}
</style>
<div class="detail-section">
  <div id="canvas_0" class="canvas">
  </div>
  <div id="canvas_1" class="canvas">
  </div>
  <div class="table_wrap">
      <table id="sort" >
        <tr>
          
          <td class="sort_btn curr">热销排行（按销售额）<i class="icon"></i></td>
          <td class="sort_btn">热销排行（按销量）<i class="icon"></i></td>
        </tr>
      </table>
      <table id="xsje" class="rxph" style="display:inline-table;">
        <tr>
          <th>商品名称</th>
          <th>条码</th>
          <th>销售金额</th>
          </tr>
          <?php foreach ($response['sale_rank']['sale_money'] as $sale_row) {?>
        <tr>
          <td class="label1" title="<?php echo $sale_row['goods_name']; ?>"><?php echo mb_substr( $sale_row['goods_name'], 0, 30); ?></td>
          <td class="label1"><?php echo $sale_row['goods_barcode']; ?></td>
          <td class="label1"><?php echo $sale_row['sale_money']; ?></td>
        </tr>
        <?php }?>
      </table>
      <table id="xssl" class="rxph">
        <tr>
          <th>商品名称</th>
          <th>条码</th>
          <th>数量</th>
          </tr>
          <?php foreach ($response['sale_rank']['sale_count'] as $sale_row) {?>
        <tr>
          <td class="label1" title="<?php echo $sale_row['goods_name']; ?>"><?php echo mb_substr( $sale_row['goods_name'], 0, 30); ?></td>
          <td class="label1"><?php echo $sale_row['goods_barcode']; ?></td>
          <td class="label1"><?php echo $sale_row['sale_count']; ?></td>
        </tr>
        <?php }?>
      </table>
      <p class="legend">近七天热销排行</p>
    </div>
  <div id="canvas_2" class="canvas">
  </div>
   <div id="canvas_3" class="canvas">
  </div>
  <div id="canvas_4" class="canvas">
  </div>
  
 
</div>
<script type="text/javascript">
    $("#sort").find(".sort_btn").each(function(index, element) {
        $(this).click(function(){
			$(this).addClass("curr").siblings().removeClass("curr");
			$(".table_wrap").find(".rxph").eq(index).show().siblings(".rxph").hide();
			})
    });
    </script> 
<script src="http://g.tbcdn.cn/bui/acharts/1.0.29/acharts-min.js"></script> 
<script type="text/javascript">
      <?php foreach ($response['chart'] as $key=>$chart_row) {
      	?>
      	var id= 'canvas_'+<?php echo $key;?>;
     
        var chart = new AChart({
            theme : AChart.Theme.SmoothBase,
            id : id,
            width : 365,
            height : 350,
            colors: ['#1695ca'],
            plotCfg : {
              margin : [30,30,50] //画板的边距
            },
            xAxis : {
            	categories :<?php echo $response['date'];?>
            },
			seriesOptions : { //设置多个序列共同的属性
            lineCfg : { //不同类型的图对应不同的共用属性，lineCfg,areaCfg,columnCfg等，type + Cfg 标示
              smooth : true,
              "line": {
                  "stroke-width": 1,
                  "stroke-linejoin": "round",
                 "stroke-linecap": "round"
             },
			 "lineActived": {
                    "stroke-width": 1,
					"stroke-linejoin": "round",
                 "stroke-linecap": "round"
                },
              labels : { //标示显示文本
                label : { //文本样式
                  y : -17
                },
                //渲染文本
                renderer : function(value,item){ //通过item修改属性
                    item.fill = '#666';
                    item['font-weight'] = 'normal';
                    item['font-size'] = 12;
                  return value;
                }
              }
            }
          },
          
            tooltip : {
              
              valueSuffix : 'Point',
              shared : true, //是否多个数据序列共同显示信息
           custom : true, //自定义tooltip
              crosshairs : false ,//是否出现基准线
              itemTpl : '{value}'
            },
            /* tooltip: {
            	formatter: function() {
            	return this.y;
            	}
            	},*/
            series : [{
                name: '<?php echo $chart_row['name'];?>',
                data: <?php echo $chart_row['data'];?>
            }]
        });
     
        chart.render();
        <?php } ?>
    </script> 
