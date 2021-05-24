<?php
/**
 * File Name: Update.php
 * ©2020 All right reserved Qiaotongtianxia Network Technology Co., Ltd.
 * @author: hyunsu
 * @date: 2021/5/21 11:29 上午
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
use qttx\components\db\DbModel;
use qttx\helper\StringHelper;
use qttx\validators\AccountValidator;

class Update extends UserModel
{
    /**
     * @var string 接收参数,必须：ID
     */
    public $id;

    /**
     * @var string 接收参数,必须：账号
     */
    public $account;

    /**
     * @var string 接收参数,非必须：手机号
     */
    public $mobile;

    /**
     * @var string 接收参数,非必须：邮箱
     */
    public $email = '';

    /**
     * @var string 接收参数,非必须：密码
     */
    public $password = '';

    /**
     * @var string 接收参数,非必须：
     */
    public $wechat_unionid = '';

    /**
     * @var string 接收参数,非必须：
     */
    public $wechat_openid = '';

    /**
     * @var string 接收参数,非必须：
     */
    public $qq_id = '';

    /**
     * @var string 接收参数,非必须：
     */
    public $alipay_id = '';

    /**
     * @var string 接收参数,非必须：
     */
    public $weibo_id = '';

    /**
     * @var int 接收参数,必须：状态
     */
    public $state;

    /**
     * @var string 接收参数,必须:名字
     */
    public $nick_name;

    /**
     * @var int 接收参数,非必须,所属地区
     */
    public $city_id;

    /**
     * @var int 接收参数,非必须,性别
     */
    public $gender;

    /**
     * @var string 接收参数,非必须,生日
     */
    public $birthday;

    /**
     * @var string 接收参数,非必须,个人说明
     */
    public $description = '';

    /**
     * @var string 接收参数,非必须,头像
     */
    public $avatar = '';

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
            [['id', 'account', 'state', 'nick_name'], 'required'],
            [['id'], 'customer', 'callback' => function ($value) {
                if (HpRabcSingle::is_administrator(null, $this->external)) return true;

                // 下级用户才能更新
                $child_ids = HpRabcSingle::getUserAllChildren(null,$this->external);
                if (in_array($value, $child_ids)) {
                    return true;
                }
                return '无权限更新当前用户';
            }],
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

            // 更新账号信息
            $this->updateUser($db);

            // 更新基本信息
            $this->updateInfo($db);

            // 更新角色关联
            $this->updateRole($db);

            // 清空缓存
            RabcRedis::clearRedis();
            HpRabcSingle::delRedisUserInfo($this->id);

            $db->commitTrans();

            return true;

        } catch (\Exception $exception) {
            $db->rollBackTrans();
            throw $exception;
        }
    }

    /**
     * @param $db DbModel
     */
    protected function updateUser($db)
    {
        $cols = [
            'account' => $this->account,
            'state' => $this->state,
        ];
        if ($this->mobile) $cols['mobile'] = $this->mobile;
        if ($this->email) $cols['email'] = $this->email;
        if ($this->wechat_unionid) $cols['wechat_unionid'] = $this->wechat_unionid;
        if ($this->wechat_openid) $cols['wechat_openid'] = $this->wechat_openid;
        if ($this->qq_id) $cols['qq_id'] = $this->qq_id;
        if ($this->alipay_id) $cols['alipay_id'] = $this->alipay_id;
        if ($this->weibo_id) $cols['alipay_id'] = $this->weibo_id;
        if ($this->alipay_id) $cols['alipay_id'] = $this->alipay_id;

        if ($this->password) {
            $cols['salt'] = StringHelper::random(8);
            $cols['password'] = md5($cols['salt'] . $this->password);
        }

        $db->update($this->external->userTableName())
            ->cols($cols)
            ->whereArray(['id' => $this->id])
            ->query();
    }

    /**
     * @param $db DbModel
     */
    protected function updateInfo($db)
    {
        $cols = ['nick_name' => $this->nick_name];
        if ($this->city_id) $cols['city_id'] = $this->city_id;
        if ($this->gender) $cols['gender'] = $this->gender;
        if ($this->birthday) $cols['birthday'] = $this->birthday;
        if ($this->avatar) $cols['avatar'] = $this->avatar;
        $cols['description'] = $this->description;

        $db->update($this->external->userInfoTableName())
            ->cols($cols)
            ->whereArray(['user_id' => $this->id])
            ->query();
    }

    /**
     * @param $db DbModel
     */
    protected function updateRole($db)
    {
        $db->update($this->external->relUserRoleTableName())
            ->col('del_time', time())
            ->whereArray(['user_id' => $this->id])
            ->where('del_time=0')
            ->query();

        if (empty($this->role_ids)) return;

        foreach ($this->role_ids as $rid) {
            $db->insert($this->external->relUserRoleTableName())
                ->cols([
                    'user_id' => $this->id,
                    'role_id' => $rid,
                    'del_time' => 0
                ])
                ->query();
        }
    }
}