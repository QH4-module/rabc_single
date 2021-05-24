<?php
/**
 * File Name: PrivilegeModel.php
 * ©2020 All right reserved Qiaotongtianxia Network Technology Co., Ltd.
 * @author: hyunsu
 * @date: 2021/5/21 3:56 下午
 * @email: hyunsu@foxmail.com
 * @description:
 * @version: 1.0.0
 * ============================= 版本修正历史记录 ==========================
 * 版 本:          修改时间:          修改人:
 * 修改内容:
 *      //
 */

namespace qh4module\rabc_single\models\privilege;


use qh4module\rabc_single\external\ExtRabcSingle;
use qttx\web\ServiceModel;

/**
 * Class PrivilegeModel
 * @package qh4module\rabc_single\models\privilege
 * @property ExtRabcSingle $external
 */
class PrivilegeModel extends ServiceModel
{
    /**
     * @inheritDoc
     */
    public function rules()
    {
        return $this->mergeRules([
            [['id', 'parent_id', 'create_by'], 'string', ['max' => 64]],
            [['id_path', 'key_path'], 'string', ['max' => 1000]],
            [['key', 'name', 'desc', 'path'], 'string', ['max' => 200]],
            [['type', 'level', 'create_time', 'sort', 'del_time'], 'integer'],
            [['icon'], 'string', ['max' => 100]],
            [['type'], 'in', 'range' => [1, 2]],
        ], $this->external->rules());
    }

    /**
     * @inheritDoc
     */
    public function attributeLangs()
    {
        return $this->mergeLanguages([
            'id' => 'ID',
            'parent_id' => '上级',
            'id_path' => '所有父级ID',
            'key' => '唯一标记',
            'key_path' => '所有父级标记',
            'name' => '名称',
            'desc' => '说明',
            'type' => '类型 ',
            'level' => '级别',
            'icon' => '图标',
            'path' => '关联路由',
            'create_time' => '创建时间',
            'create_by' => '创建人',
            'sort' => '排序'
        ], $this->external->attributeLangs());
    }
}