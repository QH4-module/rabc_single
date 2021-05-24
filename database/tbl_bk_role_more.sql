DROP TABLE IF EXISTS `tbl_bk_role_more`;

CREATE TABLE IF NOT EXISTS `tbl_bk_role_more`
(
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `role_id`     VARCHAR(64)  NOT NULL COMMENT '角色',
    `parent_id`   VARCHAR(64)  NOT NULL COMMENT '上级',
    `asc_level`   INT          NOT NULL COMMENT '以父级为基准,用户层数',
    `desc_level`  INT          NOT NULL COMMENT '以用户为基准,父级层数',
    `create_time` BIGINT       NOT NULL COMMENT '',
    `del_time`    BIGINT       NOT NULL,
    PRIMARY KEY (`id`)
)
    ENGINE = InnoDB
    COMMENT = 'rabc-角色表-冗余表';

CREATE INDEX `role_id_index` ON `tbl_bk_role_more` (`role_id` ASC);

CREATE INDEX `parent_id_index` ON `tbl_bk_role_more` (`parent_id` ASC);
