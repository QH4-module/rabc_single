<?php
/**
 * File Name: MainMenu.php
 * ©2020 All right reserved Qiaotongtianxia Network Technology Co., Ltd.
 * @author: hyunsu
 * @date: 2021/5/24 2:11 下午
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
 * Class MainMenu
 * @package qh4module\rabc_single\models\privilege
 * @property ExtRabcSingle $external
 */
class MainMenu extends ServiceModel
{
    /**
     * @var string 子级字段的名称
     */
    public $children_name = 'children';

    /**
     * @inheritDoc
     */
    public function run()
    {
        $sql = $this->external->getDb()
            ->select(['id', 'parent_id', 'name', 'path', 'icon'])
            ->from($this->external->privilegeTableName());

        $user_id = TokenFilter::getPayload('user_id');
        if (!HpRabcSingle::is_administrator($user_id, $this->external)) {
            $priv_ids = HpRabcSingle::getUserRelatedPrivileges($user_id, $this->external);
            $sql->whereIn('id', $priv_ids);
        }


        $result = $sql
            ->where('type=1 and del_time=0')
            ->query();

        $result = ArrayHelper::formatTree($result, '', 'parent_id', 'id', $this->children_name);

        return $result;
    }
}