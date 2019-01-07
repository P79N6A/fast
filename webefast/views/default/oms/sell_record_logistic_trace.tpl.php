<?php if ($response['kdniao_enable'] == 1): ?>
    <?php if ($response['status'] != 1): ?>
        <div class="clearfix" style="text-align: left;">
            <span><?php echo $response['message']; ?></span>
        </div>
        <?php
    endif;
    ?>
    <?php foreach ($response['data'] as $val): ?>
        <div class="clearfix" style="text-align: left;">
            <span>快递单号：<?php echo $val['No']; ?>，物流轨迹：</span>
            <?php
            if ($val['Success'] != 1) {
                echo "<span>{$val['Reason']}</span>";
                continue;
            }
            ?>
        </div>
        <table cellspacing="0" class="table table-bordered">
            <tr>
                <th align="center">时间</th>
                <th align="center">物流信息</th>

            </tr>
            <?php foreach ($val['Traces'] as $trace) { ?>
                <tr>
                    <td><?php echo $trace['AcceptTime']; ?></td>
                    <td><?php echo $trace['AcceptStation']; ?></td>

                </tr>
            <?php } ?>
        </table>
    <?php endforeach; ?>
<?php else: ?>
    <div class="clearfix" style="text-align: left;">
        <span><?php echo $response['tid']; ?>物流跟踪查询</span>
    </div>
    <table cellspacing="0" class="table table-bordered">
        <tr>
            <th align="center">时间</th>
            <th align="center">物流信息</th>
        </tr>
        <?php foreach ($response['trace_list']['transit_step_info'] as $step_info) { ?>
            <tr>
                <td><?php echo $step_info['status_time']; ?></td>
                <td><?php echo $step_info['status_desc']; ?></td>
            </tr>
        <?php } ?>
    </table>
<?php endif; ?>