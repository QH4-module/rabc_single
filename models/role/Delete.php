<?php
/**
 * File Name: Delete.php
 * ©2020 All right reserved Qiaotongtianxia Network Technology Co., Ltd.
 * @author: hyunsu
 * @date: 2021/5/21 3:04 下午
 * @email: hyunsu@foxmail.com
 * @description:
 * @version: 1.0.0
 * ============================= 版本修正历史记录 ==========================
 * 版 本:          修改时间:          修改人:
 * 修改内容:
 *      //
 */

namespace qh4module\rabc_single\models\role;


use qh4module\rabc\models\RabcRedis;
use qh4module\rabc_single\HpRabcSingle;

/**
 * Class Delete
 * @package qh4module\rabc_single\models\role
 */
class Delete extends RoleModel
{
    /**
     * @var string[]|int[] 接收参数,必须：主键
     */
    public $ids;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['ids'], 'required'],
            [['ids'], 'array', 'type' => function ($value) {
                return is_string($value) || is_numeric($value);
            }],
            [['ids'], 'customer', 'callback' => function ($value) {
                if(HpRabcSingle::is_administrator(null,$this->external)) return true;
                // 下属角色才能更改
                list($role_ids, $child_ids) = HpRabcSingle::getUserRelationAllRoles();
                if (empty(array_diff($value, $child_ids))) {
                    return true;
                }
                return '权限不足，无法删除';
            }],
        ];
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        $db = $this->external->getDb();

        $db->beginTrans();

        try {

            // 固定角色不能删除
            $result = $db->select('is_fixed')
                ->from($this->external->roleTableName())
                ->whereIn('id',$this->ids)
                ->where('del_time=0')
                ->query();
            if (empty($result)) {
                $db->rollBackTrans();
                $this->addError('id', '角色不存在获取已被删除');
                return false;
            }
            foreach ($result as $item) {
                if ($item['is_fixed'] == 1) {
                    $db->rollBackTrans();
                    $this->addError('id', '存在不能删除的角色');
                    return false;
                }
            }


            $t = time();

            // 获取所有下级
            $child_ids = HpRabcSingle::getRoleAllChildren($this->ids, false, $this->external);
            // 拼合所有要删除的id
            $ids = array_merge($child_ids, $this->ids);

            // 全部删除
            $db->update($this->external->roleTableName())
                ->col('del_time', $t )
                ->whereIn('id', $ids)
                ->where('del_time=0')
                ->query();

            // 删除所有冗余
            $db->update($this->external->roleMoreTableName())
                ->col('del_time', $t )
                ->whereIn('role_id', $ids)
                ->where('del_time=0')
                ->query();

            // 删除所有权限关联
            $db->update($this->external->relRolePrivTableName())
                ->col('del_time', $t )
                ->whereIn('role_id', $ids)
                ->where('del_time=0')
                ->query();

            // 删除所有用户关联
            $db->update($this->external->relUserRoleTableName())
                ->col('del_time', $t )
                ->whereIn('role_id', $ids)
                ->where('del_time=0')
                ->query();

            // 清理缓存
            RabcRedis::clearRedis();

            $db->commitTrans();

            return true;

        } catch (\Exception $exception) {
            $db->rollBackTrans();
            throw $exception;
        }
    }
}