<?php
/**
 * File Name: Index.php
 * ©2020 All right reserved Qiaotongtianxia Network Technology Co., Ltd.
 * @author: hyunsu
 * @date: 2021/5/21 4:00 下午
 * @email: hyunsu@foxmail.com
 * @description:
 * @version: 1.0.0
 * ============================= 版本修正历史记录 ==========================
 * 版 本:          修改时间:          修改人:
 * 修改内容:
 *      //
 */

namespace qh4module\rabc_single\models\privilege;


use qh4module\rabc_single\HpRabcSingle;
use qh4module\token\TokenFilter;
use qttx\helper\ArrayHelper;

class Index extends PrivilegeModel
{
    /**
     * @var string 接收参数，筛选字段：ID
     */
    public $id;

    /**
     * @var string 接收参数，筛选字段：名称
     */
    public $name;

    /**
     * @var int 接收参数，筛选字段：类型
     */
    public $type;

    public function run()
    {
        // 所有的字段,根据列表显示进行删减
        $fields = ['`ta`.`id`', '`ta`.`parent_id`', '`ta`.`key`', '`ta`.`name`',
            '`ta`.`desc`', '`ta`.`type`', '`ta`.`icon`', '`ta`.`path`',
            '`ta`.`create_time`', '`ta`.`create_by`', '`ta`.`sort`',
            'tb.nick_name as create_by_name',
        ];

        // 构建基础查询
        $tb_user_info = $this->external->userInfoTableName();
        $tb_priv = $this->external->privilegeTableName();
        $user_id = TokenFilter::getPayload('user_id');

        // 构建基础查询
        $sql = $this->external->getDb()
            ->select($fields)
            ->from("$tb_priv as ta")
            ->leftJoin("$tb_user_info as tb", 'ta.create_by=tb.user_id');

        if (!HpRabcSingle::is_administrator($user_id,$this->external)) {
            $priv_ids = HpRabcSingle::getUserRelatedPrivileges($user_id, $this->external);
            if (empty($priv_ids)) {
                return array('total' => 0, 'list' => [], 'page' => 1, 'limit' => 10);
            }
            $sql->whereIn('id', $priv_ids);
        }

        // 追加筛选条件
        if ($this->id) {
            $sql->where('`ta`.`id`= :id186')
                ->bindValue('id186', $this->id);
        }
        if ($this->name) {
            $sql->where('`ta`.`name` like :name129')
                ->bindValue('name129', "%{$this->name}%");
        }
        if ($this->type) {
            $sql->where('`ta`.`type`= :type372')
                ->bindValue('type372', $this->type);
        }

        $result = $sql
            ->where('`ta`.`del_time`= :del_time402')
            ->bindValue('del_time402', 0)
            ->query();

        $data = ArrayHelper::formatTree($result, 1);

        return array(
            'total' => sizeof($data),
            'list' => $data,
            'page' => 1,
            'limit' => 10
        );
    }
}