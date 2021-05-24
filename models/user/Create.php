<?php
/**
 * File Name: Create.php
 * ©2020 All right reserved Qiaotongtianxia Network Technology Co., Ltd.
 * @author: hyunsu
 * @date: 2021/5/21 11:10 上午
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
use qh4module\rabc_single\models\RabcRedis;
use qh4module\token\TokenFilter;
use QTTX;
use qttx\components\db\DbModel;
use qttx\helper\StringHelper;
use qttx\validators\AccountValidator;

/**
 * Class Create
 * @package qh4module\rabc_single\models\user
 */
class Create extends UserModel
{
    /**
     * @var string 接收参数,必须：账号
     */
    public $account;

    /**
     * @var string 接收参数,必须：密码
     */
    public $password;

    /**
     * @var string 接收参数,必须:名字
     */
    public $nick_name;

    /**
     * @var int 接收参数,非必须,所属地区
     */
    public $city_id = 0;

    /**
     * @var string[] 接收参数,非必须,所属角色
     */
    public $role_ids = [];


    /**
     * @inheritDoc
     */
    public function rules()
    {
        return $this->mergeRules([
            [['account', 'password', 'nick_name'], 'required'],
            [['account'], 'account', 'mode' => AccountValidator::TYPE_MODE2],
            [['mobile'], 'mobile'],
            [['role_ids'], 'array', 'type' => 'string'],
            [['role_ids'], 'customer', 'callback' => function ($value) {
                if (HpRabcSingle::is_administrator(null, $this->external)) return true;

                list($role_ids, $children_ids) = HpRabcSingle::getUserRelationAllRoles(null, $this->external);
                if (empty(array_diff($value, $children_ids))) {
                    return true;
                }
                return '角色超出授权范围';
            }],
        ], parent::rules());
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        $db = $this->external->getDb();

        $db->beginTrans();

        try {
            // 查询账号是否重复
            $has = $db->select('id')
                ->from($this->external->userTableName())
                ->whereArray(['account' => $this->account])
                ->row();
            if (!empty($has)) {
                $this->addError('account', '账号重复');
                return false;
            }

            $id = QTTX::$app->snowflake->id();

            // 用户账号表插入
            $this->insertUser($id, $db);

            // 用户信息表插入
            $this->insertUserInfo($id, $db);

            // 角色关联表插入
            $this->insertRole($id, $db);

            // 清空rabc相关缓存
            RabcRedis::clearRedis();

            $db->commitTrans();

            return true;

        } catch (\Exception $exception) {
            $db->rollBackTrans();
            throw $exception;
        }
    }

    /**
     * @param $id
     * @param $db DbModel
     */
    protected function insertUser($id, $db)
    {
        $salt = StringHelper::random(8);
        $db->insert($this->external->userTableName())
            ->cols([
                'id' => $id,
                'account' => $this->account,
                'salt' => $salt,
                'password' => md5($salt . $this->password),
                'create_by' => TokenFilter::getPayload('user_id'),
                'create_time' => time(),
                'state' => 1,
                'del_time' => 0
            ])
            ->query();
    }

    /**
     * @param $id
     * @param $db DbModel
     */
    protected function insertUserInfo($id, $db)
    {
        $db->insert($this->external->userInfoTableName())
            ->cols([
                'user_id' => $id,
                'nick_name' => $this->nick_name,
                'gender' => 0,
                'avatar' => '',
                'city_id' => $this->city_id,
                'balance' => 0,
                'scores' => 0,
                'level' => 0,
            ])
            ->query();
    }

    /**
     * @param $id
     * @param $db DbModel
     */
    protected function insertRole($id, $db)
    {
        if (empty($this->role_ids)) return;

        foreach ($this->role_ids as $rid) {
            $db->insert($this->external->relUserRoleTableName())
                ->cols([
                    'user_id' => $id,
                    'role_id' => $rid,
                    'del_time' => 0
                ])
                ->query();
        }
    }
}