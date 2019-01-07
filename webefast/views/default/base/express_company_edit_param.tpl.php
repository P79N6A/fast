<form  class="form-horizontal" id="form1" action="?app_act=base/express_company/do_edit_param&app_fmt=json" method="post">


    <div class="row" style="height:30px;line-height:30px;">
        <input id="company_code" name="company_code" value="<?php echo $request['company_code']; ?>" type="hidden" />

        <?php foreach ($response['data'] as $key => $val): ?>      
            <div class="control-group span16">

                <label class="control-label span3" style="width: 60px;"><?php echo $val['name']; ?>：</label>
                <div class="controls">
                    <label></label>
                    <input  class="control-text" name="<?php echo $key; ?>" id="<?php echo $key; ?>" value="<?php echo $val['val']; ?>" type="text"  ><label> </label>
                </div>

            </div>
        <?php endforeach; ?>
    </div>
        <div class="row" style="height:50px;">
    <div class="span13 offset3 " style="">
                <button type="submit" class="button button-primary" id="submit">提交</button>
                <button type="reset" class="button " id="reset">重置</button>
            </div>
          </div>      
</form>

<script type="text/javascript">
     var form;
//$(function() {       
     form =  new BUI.Form.HForm({
                srcNode : '#form1',
                submitType : 'ajax',
                callback : function(data){
				 var type = data.status == 1 ? 'success' : 'error';
                        
                            BUI.Message.Alert(data.message, function() {  ui_closePopWindow('<?php echo $request['ES_frmId'] ;?>'); }, type);
                     
                       
						                }
        }).render();
     //});
    </script>