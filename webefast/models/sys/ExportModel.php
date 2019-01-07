<?php

/**
 * 导出生成文件类
 *
 * @author dfr
 */
require_model('tb/TbModel');
require_model('common/TaskModel');

class ExportModel extends TbModel {

    protected $export_dir = '';

    function __construct() {
        parent::__construct();
        $this->export_dir = ROOT_PATH . CTX()->app_name . "/temp/export/";
    }

    function get_status($task_id) {
        $task = new TaskModel();
        return $task->get_task_status($task_id);
    }

    function create_task($filter) {

        $task = new TaskModel();
        $data = array();
        //$filter['ctl_export_file_type'] = 'execl';
        unset($filter['ctl_conf']);
        $file_name = $this->create_csv_name($filter);
        $filter['app_act'] = "ctl/index/do_index";
        $filter['app_fmt'] = "json";
        $filter['app_ctl'] = "DataTable/do_get_data";
        if ($filter['ctl_export_file_type'] == 'csv') {
            $filter['ctl_export_file_name'] = $this->get_csv_file_path($file_name);
        } else {
            $filter['ctl_export_file_name'] = $this->get_xlsx_file_path($file_name);
        }



        $filter['__t_user_code'] = load_model('sys/UserTaskModel')->get_user_code();
        $data['request'] = $filter;
        $data['task_type'] = 0;
        $data['is_auto'] = 1;
        $data['code'] = 'export_' . $file_name;
        $data['plan_exec_ip'] = $task->ip;

        $ret = $task->save_task($data);

        $ret_data['task_id'] = $ret['data'];
        $ret_data['file_key'] = $file_name;
        $ret_data['export_name'] = $filter['ctl_export_name'];
        return $this->format_ret(1, $ret_data);
    }

    function downlaod_csv($file_name, $export_name) {
        $path = $this->get_csv_file_path($file_name);
        header("Content-type:application/vnd.ms-excel;charset=utf8");
        header("Content-Disposition:attachment; filename=" . iconv('utf-8', 'gbk', $export_name) . ".csv");
        echo file_get_contents($path);
        die();
    }

    function download_execl($file_name, $export_name) {
        $path = $this->get_xlsx_file_path($file_name);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        //  header("Content-type:application/vnd.ms-excel;charset=utf8");
        $export_name_str = iconv('utf-8', 'gbk', $export_name) . '.xlsx';
        header('Content-Disposition: attachment;filename="' . $export_name_str . '"');
        header('Cache-Control: max-age=0');
        echo file_get_contents($path);
        die();
    }

    function get_xlsx_file_path($file_name) {
        return $this->export_dir . $file_name . ".xlsx";
    }

    function get_csv_file_path($file_name) {
        return $this->export_dir . $file_name . ".csv";
    }

    function create_csv_name($filter) {
        $file_name = md5(json_encode($filter) . time());
        return $file_name;
    }

}

?>
