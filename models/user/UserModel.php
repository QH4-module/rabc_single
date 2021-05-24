<?php
/**
 * File Name: UserModel.php
 * ©2020 All right reserved Qiaotongtianxia Network Technology Co., Ltd.
 * @author: hyunsu
 * @date: 2021/5/21 10:49 上午
 * @email: hyunsu@foxmail.com
 * @description:
 * @version: 1.0.0
 * ============================= 版本修正历史记录 ==========================
 * 版 本:          修改时间:          修改人:
 * 修改内容:
 *      //
 */

namespace qh4module\rabc_single\models\user;


use qh4module\rabc_single\external\ExtRabcSingle;
use qttx\web\ServiceModel;

/**
 * Class UserModel
 * @package qh4module\rabc_single\models\user
 * @property ExtRabcSingle $external
 */
class UserModel extends ServiceModel
{
    /**
     * @inheritDoc
     */
    public function rules()
    {
        return $this->mergeRules([
            [['account'], 'string', ['max' => 100]],
            [['mobile'], 'string', ['max' => 15]],
            [['email', 'wechat_unionid', 'wechat_openid', 'qq_id', 'alipay_id', 'weibo_id', 'apple_id'], 'string', ['max' => 150]],
            [['create_time', 'state', 'city_id', 'level'], 'integer'],
            [['user_id'], 'string', ['max' => 64]],
            [['nick_name'], 'string', ['max' => 20]],
            [['avatar'], 'string', ['max' => 500]],
            [['birthday'], 'match', 'pattern' => '/\d+-\d+-\d+/'],
            [['description'], 'string', ['max' => 1000]],
            [['gender'], 'in', 'range' => [0, 1, 2]],
            [['balance', 'sources'], 'number'],
        ], $this->external->rules());
    }

    /**
     * @inheritDoc
     */
    public function attributeLangs()
    {
        return $this->mergeLanguages([
            'id' => 'ID',
            'account' => '账号',
            'mobile' => '手机号',
            'email' => '邮箱',
            'password' => '密码',
            'salt' => '密码混淆随机数',
            'create_by' => '创建人',
            'create_time' => '创建时间',
            'state' => '状态',
            'user_id' => '用户ID',
            'nick_name' => '昵称',
            'avatar' => '头像',
            'gender' => '性别',
            'birthday' => '生日',
            'description' => '个人简介',
            'city_id' => '城市',
            'balance' => '余额',
            'scores' => '积分',
            'level' => '等级',
        ], $this->external->attributeLangs());
    }
}