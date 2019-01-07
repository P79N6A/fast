<html>
	<style>
	div{
	font-size:12px;
	}
	</style>
    <body>
        <?php $count = count($response['data']['detail']);
        $detail = array_values($response['data']['detail']);?>
        <?php $i = 0;
        while($i<$count) {?>
        <?php $val = $detail[$i];
        $page_num  = 0;
            if($i%8==0 ){
                   $page_num =   ($i/8)+1;
            } 
        ?>
        
            <?php if($page_num>0):?>
        
  
            <div style="width:95%;height:17px;padding-top: 2px;">
                <div style=" text-align:left">采购单号:&nbsp;&nbsp; <?php echo $response['data']['record']['record_code'];?>  
                &nbsp;&nbsp;&nbsp;&nbsp;页码：&nbsp;&nbsp;<?php echo $page_num ;?>
                </div>
            </div>
        <?php  endif;?>
        
        <div style="width:270px;height:185px;float:left;margin-left:8px;border:1px solid black;margin-top:8px;"><!---->
       
                <table style="width:100%;height:100%;" border="0" cellpadding="0" cellspacing="0">			
			<tr>
				<td  style='width:140px;'> 
                                    <div style="height:130px;"><img src="<?php echo $val['goods_img']; ?>"  style="width:130px;height:110px;" /></div>
                                        <div>商品编码:<?php echo $val['goods_code'];?></div>
                                           <div>商品编码:<?php echo $val['barcode'];?></div>
                                        
				</td>
				<td >
					<div style="height: 20px;"><?php echo $val['id'];?></div>
                                        <div style="height: 20px;">货品描述:</div>
                                         <div style="height: 70px;">
                                        <?php if(empty($val['goods_desc'])){
                                            echo "空";
                                        }else{
                                            echo $val['goods_desc'];
                                        }?>
                                        </div>
                                       <div>数量:<?php echo $val['num'];?></div>
				</td>
			</tr>
			
	
		</table>
       
        </div>
   
          <?php $i++; } ?>
            
  
    </body>
    <script>
document.domain = 'baotayun.com';
</script>
</html>
