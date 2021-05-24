<?php
/**
 * File Name: CascaderData.php
 * ©2020 All right reserved Qiaotongtianxia Network Technology Co., Ltd.
 * @author: hyunsu
 * @date: 2021/5/21 4:24 下午
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
use qh4module\token\TokenFilter;
use qttx\helper\ArrayHelper;
use qttx\web\ServiceModel;

/**
 * Class CascaderData
 * @package qh4module\rabc_single\models\privilege
 * @property ExtRabcSingle $external
 */
class CascaderData extends ServiceModel
{
    /**
     * @var bool 是否只返回自己相关的权限资源
     * 权限资源是单继承,下级权限是从自己的衍生而来,所以只取自己直接关联的权限就可以
     */
    public $only_own;

    /**
     * @inheritDoc
     */
    public function run()
    {
        $user_id = TokenFilter::getPayload('user_id');

        $sql = $this->external->getDb()
            ->select(['id as value', 'parent_id', 'name as label'])
            ->from($this->external->privilegeTableName());

        // 非管理员限制范围
        if (!HpRabcSingle::is_administrator($user_id,$this->external)) {
            $priv_ids = HpRabcSingle::getUserRelatedPrivileges($user_id, $this->external);
            if(empty($priv_ids)) return [];
            $sql->whereIn('id', $priv_ids);
        }

        $result = $sql->where("del_time=0")
            ->query();

        return ArrayHelper::formatTree($result, "", 'parent_id', 'value');
    }
}