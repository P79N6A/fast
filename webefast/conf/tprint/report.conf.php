<?php

return array(
//    
//             <div id="report_top" class="group dev_boder" title="报表头"></div>
//              <div id="report_table_body" class="group dev_boder" title="表格"></div>
//              <div id="report_table_bottom" class="group dev_boder"title="表格尾"></div>
//              <div id="report_bottom" class="group dev_boder" title="报表尾"></div>
    
     "init_style"=>array(
         'width'=>'191mm',
         'height'=>'191mm',
         'css'=>array('tprint_report'),
     ),
     'init_group'=>array(
      'report_top'=> array('title'=>'报表头'),
      'report_table_body'=> 
         array('title'=>'表格',
                 'child'=>array(
                  'type'=>'table',
                   'id'=>'detail',
                   'colume_num'=>5,
                 )
          ),
      'report_table_bottom'=> array('title'=>'表格尾'),
      'report_bottom'=> array('title'=>'报表尾'),
     ),


    
);