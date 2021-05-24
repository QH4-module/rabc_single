<?php
/**
 * File Name: Index.php
 * ©2020 All right reserved Qiaotongtianxia Network Technology Co., Ltd.
 * @author: hyunsu
 * @date: 2021/5/21 1:51 下午
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
use qh4module\token\TokenFilter;
use qttx\helper\ArrayHelper;

/**
 * Class Index
 * @package qh4module\rabc_single\models\role
 */
class Index extends RoleModel
{
    /**
     * @var string 接收参数，筛选字段：ID
     */
    public $id;

    /**
     * @var string 接收参数,筛选字段,上级id
     */
    public $parent_id;

    /**
     * @var string 接收参数，筛选字段：名称
     */
    public $name;


    /**
     * @inheritDoc
     */
    public function run()
    {
        // 所有的字段,根据列表显示进行删减
        $fields = ['`ta`.`id`', '`ta`.`parent_id`', '`ta`.`name`', '`ta`.`desc`', '`ta`.`create_by`',
            '`ta`.`create_time`', '`ta`.`is_fixed`',
            'tb.nick_name as create_by_name',
        ];

        // 构建基础查询
        $tb_user_info = $this->external->userInfoTableName();
        $tb_role = $this->external->roleTableName();
        $user_id = TokenFilter::getPayload('user_id');

        $db = $this->external->getDb();
        $sql = $db
            ->select($fields)
            ->from("$tb_role as ta")
            ->leftJoin("$tb_user_info as tb", 'ta.create_by=tb.user_id');

        // 非管理员只显示自己相关角色
        if (!HpRabcSingle::is_administrator($user_id, $this->external)) {
            list($role_ids, $child_ids) = HpRabcSingle::getUserRelationAllRoles($user_id, $this->external);
            $sql->whereIn('id', array_merge($role_ids, $child_ids));
        }

        // 不展示固定的
        $sql->where('is_fixed=0');

        // 追加筛选条件
        if ($this->id) {
            $sql->where('`ta`.`id`= :id690')
                ->bindValue('id690', $this->id);
        }
        if ($this->parent_id) {
            $sql->where('`ta`.`parent_id`= :pid692')
                ->bindValue('pid692', $this->parent_id);
        }
        if ($this->name) {
            $sql->where('`ta`.`name` like :name974')
                ->bindValue('name974', "%{$this->name}%");
        }

        $result = $sql
            ->where('`ta`.`del_time`= :del_time920')
            ->bindValue('del_time920', 0)
            ->query();

        $data = ArrayHelper::formatTree($result, 1);

        return array(
            'total' => sizeof($data),
            'list' => $data,
            'page' => 1,
            'limit' => 10
        );
    }


    protected function formatResult($result_role, $result_relation)
    {
        $tree = array();
        $temp = array();

        foreach ($result_role as $item) {
            $temp[$item['id']] = $item;
        }

        foreach ($result_relation as $item) {
            if (!isset($temp[$item['role_id']])) {
                // 如果是搜索部分,可能存在没有这个角色,直接略过
                continue;
            }
            if ($item['parent_id'] != 1 && isset($temp[$item['parent_id']])) {
                $temp[$item['parent_id']]['children'][] = &$temp[$item['role_id']];
            } else {
                $tree[] = &$temp[$item['role_id']];
            }
        }
        unset($temp);

        return $tree;
    }
}