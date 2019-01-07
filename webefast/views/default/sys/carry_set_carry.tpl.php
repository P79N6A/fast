<div class="page-header1" style="margin-top: 10px;">
    <span class="page-title">
        <h2>库存维护</h2>
    </span>
</div>
<div class="clear"></div>
<hr>
<div class="row form-actions actions-bar">
    <div class="span24 offset3 " style="text-align:center">			
        上次结转月份			   
        <input type="text" id="start_time" disabled="disabled"  name="start_time"  value="<?php echo $response['start_date']; ?>" />

    </div>
    <div class="span24 offset3 " style="text-align:center">			
        本次结转月份		   
        <input type="text" id="end_time"  name="end_time"  <?php
        if (!empty($response['data']['data'])) {
            echo 'value="' . date('Y-m', strtotime($response['data']['data']['end_date'])) . '"  disabled="disabled" ';
        } else {
            echo 'value=""';
        }
        ?> />

    </div>	

</div>
<div class="row span24" style="text-align:center">
    <?php if (empty($response['data']['data'])): ?>
        <button type="button" class="button button-success" value="生存结转任务" id="create"><i class="icon-plus-sign icon-white"></i> 生存结转任务</button>
    <?php endif ?>

</div>
<div class="row" style="text-align:center">
    <div class="span18" id="message">
        <?php if (!empty($response['data']['data'])): ?>
            <?php echo $response['data']['data']['state_name']; ?>...
        <?php endif ?>    


    </div>
</div>
<script type="text/javascript">
    $(function() {

        BUI.use('bui/calendar', function(Calendar) {
            var inputEl = $('#end_time'),
                    monthpicker = new BUI.Calendar.MonthPicker({
                        trigger: inputEl,
                        // month:1, //月份从0开始，11结束
                        autoHide: true,
                        align: {
                            points: ['bl', 'tl']
                        },
                        //year:2000,
                        success: function() {
                            var month = this.get('month'),
                                    year = this.get('year');
                            inputEl.val(year + '-' + (month + 1));//月份从0开始，11结束
                            this.hide();
                        }
                    });
            monthpicker.render();
            monthpicker.on('show', function(ev) {
                var val = inputEl.val(),
                        arr, month, year;
                if (val) {
                    arr = val.split('-'); //分割年月
                    year = parseInt(arr[0]);
                    month = parseInt(arr[1]);
                    monthpicker.set('year', year);
                    monthpicker.set('month', month - 1);
                }
            });
        });
        $('#create').click(function() {
            var param = {};
            param.end_time = $('#end_time').val();
            var url = "?app_act=sys/carry/do_set_carry&app_fmt=json";
            $('#create').attr('disabled', true);
            $.post(url, param, function(ret) {
                if (ret.status > 0) {
                    BUI.Message.Alert('任务生成,结转准备中...');
                } else {
                    BUI.Message.Alert(ret.message);
                }
            }, 'json');

        });
    });
</script>