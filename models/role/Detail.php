<?php
/**
 * File Name: Detail.php
 * ©2020 All right reserved Qiaotongtianxia Network Technology Co., Ltd.
 * @author: hyunsu
 * @date: 2021/5/21 2:29 下午
 * @email: hyunsu@foxmail.com
 * @description:
 * @version: 1.0.0
 * ============================= 版本修正历史记录 ==========================
 * 版 本:          修改时间:          修改人:
 * 修改内容:
 *      //
 */

namespace qh4module\rabc_single\models\role;


use qh4module\rabc_single\HpRabcSingle;

class Detail extends RoleModel
{
    /**
     * @var string|int 接收参数,必须：主键
     */
    public $id;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['id'],'required'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        // 所有的字段,根据需要删减
        $fields = ['`ta`.`id`','`ta`.`parent_id`','`ta`.`id_path`','`ta`.`name`','`ta`.`desc`','`ta`.`create_by`',
            '`ta`.`create_time`','`ta`.`is_fixed`',
            'tb.nick_name as create_by_name',
        ];
        $tb_user_info = $this->external->userInfoTableName();
        $tb_role = $this->external->roleTableName();

        $result = $this->external->getDb()
            ->select($fields)
            ->from("$tb_role as ta")
            ->leftJoin("$tb_user_info as tb", 'ta.create_by=tb.user_id')
            ->whereArray(['id' => $this->id])
            ->row();
        if (empty($result)) {
            $this->addError('id', '查询条目不存在');
            return false;
        }

        // 获取关联角色
        $result['privilege_ids'] = HpRabcSingle::getRoleRelatedPrivileges($this->id, [
            'id' => 'value',
            'name' => 'label'
        ], $this->external);

        return $result;
    }
}