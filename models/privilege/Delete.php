<?php
/**
 * File Name: Delete.php
 * ©2020 All right reserved Qiaotongtianxia Network Technology Co., Ltd.
 * @author: hyunsu
 * @date: 2021/5/22 3:28 下午
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
use qh4module\rabc_single\HpRabcSingle;
use qttx\web\ServiceModel;

/**
 * Class Delete
 * @package qh4module\rabc_single\models\privilege
 * @property ExtRabcSingle $external
 */
class Delete extends ServiceModel
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
            [['ids'],'required'],
            [['ids'], 'array', 'type' => function ($value) {
                return is_string($value) || is_numeric($value);
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

            if (!HpRabcSingle::is_administrator(null, $this->external)) {
                $db->rollBackTrans();
                $this->addError('ids', '只有管理员可以删除权限资源');
                return false;
            }

            // 删除权限资源
            $db->update($this->external->privilegeTableName())
                ->col('del_time', time())
                ->whereIn('id', $this->ids)
                ->query();


            // 删除关联角色
            $db->update($this->external->relRolePrivTableName())
                ->col('del_time', time())
                ->whereIn('privilege_id', $this->ids)
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