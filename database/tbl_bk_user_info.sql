DROP TABLE IF EXISTS `tbl_bk_user_info`;

CREATE TABLE IF NOT EXISTS `tbl_bk_user_info`
(
    `user_id`     VARCHAR(64)    NOT NULL,
    `nick_name`   VARCHAR(20)    NULL COMMENT '昵称',
    `avatar`      VARCHAR(500)   NULL COMMENT '头像',
    `gender`      TINYINT        NULL COMMENT '1 男 2女',
    `birthday`    DATE           NULL COMMENT '生日',
    `balance`     DECIMAL(10, 2) NOT NULL COMMENT '余额',
    `scores`      DECIMAL(10, 2) NOT NULL COMMENT '积分',
    `level`       INT            NOT NULL COMMENT '等级',
    `city_id`     INT            NOT NULL COMMENT '地区',
    `description` VARCHAR(1000)  NULL COMMENT '个人简介',
    PRIMARY KEY (`user_id`)
)
    ENGINE = InnoDB
    COMMENT = 'rabc-用户的基本信息';

INSERT INTO `tbl_bk_user_info` (`user_id`, `nick_name`, `gender`, `city_id`, `balance`, `scores`, `level`)
VALUES ('1', '管理员', 0, 100000, 0, 0, 0);