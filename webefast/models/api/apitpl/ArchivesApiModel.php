<?php

require_lang('api');

/**
 * 系统档案接口定义类
 * 档案类接口必须继承此类
 * 通过继承此类实现档案的创建、获取、更新
 * @author WMH
 */
interface ArchivesApiModel {

    /**
     * 档案创建
     * @param array $param 外部输入参数
     */
    public function api_archives_create($param);

    /**
     * 档案查询
     * @param array $param 外部输入参数
     */
    public function api_archives_get($param);

    /**
     * 档案更新
     * @param array $param 外部输入参数
     */
    public function api_archives_update($param);
}
