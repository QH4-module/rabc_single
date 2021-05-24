<?php
/**
 * File Name: CascaderData.php
 * ©2020 All right reserved Qiaotongtianxia Network Technology Co., Ltd.
 * @author: hyunsu
 * @date: 2021/5/21 2:04 下午
 * @email: hyunsu@foxmail.com
 * @description:
 * @version: 1.0.0
 * ============================= 版本修正历史记录 ==========================
 * 版 本:          修改时间:          修改人:
 * 修改内容:
 *      //
 */

namespace qh4module\rabc_single\models\role;


use qh4module\rabc_single\external\ExtRabcSingle;
use qh4module\rabc_single\HpRabcSingle;
use qh4module\token\TokenFilter;
use qttx\helper\ArrayHelper;
use qttx\web\ServiceModel;

/**
 * Class CascaderData
 * @package qh4module\rabc_single\models\role
 * @property ExtRabcSingle $external
 */
class CascaderData extends ServiceModel
{
    /**
     * @var bool 是否只允许自己所关联的和下属角色选中
     */
    public $only_own_enable;

    /**
     * @var bool 是否只允许自己下属角色选中
     * 只有 only_own_enable 为 true ,该参数才有效
     */
    public $only_children_enable;


    public function run()
    {
        $user_id = TokenFilter::getPayload('user_id');

        // 存储允许选择的id
        $enable_role_ids = [];
        if ($this->only_own_enable) {
            list($role_ids, $children_ids) = HpRabcSingle::getUserRelationAllRoles($user_id, $this->external);
            if ($this->only_children_enable) {
                $enable_role_ids = $children_ids;
            }else{
                $enable_role_ids = array_merge($role_ids, $children_ids);
            }
        }

        $sql = $this->external->getDb()
            ->select(['id as value', 'parent_id', 'name as label'])
            ->from($this->external->roleTableName());
        if ($this->only_own_enable) {
            if (empty($enable_role_ids)) return [];
            $sql->whereIn('id', $enable_role_ids);
        }
        $result = $sql->where("parent_id <> '' and del_time=0")
            ->query();

        return ArrayHelper::formatTree($result, "", 'parent_id', 'value');
    }
}