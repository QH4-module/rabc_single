<?php
/**
 * File Name: Delete.php
 * ©2020 All right reserved Qiaotongtianxia Network Technology Co., Ltd.
 * @author: hyunsu
 * @date: 2021/5/21 1:39 下午
 * @email: hyunsu@foxmail.com
 * @description:
 * @version: 1.0.0
 * ============================= 版本修正历史记录 ==========================
 * 版 本:          修改时间:          修改人:
 * 修改内容:
 *      //
 */

namespace qh4module\rabc_single\models\user;


use qh4module\rabc_single\HpRabcSingle;
use qh4module\token\TokenFilter;

class Delete extends UserModel
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
            [['ids'],'customer','callback'=>function($value){
                if (HpRabcSingle::is_administrator(null, $this->external)) return true;
                // 只能删除下属用户
                $children_ids = HpRabcSingle::getUserAllChildren(null, $this->external);
                if (empty(array_diff($value, $children_ids))) {
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

            // 删除所有用户
            $db->update($this->external->userTableName())
                ->col('del_time', time())
                ->whereIn('id', $this->ids)
                ->query();

            // 删除角色关联
            $db->update($this->external->relUserRoleTableName())
                ->col('del_time', time())
                ->whereIn('user_id', $this->ids)
                ->where('del_time=0')
                ->query();

            // 清理缓存
            foreach ($this->ids as $item) {
                HpRabcSingle::delRedisUserInfo($item);
            }

            $db->commitTrans();

            return true;

        } catch (\Exception $exception) {
            $db->rollBackTrans();

            throw $exception;
        }
    }
}