QH4框架扩展模块-权限管理模块-简单版本

### 依赖
该模块依赖于城市模块,主要是将 user_info 表的 `city_id` 字段进行转换,不需要这个功能可以删除对应行 (models/user/detail 的54,59,65行)
```php
composer require qh4module/city
```

#### 功能
这个是简单版本的权限管理模块,只有3层模型,包括用户、角色、权限。

对应的有高级版本，有4层模型，包括用户、角色、权限、部门。

关于高级版本，在笔者写文档时，还在开发中，包名为 rabc_adv，在你阅读文档时，可能已经开发完成

### 简述
* 用户: 后台管理账户,可以登录后台

* 角色: 中间层,用来关联用户和权限

* 权限(为了和权限管理模块的这个`权限`做区分,有时候也会写作权限资源): 实际操作或展示信息,包括菜单和页面上的按钮或显示信息等

用户与角色是多对多的关系,即: 一个用户可以有多个角色,一个角色可以分配给多个用户

角色与资源是多对多的关系,即: 一个角色可以有多种权限资源, 一种权限资源可以分配多个角色

用户之间不存在继承关系,用户的关系通过角色推断出来,用户所拥有的权限也需要通过角色来推断。用户多个角色之间权限取交集

角色是单继承关系,即一个角色有且只有一个上级

权限资源是单继承关系,即一个权限资源有且只有一个上级


### api列表
```php
/**
 * 获取权限的级联数据树
 * @return array
 */
public function actionPrivCascaderData()
```

```php
/**
 * 获取角色的级联数据树
 * @return array
 */
public function actionRoleCascaderData()
```

```php
/**
 * 获取权限资源列表,返回数据树
 * @return array
 */
public function actionPrivilegeIndex()
```

```php
/**
 * 新增一条权限资源
 * @return array
 */
public function actionPrivilegeCreate()
```

```php
/**
 * 获取权限资源详情
 * @return array
 */
public function actionPrivilegeDetail()
```

```php
/**
 * 更新权限资源
 * @return array
 */
public function actionPrivilegeUpdate()
```

```php
/**
 * 批量删除权限资源
 * @return array
 */
public function actionPrivilegeDelete()
```

```php
/**
 * 获取角色列表,返回数据树
 * @return array
 */
public function actionRoleIndex()
```

```php
/**
 * 新增一个角色
 * @return array
 */
public function actionRoleCreate()
```

```php
/**
 * 更新角色
 * @return array
 */
public function actionRoleUpdate()
```

```php
/**
 * 获取角色详情
 * @return array
 */
public function actionRoleDetail()
```

```php
/**
 * 批量删除角色
 * @return array
 */
public function actionRoleDelete()
```

```php
/**
 * 分页获取用户数据
 * @return array [total,list,page,limit]
 */
public function actionUserIndex()
```

```php
/**
 * 新增一个用户
 * @return array
 */
public function actionUserCreate()
```

```php
/**
 * 更新用户信息
 * @return array
 */
public function actionUserUpdate()
```

```php
/**
 * 获取用户详情
 * @return array
 */
public function actionUserDetail()
```

```php
/**
 * 批量删除用户
 * @return array
 */
public function actionUserDelete()
```

### 方法列表
```php
/**
 * 获取指定用户是否是管理员
 * @param string $user_id
 * @param External|null $external
 * @param DbModel $db
 * @return bool
 */
public static function is_administrator($user_id=null,External $external=null,$db = null)
```

```php
/**
 * 获取所有的下级用户,不包括自己
 * @param string $user_id
 * @param ExtRabcSingle|null $external
 * @param DbModel $db
 * @return array
 */
public static function getUserAllChildren($user_id = null, ExtRabcSingle $external = null, $db = null)
```

```php
/**
 * 获取用户关联的所有权限
 * @param string $user_id
 * @param ExtRabcSingle|null $external
 * @param DbModel $db
 * @return array
 */
public static function getUserRelatedPrivileges($user_id = null, ExtRabcSingle $external = null, $db = null)
```

```php
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
```

```php
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
```

```php
/**
 * 获取用户的所有关联角色,包括下级角色
 * @param string $user_id
 * @param ExtRabcSingle|null $external
 * @param DbModel $db
 * @return array 二维数组[直接关联角色id[],下级角色id[]]
 */
public static function getUserRelationAllRoles($user_id = null, ExtRabcSingle $external = null, $db = null)
```

```php
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
```