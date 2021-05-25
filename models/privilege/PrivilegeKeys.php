<?php
/**
 * File Name: PrivilegeKeys.php
 * ©2020 All right reserved Qiaotongtianxia Network Technology Co., Ltd.
 * @author: hyunsu
 * @date: 2021/5/24 4:49 下午
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
use qttx\web\ServiceModel;

/**
 * Class PrivilegeKeys
 * @package qh4module\rabc_single\models\privilege
 * @property ExtRabcSingle $external
 */
class PrivilegeKeys extends ServiceModel
{
    /**
     * @inheritDoc
     */
    public function run()
    {
        $sql = $this->external->getDb()
            ->select('key_path')
            ->from($this->external->privilegeTableName());

        $user_id = TokenFilter::getPayload('user_id');

        if (!HpRabcSingle::is_administrator($user_id, $this->external)) {
            $priv_ids = HpRabcSingle::getUserRelatedPrivileges($user_id, $this->external);
            $sql->whereIn('id', $priv_ids);
        }

        $result = $sql
            ->where('del_time=0')
            ->column();

        return $result ?: [];
    }
}