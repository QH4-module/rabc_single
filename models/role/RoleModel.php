<?php
/**
 * File Name: RoleModel.php
 * ©2020 All right reserved Qiaotongtianxia Network Technology Co., Ltd.
 * @author: hyunsu
 * @date: 2021/5/21 1:52 下午
 * @email: hyunsu@foxmail.com
 * @description:
 * @version: 1.0.0
 * ============================= 版本修正历史记录 ==========================
 * 版 本:          修改时间:          修改人:
 * 修改内容:
 *      //
 */

namespace qh4module\rabc_single\models\role;


use qh4module\rabc_single\external\ExtRabcSingle;
use qttx\web\ServiceModel;

/**
 * Class RoleModel
 * @package qh4module\rabc_single\models\role
 * @property ExtRabcSingle $external
 */
class RoleModel extends ServiceModel
{
    /**
     * @inheritDoc
     */
    public function rules()
    {
        return $this->mergeRules([
            [['id', 'parent_id', 'create_by'], 'string', ['max' => 64]],
            [['name'], 'string', ['max' => 45]],
            [['desc'], 'string', ['max' => 200]],
            [['create_time', 'is_fixed', 'del_time'], 'integer']
        ], $this->external->rules());
    }

    /**
     * @inheritDoc
     */
    public function attributeLangs()
    {
        return $this->mergeLanguages([
            'id' => 'ID',
            'parent_id' => '上级角色',
            'name' => '名称',
            'desc' => '说明',
            'create_by' => '创建人',
            'create_time' => '创建时间',
        ], $this->external->attributeLangs());
    }
}