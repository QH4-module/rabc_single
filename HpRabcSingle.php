<?php
/**
 * File Name: HpRabcSingle.php
 * ©2020 All right reserved Qiaotongtianxia Network Technology Co., Ltd.
 * @author: hyunsu
 * @date: 2021/5/21 10:34 上午
 * @email: hyunsu@foxmail.com
 * @description:
 * @version: 1.0.0
 * ============================= 版本修正历史记录 ==========================
 * 版 本:          修改时间:          修改人:
 * 修改内容:
 *      //
 */

namespace qh4module\rabc_single;


use qh4module\rabc_single\external\ExtRabcSingle;
use qh4module\rabc_single\models\RabcRedis;
use qh4module\token\TokenFilter;
use qttx\components\db\DbModel;
use qttx\web\External;

class HpRabcSingle
{
    /**
     * 从redis中删除用户信息
     * 个人用户的信息,Rabc内部会自动维护
     * 如果程序中手动修改了用户信息,请手动删除或者修改缓存
     * @param $user_id
     * @param ExtRabcSingle $external
     */
    public static function delRedisUserInfo($user_id, ExtRabcSingle $external = null)
    {
        if (empty($user_id)) $user_id = TokenFilter::getPayload('user_id');
        if (is_null($external)) $external = new ExtRabcSingle();
        if ($redis = $external->getRedis()) {
            $key = RabcRedis::user_info($user_id);
            $redis->del($key);
        }
    }

    /**
     * 获取指定用户是否是管理员
     * @param string $user_id
     * @param External|null $external
     * @param DbModel $db
     * @return bool
     */
    public static function is_administrator($user_id = null, External $external = null, $db = null)
    {
        if (empty($user_id)) $user_id = TokenFilter::getPayload('user_id');
        if (is_null($external)) $external = new ExtRabcSingle();
        if (is_null($db)) $db = $external->getDb();

        $result = $db->select('id')
            ->from($external->relUserRoleTableName())
            ->whereArray([
                'user_id' => $user_id,
                'role_id' => '1'
            ])
            ->where('del_time=0')
            ->row();

        return !empty($result);
    }

    /**
     * 获取所有的下级用户,不包括自己
     * @param string $user_id
     * @param ExtRabcSingle|null $external
     * @param DbModel $db
     * @return array
     */
    public static function getUserAllChildren($user_id = null, ExtRabcSingle $external = null, $db = null)
    {
        if (empty($user_id)) $user_id = TokenFilter::getPayload('user_id');
        if (is_null($external)) $external = new ExtRabcSingle();
        if (is_null($db)) $db = $external->getDb();
        list($role_ids, $children_role_ids) = self::getUserRelationAllRoles($user_id, $external, $db);
        if (empty($children_role_ids)) return [];
        $result = $db->select('user_id')
            ->from($external->relUserRoleTableName())
            ->whereIn('role_id', $children_role_ids)
            ->where('del_time=0')
            ->column();
        return $result ?: [];
    }

    /**
     * 获取用户关联的所有权限
     * @param string $user_id
     * @param ExtRabcSingle|null $external
     * @param DbModel $db
     * @return array
     */
    public static function getUserRelatedPrivileges($user_id = null, ExtRabcSingle $external = null, $db = null)
    {
        if (empty($user_id)) $user_id = TokenFilter::getPayload('user_id');
        if (is_null($external)) $external = new ExtRabcSingle();
        if (is_null($db)) $db = $external->getDb();
        $role_ids = self::getUserRelatedRoles($user_id, false, $external, $db);
        if (empty($role_ids)) return [];
        $result = self::getRoleRelatedPrivileges($role_ids, false, $external, $db);
        return $result ?: [];
    }

    /**
     * 获取用户关联的权限 key_path
     * @param string $user_id
     * @param ExtRabcSingle|null $external
     * @param DbModel $db
     * @return array
     */
    public static function getUserRelatedPrivKeys($user_id = null, ExtRabcSingle $external = null, $db = null)
    {
        if (empty($user_id)) $user_id = TokenFilter::getPayload('user_id');
        if (is_null($external)) $external = new ExtRabcSingle();
        if (is_null($db)) $db = $external->getDb();
        $role_ids = self::getUserRelatedRoles($user_id, false, $external, $db);
        if (empty($role_ids)) return [];
        $result = self::getRoleRelatedPrivileges($role_ids, ['key_path' => null,], $external, $db);
        if (empty($result)) return [];
        return array_column($result, 'key_path');
    }


    /**
     * 获取角色关联的权限
     * @param string|array $role_id 角色id或者角色id数组
     * @param false|array $map 指定只获取权限id还是获取权限多个字段
     *                    参数为 false ,返回一维数组,表示所有相关权限的id
     *                    还可以传入一个键值对数组,键表示 privilege 表的字段名,值表示别名(空值表示不取别名)
     *                    例如:[id=>value,name=>label]
     *                    返回 [[value=>xxxx,name=>xxxx],...] 的格式
     * @param ExtRabcSingle|null $external
     * @param DbModel $db
     * @return array
     */
    public static function getRoleRelatedPrivileges($role_id, $map = false, ExtRabcSingle $external = null, $db = null)
    {
        if (is_null($external)) $external = new ExtRabcSingle();
        if (is_null($db)) $db = $external->getDb();
        if (is_array($map)) {
            $select = [];
            foreach ($map as $field => $alias) {
                if ($alias) {
                    $select[] = "`tb`.`{$field}` as {$alias}";
                } else {
                    $select[] = "`tb`.`{$field}`";
                }
            }
            $sql = $db->select($select)
                ->from($external->relRolePrivTableName() . ' as ta')
                ->leftJoin($external->privilegeTableName() . ' as tb', 'ta.privilege_id=tb.id');
            if (is_array($role_id)) {
                $sql->whereIn('ta.role_id', $role_id);
            } else {
                $sql->whereArray(['ta.role_id' => $role_id]);
            }
            $result = $sql->where('ta.del_time=0')
                ->query();

            return $result ?: [];
        } else {
            $sql = $db->select('privilege_id')
                ->from($external->relRolePrivTableName());
            if (is_array($role_id)) {
                $sql->whereIn('role_id', $role_id);
            } else {
                $sql->whereArray(['role_id' => $role_id]);
            }
            $result = $sql->where('del_time=0')
                ->column();
            return $result ?: [];
        }
    }


    /**
     * 获取用户直接关联的角色
     * @param string $user_id 用户的id
     * @param false|array $map 指定只获取角色id还是获取角色多个字段
     *                    参数为 false ,返回一维数组,表示所有相关角色的id
     *                    还可以传入一个键值对数组,键表示 role表的字段名,值表示别名(空值表示不取别名)
     *                    例如:[id=>value,name=>label]
     *                    返回 [[value=>xxxx,name=>xxxx],...] 的格式
     * @param ExtRabcSingle|null $external
     * @param DbModel $db
     * @return array
     */
    public static function getUserRelatedRoles($user_id = null, $map = false, ExtRabcSingle $external = null, $db = null)
    {
        if (empty($user_id)) $user_id = TokenFilter::getPayload('user_id');
        if (is_null($external)) $external = new ExtRabcSingle();
        if (is_null($db)) $db = $external->getDb();
        if (is_array($map)) {
            $select = [];
            foreach ($map as $field => $alias) {
                if ($alias) {
                    $select[] = "`tb`.`{$field}` as {$alias}";
                } else {
                    $select[] = "`tb`.`{$field}`";
                }
            }
            $result = $db->select($select)
                ->from($external->relUserRoleTableName() . ' as ta')
                ->leftJoin($external->roleTableName() . ' as tb', 'ta.role_id=tb.id')
                ->whereArray(['ta.user_id' => $user_id])
                ->where('ta.del_time=0')
                ->query();

            return $result ?: [];
        } else {
            $result = $db->select('role_id')
                ->from($external->relUserRoleTableName())
                ->whereArray(['user_id' => $user_id])
                ->where('del_time=0')
                ->column();
            return $result ?: [];
        }
    }

    /**
     * 获取用户的所有关联角色,包括下级角色
     * @param string $user_id
     * @param ExtRabcSingle|null $external
     * @param DbModel $db
     * @return array 二维数组[直接关联角色id[],下级角色id[]]
     */
    public static function getUserRelationAllRoles($user_id = null, ExtRabcSingle $external = null, $db = null)
    {
        if (empty($user_id)) $user_id = TokenFilter::getPayload('user_id');
        if (is_null($external)) $external = new ExtRabcSingle();
        if (is_null($db)) $db = $external->getDb();
        $role_ids = self::getUserRelatedRoles($user_id, false, $external, $db);
        if (empty($role_ids)) return [[], []];
        $children_ids = self::getRoleAllChildren($role_ids, false, $external, $db);
        return [$role_ids, $children_ids ?: []];
    }


    /**
     * 返回所有的下级角色
     * @param string|array $role_id 角色id,可以是id数组
     * @param bool $map 指定只获取角色id还是包含层级关系
     *                  true 返回值是二维数组,包括
     *                      [
     *                          [role_id,asc_level,desc_level],
     *                          [role_id,asc_level,desc_level]
     *                          ...
     *                      ]
     *                  false 返回一维数组,[id1,id2,id3.....]
     * @param ExtRabcSingle|null $external
     * @param DbModel $db
     * @return array
     */
    public static function getRoleAllChildren($role_id, $map = false, ExtRabcSingle $external = null, $db = null)
    {
        if (is_null($external)) $external = new ExtRabcSingle();
        if (is_null($db)) $db = $external->getDb();

        if (is_array($map)) {
            $sql = $db->select(['role_id', 'asc_level', 'desc_level'])
                ->from($external->roleMoreTableName());
            if (is_array($role_id)) {
                $sql->whereIn('parent_id', $role_id);
            } else {
                $sql->whereArray(['parent_id' => $role_id]);
            }
            $result = $sql->where('del_time=0')
                ->query();
            return $result ?: [];
        } else {
            $sql = $db->select('role_id')
                ->from($external->roleMoreTableName());
            if (is_array($role_id)) {
                $sql->whereIn('parent_id', $role_id);
            } else {
                $sql->whereArray(['parent_id' => $role_id]);
            }
            $result = $sql->where('del_time=0')
                ->column();
            return $result ?: [];
        }
    }
}