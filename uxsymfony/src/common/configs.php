<?php

/*
 * 网站公共配置文件
 */

namespace App\common;

class configs
{
    static public function getConfigs()
    {
        return [
            'admin_login_path' => $_ENV['ADMIN_LOGIN_PATH'], // 管理员登录路径
            'supplier_login_path' => $_ENV['SUPPLIER_LOGIN_PATH'], // 供应商登录路径
        ];
    }
}