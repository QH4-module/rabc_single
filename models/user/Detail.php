<?php
/**
 * File Name: Detail.php
 * ©2020 All right reserved Qiaotongtianxia Network Technology Co., Ltd.
 * @author: hyunsu
 * @date: 2021/5/21 11:23 上午
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

/**
 * Class Detail
 * @package qh4module\rabc_single\models\user
 */
class Detail extends UserModel
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
            [['id'], 'required'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        // 所有的字段,根据需要删减
        $fields = ['`ta`.`id`', '`ta`.`account`', '`ta`.`mobile`', '`ta`.`email`',
            '`ta`.`create_by`', '`ta`.`create_time`', '`ta`.`wechat_unionid`',
            '`ta`.`wechat_openid`', '`ta`.`qq_id`', '`ta`.`alipay_id`',
            '`ta`.`weibo_id`', '`ta`.`apple_id`', '`ta`.`state`',
            '`tb`.`nick_name`', '`tb`.`avatar`', '`tb`.`gender`', '`tb`.`birthday`',
            '`tb`.`description`', '`tb`.`city_id`','`tb`.`balance`','`tb`.`scores`','`tb`.`level`',
            'tc.nick_name as create_by_name',
            'td.name as city_name'
        ];

        $tb_info = $this->external->userInfoTableName();
        $tb_user = $this->external->userTableName();
        $tb_city = $this->external->cityTableName();
        $result = $this->external->getDb()
            ->select($fields)
            ->from("$tb_user as ta")
            ->leftJoin("$tb_info as tb", 'ta.id=tb.user_id')
            ->leftJoin("$tb_info as tc", 'ta.create_by=tc.user_id')
            ->leftJoin("$tb_city as td",'tb.city_id=td.id')
            ->whereArray(['ta.id' => $this->id])
            ->row();

        if (empty($result)) {
            $this->addError('id', '查询条目不存在');
            return false;
        }

        // 获取关联角色
        $result['role_ids'] = HpRabcSingle::getUserRelatedRoles($this->id, [
            'id' => 'value',
            'name' => 'label'
        ], $this->external);

        return $result;
    }
}