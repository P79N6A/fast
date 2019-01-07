<div class="upload1">
    
   
    <div class="row form-actions actions-bar">
		<div class="span13 offset3 ">
		<div id="J_Uploader" style="display:none;" >
                    </div>
		 店铺  <select name="shop_code" id="shop_code" data-rules="{required : true}">
			       <?php foreach($response['shop'] as $k=>$v){ 
			       	     $row = array_values($v);
			       	?>
			    	<option  value ="<?php echo $row[0]; ?>" ><?php echo $row[1]; ?></option>
			       <?php } ?>
			        </select>
		 <button type="button" class="button button-success" value="新增商品导入" id="btnimport" ><i class="icon-plus-sign icon-white"></i>支付宝收支导入</button>
		<a class="button" target="_blank" href="<?php echo get_excel_url("api_taobao_alipay.csv",1) ?>">模版下载</a>
		</div>
   </div>
</div>
<div class="result1" style="display: block">
            
</div>

<script type="text/javascript">
var  shop_code = $("#shop_code").val();		
var  url = '?app_act=acc/api_taobao_alipay/do_import&app_fmt=json&shop_code='+shop_code; ;	
                    BUI.use(['bui/uploader','bui/overlay'],function (Uploader,Overlay) {
                    	
                        /**
                         * 返回数据的格式
                         *
                         *  默认是 {url : 'url'},否则认为上传失败
                         *  可以通过isSuccess 更改判定成功失败的结构
                         */
                        var uploader = new Uploader.Uploader({
                            //指定使用主题
                            type:'iframe',
                            render: '#J_Uploader',
                            url: url,
                            queue: {
                                resultTpl:{
                                    'error': '<div class="error"><span class="uploader-error">{msg}</span></div>'
                                }
                            },
                            rules: {
                                //文的类型
                                ext: ['.csv','文件类型只能为{0}'],
                                //文件大小的最大值,单位也是kb
                                maxSize: [10240, '文件大小不能大于10M']
                            }
                        }).render();
     
                        var dialog = new Overlay.Dialog({
                           // title:'文件上传',
                            width:300,
                            height:120,
                            closeable : false,
                            bodyContent:'正在导入...',
                            buttons:[],
                            success:function () {
                                this.close();
                            }
                        });
                                 //上传成功时会触发
                       var run = 0;
                      uploader.on('success', function(ev){ 
                         
                        var result = ev.result;
                        var msg = '成功导入:'+ev.result.url;
                        
                            if(result.data!=''){
                               // msg +="<br />失败SKU:"+result.data;
                                msg +="<br />"+result.data;
                            }
                            dialog.set('bodyContent',msg);
                      });
                        $('#btnimport').click(function(){
                        	shop_code = $("#shop_code").val();
                        	
                        	if(shop_code == ''){
                            	alert("请选择店铺");
                        		return;
                            }
                            url = '?app_act=acc/api_taobao_alipay/do_import&app_fmt=json&shop_code='+shop_code;
                            uploader.set('url',url);
                            uploader.get('button').get('fileInput').click();
                           });
                        uploader.on('change',function(){
                            setTimeout(function(){get_status()},1000);
                            dialog.show();
                        });
                      
                        function get_status(){
                           var run = 0
                            var item = uploader.get('queue').getFirstItem();
                          
                            var status = uploader.get('queue').status(item);
                            
                            switch(status){
                                case 'add':
                                    run = 0;
                                    break;
                                case 'wait':
                                    run = 0;    
                                     break;
                                case 'progress':
                                    run = 0;     
                                     break;
                                case 'success':
                                    run = 1; 
                                    //dialog.set('bodyContent','成功导入:'+$('.success').html());
                                     break;
                                case 'cancel':
                                    run = 1; 
                                      dialog.set('bodyContent','导入被取消');
                                       break;
                                case 'error':
                                    run = 1; 
                                    var msg = $('.error').html();
                                    msg = (msg=='')?'导入异常':msg;
                                    dialog.set('bodyContent',msg);
                                     break;
                                 default:
                                      run = 1; 
                                      dialog.set('bodyContent','导入异常');  
                                       break;
                            }
                            
                            if(run==0){
                                dialog.set('bodyContent','正在导入...');
                                setTimeout(function(){get_status()},2000);
                            }else{
                               // $('.bui-queue-item-del').click();
                                dialog.set('buttons',[ {
                                                text:'关闭',
                                                elCls : 'button button-primary',
                                                handler : function(){
                                                   if(status=='success'){
                                                       //location.reload();
                                                       // reload_page();
                                                   }
                                                        dialog.close();
                                                   
                                                }
                                              }]);
                            }
                       
                        }
                    });
          
          
          
   </script>              

