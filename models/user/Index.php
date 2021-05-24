<?php
/**
 * File Name: Index.php
 * ©2020 All right reserved Qiaotongtianxia Network Technology Co., Ltd.
 * @author: hyunsu
 * @date: 2021/5/21 10:43 上午
 * @email: hyunsu@foxmail.com
 * @description:
 * @version: 1.0.0
 * ============================= 版本修正历史记录 ==========================
 * 版 本:          修改时间:          修改人:
 * 修改内容:
 *      //
 */

namespace qh4module\rabc_single\models\user;


use qh4module\qhgc\SorterValidator;
use qh4module\rabc_single\external\ExtRabcSingle;
use qh4module\rabc_single\HpRabcSingle;
use qh4module\token\TokenFilter;
use qttx\web\ServiceModel;

/**
 * Class Index
 * @package qh4module\rabc_single\models\user
 * @property ExtRabcSingle $external
 */
class Index extends ServiceModel
{
    /**
     * @var int 页数,从1开始
     */
    public $page = 1;

    /**
     * @var int 每页显示数量
     */
    public $limit = 10;

    /**
     * @var array 接收参数,排序规则
     * 格式:['id'=>'asc','name'=>'desc'],
     */
    public $sorter = [];

    /**
     * @var string 接收参数，筛选字段：ID
     */
    public $id;

    /**
     * @var string 接收参数，筛选字段：账号
     */
    public $account;

    /**
     * @var string 接收参数，筛选字段：手机号
     */
    public $mobile;

    /**
     * @var string 接收参数，筛选字段：邮箱
     */
    public $email;

    /**
     * @var string 接收参数，筛选字段
     */
    public $wechat_unionid;

    /**
     * @var string 接收参数，筛选字段
     */
    public $wechat_openid;

    /**
     * @var string 接收参数，筛选字段
     */
    public $qq_id;

    /**
     * @var string 接收参数，筛选字段
     */
    public $alipay_id;

    /**
     * @var string 接收参数，筛选字段
     */
    public $weibo_id;

    /**
     * @var string 接收参数，筛选字段
     */
    public $apple_id;

    /**
     * @var int 接收参数，筛选字段：状态
     */
    public $state;

    /**
     * @var int 接收参数，筛选字段：昵称
     */
    public $nick_name;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return $this->mergeRules([
            [['page', 'limit'], 'integer'],
            [['sorter'], 'sorter'],
        ], parent::rules());
    }

    /**
     * @inheritDoc
     */
    public function attributeLangs()
    {
        return $this->mergeLanguages([
            'page' => '页数',
            'limit' => '每页条数',
            'sorter' => '排序规则',
        ], parent::attributeLangs());
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        // 所有的字段,根据列表显示进行删减
        $fields = ['`ta`.`id`', '`ta`.`account`', '`ta`.`mobile`', '`ta`.`email`', '`ta`.`create_by`', '`ta`.`create_time`', '`ta`.`wechat_unionid`', '`ta`.`wechat_openid`',
            '`ta`.`qq_id`', '`ta`.`alipay_id`', '`ta`.`weibo_id`',
            '`ta`.`apple_id`', '`ta`.`state`', '`tb`.`nick_name`', '`tb`.`avatar`', '`tb`.`gender`',
            'tc.nick_name as create_by_name','`ta`.`id` as key',
        ];

        $user_id = TokenFilter::getPayload('user_id');
        // 构建基础查询
        $tb_user = $this->external->userTableName();
        $tb_info = $this->external->userInfoTableName();
        $db = $this->external->getDb();
        $sql = $db
            ->calcFoundRows()
            ->select($fields)
            ->from("$tb_user as ta")
            ->leftJoin("$tb_info as tb", 'ta.id=tb.user_id')
            ->leftJoin("$tb_info as tc", 'ta.create_by=tc.user_id');

        // 非管理员只显示自己和下属用户
        if (!HpRabcSingle::is_administrator($user_id, $this->external)) {
            $children_ids = HpRabcSingle::getUserAllChildren($user_id, $this->external);
            $children_ids[] = $user_id;
            $sql ->whereIn('id', $children_ids);
        }

        // 追加筛选条件
        if ($this->id) {
            $sql->where('`ta`.`id`= :id339')
                ->bindValue('id339', $this->id);
        }
        if ($this->account) {
            $sql->where('`ta`.`account` like :account797')
                ->bindValue('account797', "%{$this->account}%");
        }
        if ($this->mobile) {
            $sql->where('`ta`.`mobile` like :mobile338')
                ->bindValue('mobile338', "%{$this->mobile}%");
        }
        if ($this->email) {
            $sql->where('`ta`.`email` like :email396')
                ->bindValue('email396', "%{$this->email}%");
        }
        if ($this->wechat_unionid) {
            $sql->where('`ta`.`wechat_unionid`= :wechat_unionid777')
                ->bindValue('wechat_unionid777', $this->wechat_unionid);
        }
        if ($this->wechat_openid) {
            $sql->where('`ta`.`wechat_openid`= :wechat_openid915')
                ->bindValue('wechat_openid915', $this->wechat_openid);
        }
        if ($this->qq_id) {
            $sql->where('`ta`.`qq_id`= :qq_id593')
                ->bindValue('qq_id593', $this->qq_id);
        }
        if ($this->alipay_id) {
            $sql->where('`ta`.`alipay_id`= :alipay_id629')
                ->bindValue('alipay_id629', $this->alipay_id);
        }
        if ($this->weibo_id) {
            $sql->where('`ta`.`weibo_id`= :weibo_id689')
                ->bindValue('weibo_id689', $this->weibo_id);
        }
        if ($this->apple_id) {
            $sql->where('`ta`.`apple_id`= :apple_id346')
                ->bindValue('apple_id346', $this->apple_id);
        }
        if ($this->state) {
            $sql->where('`ta`.`state`= :state726')
                ->bindValue('state726', $this->state);
        }
        if ($this->nick_name) {
            $sql->where('`tb`.`nick_name` like :nick_name392')
                ->bindValue('nick_name392', "%{$this->nick_name}%");
        }

        // 追加排序
        if ($this->sorter) {
            $sql->orderBy(SorterValidator::format2Mode1($this->sorter));
        }

        // 获取分页结果
        $data = $sql
            ->where('`ta`.`del_time`= :del_time755')
            ->bindValue('del_time755', 0)
            ->offset(($this->page - 1) * $this->limit)
            ->limit($this->limit)
            ->query();
        // 获取总数
        $total = $db->single('SELECT FOUND_ROWS()');

        return array(
            'total' => $total,
            'list' => $data,
            'page' => $this->page,
            'limit' => $this->limit
        );
    }
}