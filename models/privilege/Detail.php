<?php
/**
 * File Name: Detail.php
 * ©2020 All right reserved Qiaotongtianxia Network Technology Co., Ltd.
 * @author: hyunsu
 * @date: 2021/5/21 4:59 下午
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
use qttx\web\ServiceModel;

/**
 * Class Detail
 * @package qh4module\rabc_single\models\privilege
 * @property ExtRabcSingle $external
 */
class Detail extends ServiceModel
{
    /**
     * @var string|int 接收参数,必须：主键
     */
    public $id;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['id'],'required'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        // 所有的字段,根据需要删减
        $fields = ['`ta`.`id`','`ta`.`parent_id`','`ta`.`id_path`',
            '`ta`.`key_path`','`ta`.`key`','`ta`.`name`',
            '`ta`.`desc`','`ta`.`type`','`ta`.`icon`','`ta`.`path`',
            '`ta`.`create_time`','`ta`.`create_by`','`ta`.`sort`',
            'tb.nick_name as create_by_name',
        ];
        $tb_user_info = $this->external->userInfoTableName();
        $tb_role = $this->external->privilegeTableName();

        $result = $this->external->getDb()
            ->select($fields)
            ->from("$tb_role as ta")
            ->leftJoin("$tb_user_info as tb", 'ta.create_by=tb.user_id')
            ->whereArray(['id' => $this->id])
            ->row();
        if (empty($result)) {
            $this->addError('id', '查询条目不存在');
            return false;
        }

        return $result;
    }
}