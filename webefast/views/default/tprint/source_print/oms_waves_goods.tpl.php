<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>- 拣货单打印</title>
        <meta name="robots" content="noindex, nofollow">
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body  style="<?php echo "width:210mm;height:297mm;" ?>">
        <?php include get_tpl_path('web_page_top'); ?>
        <?php
        // var_dump($response['data']);die;
        //record
//               $r['goods_data'] = $goods_data;
//        $r['spec1_data'] = $spec1_data;
//        $r['spec2_data'] = $spec2_data;
//        $r['print_data'] = $new_data; 


        $psend_cfg['table_width'] = array(
            'spdm' => "15%", // 商品代码 长度
            'color' => "5%", // 颜色 长度
            'color_name' => "5%", // 颜色名称 长度
            'sl' => "5%", // 数量 长度
            'je' => "5%" // 金额 长度
        );
        $table_head = '';
        /* 拼装表头 */
        foreach ($response['data']['spec2_data'] as $kk => $vv) {


            $table_head.="<th>" . $vv . "</th>";
        }

        $table_head.='<th rowspan="1" scope="col"  width="60px"   style="text-align:center;">数量</th>';
        ?>


        <h1 align="center">
            拣货单

        </h1>
        <table cellpadding="1" width="99%">
            <tbody>
                <tr>
                    <td  style="text-align:right;width:90px;">配送单号：</td>
                    <td style="text-align:left;"><?php echo $response['data']['record']['record_code']; ?></td>
                    <!--td width="31%" rowspan="2" align="left"  ><img title="code" width="120px" height="60px" class="barcode"  src="assets/images/confirm.png" /></td-->
                </tr>
            </tbody>
        </table>


        <table border="1" width="99%" style="border-color: rgb(0, 0, 0); border-collapse: collapse;">
            <tr>
            <!-- <td style="text-align:center;"></td> --> <!-- 商品图片 -->
                <th rowspan="1" scope="col" width="10%" style="text-align:center;">商品货号</th>
                <th rowspan="1" scope="col" width="100px" style="text-align:center;">商品名称</th>
                <th rowspan="1" scope="col" width="80px" style="text-align:center;">颜色</th>
                <!--th rowspan="{$size_head.max_row}" scope="col" width="{$table_width.color_name}" style="text-align:center;">颜色名称</th-->


                <?php echo $table_head; ?>



            </tr>
            <?php
            $all_num = 0;
            //      var_dump($response['data']['print_data'] );
            foreach ($response['data']['print_data'] as $goods_code => $_spec1_val):
                ?>
                <?php foreach ($_spec1_val as $spec1_code => $_spec2_val): ?>
                    <tr>

                        <td style="text-align:center;"><?php echo $goods_code; ?></td>
                        <td style="text-align:center;"><?php echo $response['data']['goods_data'][$goods_code]; ?></td>
                        <td style="text-align:center;"><?php echo $response['data']['spec1_data'][$spec1_code]; ?></td>


                        <?php
                        $row_num = 0;
                        foreach ($response['data']['spec2_data'] as $spec2_code => $spec2_name) {
                            $num = '';
                            if (isset($_spec2_val[$spec2_code])) {
                                $num = $_spec2_val[$spec2_code];
                                $row_num +=$num;
                                $all_num +=$num;
                            }
                            ?>

                            <td style="text-align:center;">
                                <?php echo $num; ?>

                            </td>

                        <?php } ?>

                        <td style="text-align:center;"><?php echo $row_num; ?></td>

                    </tr>


                <?php endforeach; ?>
            <?php endforeach; ?>
            <tr>
                <td style="text-align:center;">&nbsp;</td>
                <td style="text-align:center;">&nbsp;</td>
                      <td style="text-align:center;">&nbsp;</td>
                <?php foreach ($response['data']['spec2_data'] as $spec2_code => $spec2_name) : ?>
                    <td style="text-align:center;">&nbsp;</td>
                <?php endforeach; ?>

                <td style="text-align:center;"><?php echo $all_num; ?></td>

            </tr>

        </table>

        <!-- end -->

        <br />
        <table border="0" width="100%">
            <tbody>
                <tr></tr>
                <tr align="right">
                    <!-- 订单操作员以及订单打印的日期 -->
                    <td>打印时间：<?php echo $response['data']['record']['print_time']; ?>&nbsp;&nbsp;&nbsp;配货人：<?php echo $response['data']['record']['print_name']; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                </tr>
            </tbody>
        </table>

        <p> <style type="text/css">
                BODY {
                    FONT-SIZE: 13px;
                }
                TD {
                    FONT-SIZE: 13px
                }
                .bg {
                    background-repeat: x y;
                    background-position: left top;
                }
                h1{ font:bold 27px; letter-spacing:6px;}

                .ct{ font:bold;} 
            </style></p>
        <p style="page-break-after:always;">&nbsp;</p>
    </body></html>