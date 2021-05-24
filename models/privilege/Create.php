<?php
/**
 * File Name: Create.php
 * ©2020 All right reserved Qiaotongtianxia Network Technology Co., Ltd.
 * @author: hyunsu
 * @date: 2021/5/21 4:10 下午
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
use qttx\components\db\DbModel;

class Create extends PrivilegeModel
{
    /**
     * @var string 接收参数,非必须：上级
     */
    public $parent_id = '';

    /**
     * @var string 接收参数,必须：唯一标记
     */
    public $key;

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
            [['key', 'name', 'type', 'path'], 'required'],
        ], parent::rules());
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        $db = $this->external->getDb();

        if (!HpRabcSingle::is_administrator(null, $this->external)) {
            $this->addError('id', '只有管理员可以新增权限');
            return false;
        }

        $id = \QTTX::$app->snowflake->id();
        $id_path = $id;
        $key_path = $this->key;
        $level = 1;

        // 检查同级下面是否存在一样的key
        if (!$this->checkKey($db)) {
            $this->addError('key', '唯一标记重复');
            return false;
        }

        // 插入
        $db->insert($this->external->privilegeTableName())
            ->cols([
                'id' => $id,
                'parent_id' => $this->parent_id,
                'id_path' => $id_path,
                'key' => $this->key,
                'key_path' => $key_path,
                'name' => $this->name,
                'desc' => $this->desc,
                'type' => $this->type,
                'level' => $level,
                'icon' => $this->icon,
                'path' => $this->path,
                'create_time' => time(),
                'create_by' => TokenFilter::getPayload('user_id'),
                'sort' => $this->sort,
                'del_time' => 0
            ])
            ->query();

        return true;
    }

    /**
     * @param $db DbModel
     * @return bool
     */
    private function checkKey($db)
    {
        $result_key = $db
            ->select('id')
            ->from($this->external->privilegeTableName())
            ->whereArray([
                'parent_id' => $this->parent_id,
                'key' => $this->key,
            ])
            ->where('del_time=0')
            ->row();

        return empty($result_key);
    }
}