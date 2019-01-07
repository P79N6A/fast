
<?php if ($response['status'] == 1): ?>
    <?php if ($request['order_process'] == 1): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>时间</th>
                    <th>状态</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($response['data'] as $data): ?>
                    <tr>
                        <td><?php echo $data['operate_time']; ?></td>
                        <td><?php echo $data['process_status']; ?></td>
                    </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>时间</th>
                    <th>状态</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($response['data'] as $data):?>
                    <tr>
                    <?php  switch ($request['wms_system_code']) {
                                case 'jdwms':
                                    echo "<td>" . $data['operateTime'] . "</td>";
                                    echo "<td>" . $data['soStatusName'] . "</td>";
                                    break;
                                default :
                                    echo "<td>" . $data['OpDate'] . "</td>";
                                    echo "<td>" . $data['Description'] . "</td>";
                                    break;
                    }?>
                    </tr>
                <?php endforeach;?>
            </tbody>
        </table>
    <?php endif; ?>
<?php else: ?>
    <div><?php echo $response['message']; ?></div>
<?php endif; ?>