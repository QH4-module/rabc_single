<?php
/**
 * File Name: ExtRabcSingle.php
 * ©2020 All right reserved Qiaotongtianxia Network Technology Co., Ltd.
 * @author: hyunsu
 * @date: 2021/5/21 10:27 上午
 * @email: hyunsu@foxmail.com
 * @description:
 * @version: 1.0.0
 * ============================= 版本修正历史记录 ==========================
 * 版 本:          修改时间:          修改人:
 * 修改内容:
 *      //
 */

namespace qh4module\rabc_single\external;


use qttx\web\External;

class ExtRabcSingle extends External
{
    /**
     * @return string 用户表
     */
    public function userTableName()
    {
        return '{{bk_user}}';
    }

    /**
     * @return string 用户信息表
     */
    public function userInfoTableName()
    {
        return '{{bk_user_info}}';
    }

    /**
     * @return string 角色表
     */
    public function roleTableName()
    {
        return '{{%bk_role}}';
    }

    /**
     * @return string 角色冗余表
     */
    public function roleMoreTableName()
    {
        return '{{%bk_role_more}}';
    }

    /**
     * @return string 权限表
     */
    public function privilegeTableName()
    {
        return '{{%bk_privilege}}';
    }

    /**
     * @return string 角色权限关联表
     */
    public function relRolePrivTableName()
    {
        return '{{%bk_relation_role_privilege}}';
    }

    /**
     * @return string 用户角色关联表
     */
    public function relUserRoleTableName()
    {
        return '{{%bk_relation_user_role}}';
    }

    /**
     * @return string 城市表
     */
    public function cityTableName()
    {
        return '{{%city}}';
    }
}