<?php
/**
 * File Name: Update.php
 * ©2020 All right reserved Qiaotongtianxia Network Technology Co., Ltd.
 * @author: hyunsu
 * @date: 2021/5/21 5:07 下午
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

/**
 * Class Update
 * @package qh4module\rabc_single\models\privilege
 */
class Update extends PrivilegeModel
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
     * 1 菜单 2非菜单
     */
    public $desc = '';

    /**
     * @var int 接收参数,必须：类型
     */
    public $type;

    /**
     * @var string 接收参数,非必须：仅对菜单有效,图标
     */
    public $icon = '';

    /**
     * @var string 接收参数,必须：关联的路由
     */
    public $path;

    /**
     * @var int 接收参数,非必须：排序
     * 数字越大越靠前
     */
    public $sort = 0;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return $this->mergeRules([
            [['id', 'name', 'type', 'path'], 'required'],
        ], parent::rules());
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        $db = $this->external->getDb();

        if (!HpRabcSingle::is_administrator(null, $this->external)) {
            $this->addError('id', '只有管理员可以更新权限');
            return false;
        }

        // 更新
        $db->update($this->external->privilegeTableName())
            ->cols([
                'name' => $this->name,
                'desc' => $this->desc,
                'type' => $this->type,
                'icon' => $this->icon,
                'path' => $this->path,
                'sort' => $this->sort,
            ])
            ->whereArray(['id' => $this->id])
            ->query();

        return true;
    }
}