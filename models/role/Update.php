<?php
/**
 * File Name: Update.php
 * ©2020 All right reserved Qiaotongtianxia Network Technology Co., Ltd.
 * @author: hyunsu
 * @date: 2021/5/21 2:32 下午
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

/**
 * Class Update
 * @package qh4module\rabc_single\models\role
 */
class Update extends RoleModel
{
    /**
     * @var string 接收参数,必须：ID
     */
    public $id;

    /**
     * @var string 接收参数,必须：名称
     */
    public $name;

    /**
     * @var string 接收参数,非必须：说明
     */
    public $desc;

    /**
     * @var array 接收参数,关联权限id
     */
    public $privilege_ids;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return $this->mergeRules([
            [['id', 'name'], 'required'],
            [['id'],'customer','callback'=>function($value){
                if (HpRabcSingle::is_administrator(null, $this->external)) return true;
                // 下属角色才能更改
                list($role_ids,$child_ids) = HpRabcSingle::getUserRelationAllRoles();
                if (in_array($value, $child_ids)) {
                    return true;
                }
                return '无权限更新当前角色';
            }],
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

            // 固定角色不能更改
            $result = $db->select('is_fixed')
                ->from($this->external->roleTableName())
                ->whereArray(['id' => $this->id])
                ->where('del_time=0')
                ->row();
            if (empty($result)) {
                $db->rollBackTrans();
                $this->addError('id', '角色不存在获取已被删除');
                return false;
            }
            if ($result['is_fixed'] == 1) {
                $db->rollBackTrans();
                $this->addError('id', '当前角色不能更新');
                return false;
            }

            // 更新
            $db->update($this->external->roleTableName())
                ->cols([
                    'name' => $this->name,
                    'desc' => $this->desc,
                ])
                ->whereArray(['id' => $this->id])
                ->query();


            $this->updatePrivilege($db);

            // 清理缓存
            RabcRedis::clearRedis();

            $db->commitTrans();

            return true;

        } catch (\Exception $exception) {
            $db->rollBackTrans();
            throw $exception;
        }
    }

    private function updatePrivilege($db)
    {
        $db->update($this->external->relRolePrivTableName())
            ->col('del_time',time())
            ->whereArray(['role_id'=>$this->id])
            ->where('del_time=0')
            ->query();

        if(empty($this->privilege_ids)) return;

        foreach ($this->privilege_ids as $pid) {
            $db->insert($this->external->relRolePrivTableName())
                ->cols([
                    'role_id' => $this->id,
                    'privilege_id' => $pid,
                    'del_time' => 0
                ])
                ->query();
        }
    }
}