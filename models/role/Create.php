<?php
/**
 * File Name: Create.php
 * ©2020 All right reserved Qiaotongtianxia Network Technology Co., Ltd.
 * @author: hyunsu
 * @date: 2021/5/21 2:12 下午
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
use qh4module\rabc_single\models\RabcRedis;
use qh4module\token\TokenFilter;
use QTTX;
use qttx\components\db\DbModel;

/**
 * Class Create
 * @package qh4module\rabc_single\models\role
 */
class Create extends RoleModel
{
    /**
     * @var array 接收参数,上级id
     */
    public $parent_id;

    /**
     * @var string 接收参数,必须：名称
     */
    public $name;

    /**
     * @var string 接收参数,非必须：说明
     */
    public $desc;

    /**
     * @var array 接收参数,权限id
     */
    public $privilege_ids;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        list($role_ids, $children_ids) = HpRabcSingle::getUserRelationAllRoles(null, $this->external);
        $all_role_ids = array_merge($role_ids, $children_ids);

        return $this->mergeRules([
            [['parent_id', 'name'], 'required'],
            [['parent_id'], 'in', 'range' => $all_role_ids, 'message' => '上级超出授权范围'],
            [['privilege_ids'], 'array', 'type' => 'string'],
            [['privilege_ids'], 'customer', 'callback' => function ($value) {
                if (HpRabcSingle::is_administrator(null, $this->external)) return true;

                $privilege_ids = HpRabcSingle::getUserRelatedPrivileges(null, $this->external);
                if (empty(array_diff($value, $privilege_ids))) {
                    return true;
                }
                return '超出授权范围';
            }],
        ], parent::rules());
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        $db = $this->external->getDb();

        $db->beginTrans();

        try {

            $result_parent = $db
                ->select(['id', 'id_path'])
                ->from($this->external->roleTableName())
                ->whereArray(['id' => $this->parent_id])
                ->row();
            if (empty($result_parent)) {
                $db->rollBackTrans();
                $this->addError('parent_id', '上级无效');
                return false;
            }

            $id = QTTX::$app->snowflake->id();

            // 插入角色表
            $this->insertRole($id, $result_parent, $db);

            // 插入权限
            $this->insertPrivilege($id, $db);

            // 清理缓存
            RabcRedis::clearRedis();

            $db->commitTrans();

            return true;

        } catch (\Exception $exception) {
            $db->rollBackTrans();
            throw $exception;
        }

    }

    /**
     * @param $id
     * @param $result_parent
     * @param $db DbModel
     */
    protected function insertRole($id, $result_parent, $db)
    {
        $ary_path = explode(',', $result_parent['id_path']);
        $ary_path[] = $id;

        $db->insert($this->external->roleTableName())
            ->cols([
                'id' => $id,
                'parent_id' => $this->parent_id,
                'id_path' => implode(',', $ary_path),
                'name' => $this->name,
                'desc' => $this->desc,
                'create_by' => TokenFilter::getPayload('user_id'),
                'create_time' => time(),
                'is_fixed' => 0,
                'del_time' => 0
            ])
            ->query();

        // 插入冗余表,把 父级 id_path 循环一遍
        $ary_path = explode(',', $result_parent['id_path']);
        foreach ($ary_path as $index => $item) {
            $db->insert($this->external->roleMoreTableName())
                ->cols([
                    'role_id' => $id,
                    'parent_id' => $item,
                    'asc_level' => sizeof($ary_path) - $index,
                    'desc_level' => $index + 1,
                    'create_time' => time(),
                    'del_time' => 0
                ])
                ->query();
        }
    }

    /**
     * @param $id
     * @param $db DbModel
     */
    private function insertPrivilege($id, $db)
    {
        if (empty($this->privilege_ids)) return;

        foreach ($this->privilege_ids as $pid) {
            $db->insert($this->external->relRolePrivTableName())
                ->cols([
                    'role_id' => $id,
                    'privilege_id' => $pid,
                    'del_time' => 0
                ])
                ->query();
        }
    }
}