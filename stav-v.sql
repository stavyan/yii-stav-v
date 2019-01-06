DROP TABLE IF EXISTS `sv_admin`;
CREATE TABLE IF NOT EXISTS `sv_admin`(
  `adminid` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `adminuser` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '管理员账号',
  `adminpass` CHAR(32) NOT NULL DEFAULT '' COMMENT '管理员密码',
  `/**
 * @api {post} api/user/add 新增用户
 * @apiVersion 0.3.0
 * @apiName AddUser
 * @apiGroup User
 * @apiPermission none
 *
 * @apiDescription 新增用户
 *
 * @apiParam {String} adminemail AdminEmail of the User.
 *
 * @apiSuccess (success 0) {String} firstname Firstname of the User.
 * @apiSuccess (success 0) {String} lastname  Lastname of the User.
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 0 OK
 *     {
 *       "firstname": "John",
 *       "lastname": "Doe"
 *     }
 *
 * @apiUse CreateUserError
 */` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '管理员邮箱',
  `logintime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '登录时间',
  `loginip` BIGINT NOT NULL DEFAULT '0' COMMENT '登录IP',
  `createtime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY(`adminid`),
  UNIQUE shop_admin_adminuser_adminpass(`adminuser`, `adminpass`),
  UNIQUE shop_admin_adminuser_adminemail(`adminuser`, `adminemail`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `sv_admin`(`adminuser`, `adminpass`, `adminemail`, `createtime`) VALUES(
  'admin', md5('123456'), 'stavyan@qq.com', UNIX_TIMESTAMP()
);

DROP TABLE IF EXISTS `sv_user`;
CREATE TABLE  IF NOT EXISTS `sv_user` (
  `userid` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `username` VARCHAR(32) NOT NULL DEFAULT '',
  `userpass` CHAR(32) NOT NULL DEFAULT '',
  `useremail` VARCHAR(100) NOT NULL DEFAULT '',
  `createtime` INT UNSIGNED NOT NULL default '0',
  UNIQUE shop_user_username_userpass(`username`, `userpass`),
  UNIQUE shop_user_useremail_userpass(`useremail`, `userpass`),
  PRIMARY KEY(`userid`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;
