<?php
/**
 * File Name: TraitRabcSingleController.php
 * ©2020 All right reserved Qiaotongtianxia Network Technology Co., Ltd.
 * @author: hyunsu
 * @date: 2021/5/21 10:39 上午
 * @email: hyunsu@foxmail.com
 * @description:
 * @version: 1.0.0
 * ============================= 版本修正历史记录 ==========================
 * 版 本:          修改时间:          修改人:
 * 修改内容:
 *      //
 */

namespace qh4module\rabc_single;


use qh4module\rabc_single\external\ExtRabcSingle;
use qh4module\rabc_single\models\privilege\CascaderData as PrivilegeCascaderData;
use qh4module\rabc_single\models\privilege\Create as PrivCreate;
use qh4module\rabc_single\models\privilege\Delete as PrivDelete;
use qh4module\rabc_single\models\privilege\Detail as PrivDetail;
use qh4module\rabc_single\models\privilege\Index as PrivIndex;
use qh4module\rabc_single\models\privilege\MainMenu;
use qh4module\rabc_single\models\privilege\ParsePrivilegeYml;
use qh4module\rabc_single\models\privilege\PrivilegeKeys;
use qh4module\rabc_single\models\privilege\Update as PrivUpdate;
use qh4module\rabc_single\models\role\CascaderData as RoleCascaderData;
use qh4module\rabc_single\models\role\Create as RoleCreate;
use qh4module\rabc_single\models\role\Delete as RoleDelete;
use qh4module\rabc_single\models\role\Detail as RoleDetail;
use qh4module\rabc_single\models\role\Index as RoleIndex;
use qh4module\rabc_single\models\role\Update as RoleUpdate;
use qh4module\rabc_single\models\user\Create as UserCreate;
use qh4module\rabc_single\models\user\Delete as UserDelete;
use qh4module\rabc_single\models\user\Detail as UserDetail;
use qh4module\rabc_single\models\user\Index as UserIndex;
use qh4module\rabc_single\models\user\Update as UserUpdate;

trait TraitRabcSingleController
{
    /**
     * @return ExtRabcSingle
     */
    public function ext_rabc_single()
    {
        return new ExtRabcSingle();
    }

    /**
     * 解析权限 yml 文件
     * 文件需要放在 lib 目录下
     * @return array|mixed
     */
    public function actionParseYml()
    {
        if (!ENV_DEV) {
            \QTTX::$response->setStatusCode(404);
            return false;
        }

        $model = new ParsePrivilegeYml();

        return $this->runModel($model);
    }

    /**
     * 获取主菜单数据
     * @return array
     */
    public function actionMainMenu()
    {
        $model = new MainMenu([
            'external' => $this->ext_rabc_single(),
        ]);

        return $this->runModel($model);
    }

    /**
     * 获取用户所有权限的key,前端主要依赖这些值判定权限
     */
    public function actionPrivilegeKeys()
    {
        $model = new PrivilegeKeys([
            'external' => $this->ext_rabc_single(),
        ]);

        return $this->runModel($model);
    }


    /**
     * 获取权限的级联数据树
     * @return array
     */
    public function actionPrivCascaderData()
    {
        $model = new PrivilegeCascaderData([
            'external' => $this->ext_rabc_single(),
        ]);

        return $this->runModel($model);
    }

    /**
     * 获取角色的级联数据树
     * @return array
     */
    public function actionRoleCascaderData()
    {
        $model = new RoleCascaderData([
            'external' => $this->ext_rabc_single(),
        ]);

        return $this->runModel($model);
    }

    /**
     * 获取权限资源列表,返回数据树
     * @return array
     */
    public function actionPrivilegeIndex()
    {
        $model = new PrivIndex([
            'external' => $this->ext_rabc_single(),
        ]);

        return $this->runModel($model);
    }

    /**
     * 新增一条权限资源
     * @return array
     */
    public function actionPrivilegeCreate()
    {
        $model = new PrivCreate([
            'external' => $this->ext_rabc_single(),
        ]);

        return $this->runModel($model);
    }

    /**
     * 获取权限资源详情
     * @return array
     */
    public function actionPrivilegeDetail()
    {
        $model = new PrivDetail([
            'external' => $this->ext_rabc_single(),
        ]);

        return $this->runModel($model);
    }

    /**
     * 更新权限资源
     * @return array
     */
    public function actionPrivilegeUpdate()
    {
        $model = new PrivUpdate([
            'external' => $this->ext_rabc_single(),
        ]);

        return $this->runModel($model);
    }

    /**
     * 批量删除权限资源
     * @return array
     */
    public function actionPrivilegeDelete()
    {
        $model = new PrivDelete([
            'external' => $this->ext_rabc_single(),
        ]);

        return $this->runModel($model);
    }


    /**
     * 获取角色列表,返回数据树
     * @return array
     */
    public function actionRoleIndex()
    {
        $model = new RoleIndex([
            'external' => $this->ext_rabc_single(),
        ]);

        return $this->runModel($model);
    }

    /**
     * 新增一个角色
     * @return array
     */
    public function actionRoleCreate()
    {
        $model = new RoleCreate([
            'external' => $this->ext_rabc_single(),
        ]);

        return $this->runModel($model);
    }

    /**
     * 更新角色
     * @return array
     */
    public function actionRoleUpdate()
    {
        $model = new RoleUpdate([
            'external' => $this->ext_rabc_single(),
        ]);

        return $this->runModel($model);
    }

    /**
     * 获取角色详情
     * @return array
     */
    public function actionRoleDetail()
    {
        $model = new RoleDetail([
            'external' => $this->ext_rabc_single(),
        ]);

        return $this->runModel($model);
    }

    /**
     * 批量删除角色
     * @return array
     */
    public function actionRoleDelete()
    {
        $model = new RoleDelete([
            'external' => $this->ext_rabc_single(),
        ]);

        return $this->runModel($model);
    }


    /**
     * 分页获取用户数据
     * @return array [total,list,page,limit]
     */
    public function actionUserIndex()
    {
        $model = new UserIndex([
            'external' => $this->ext_rabc_single(),
        ]);

        return $this->runModel($model);
    }

    /**
     * 新增一个用户
     * @return array
     */
    public function actionUserCreate()
    {
        $model = new UserCreate([
            'external' => $this->ext_rabc_single(),
        ]);

        return $this->runModel($model);
    }

    /**
     * 更新用户信息
     * @return array
     */
    public function actionUserUpdate()
    {
        $model = new UserUpdate([
            'external' => $this->ext_rabc_single(),
        ]);

        return $this->runModel($model);
    }

    /**
     * 获取用户详情
     * @return array
     */
    public function actionUserDetail()
    {
        $model = new UserDetail([
            'external' => $this->ext_rabc_single(),
        ]);

        return $this->runModel($model);
    }

    /**
     * 批量删除用户
     * @return array
     */
    public function actionUserDelete()
    {
        $model = new UserDelete([
            'external' => $this->ext_rabc_single(),
        ]);

        return $this->runModel($model);
    }
}