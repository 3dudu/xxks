-- 生成日期: 2013 年 03 月 11 日 10:49

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- 数据库: `iwebshop`
--

-- --------------------------------------------------------

--
-- 表的结构 `{pre}account_log`
--

DROP TABLE IF EXISTS `{pre}account_log`;
CREATE TABLE `{pre}account_log` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `admin_id` int(11) unsigned default '0' COMMENT '管理员ID',
  `user_id` int(11) unsigned default NULL COMMENT '用户id',
  `type` tinyint(1) NOT NULL default '0' COMMENT '0增加,1减少',
  `event` tinyint(3) NOT NULL COMMENT '操作类型，意义请看accountLog类',
  `time` datetime NOT NULL COMMENT '发生时间',
  `amount` decimal(15,2) NOT NULL COMMENT '金额',
  `amount_log` decimal(15,2) NOT NULL COMMENT '每次增减后面的金额记录',
  `note` text COMMENT '备注',
  PRIMARY KEY  (`id`),
  index (`user_id`),
  index (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='账户余额日志表';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}find_password`
--

DROP TABLE IF EXISTS `{pre}find_password`;
CREATE TABLE  `{pre}find_password` (
  `id` INT(11) unsigned NOT NULL auto_increment ,
  `user_id` INT(11) unsigned NOT NULL COMMENT '用户ID',
  `hash` CHAR(32) NOT NULL COMMENT '_hash值',
  `addtime` INT NOT NULL COMMENT '申请找回的时间',
  PRIMARY KEY (`id`) ,
  INDEX (`hash`),
  INDEX (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='找回密码';

-- --------------------------------------------------------


-- --------------------------------------------------------

--
-- 表的结构 `{pre}ad_manage`
--

DROP TABLE IF EXISTS `{pre}ad_manage`;
CREATE TABLE `{pre}ad_manage` (
  `id` int(11) unsigned NOT NULL auto_increment COMMENT '广告ID',
  `name` varchar(50) NOT NULL COMMENT '广告名称',
  `type` tinyint(1) NOT NULL COMMENT '广告类型 1:img 2:flash 3:文字 4:code',
  `position_id` int(11) unsigned default '0' COMMENT '广告位ID',
  `link` varchar(255) default NULL COMMENT '链接地址',
  `order` smallint(5) NOT NULL default '0' COMMENT '排列顺序',
  `start_time` date default NULL COMMENT '开始时间',
  `end_time` date default NULL COMMENT '结束时间',
  `content` text COMMENT '图片、flash路径，文字，code等',
  `description` varchar(255) default NULL COMMENT '描述',
  `goods_cat_id` int(11) unsigned default '0' COMMENT '绑定的商品分类ID',
  `_hash` int(11) unsigned NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY  (`id`,`_hash`),
  index (`position_id`),
  index (`order`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='广告记录表';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}ad_position`
--

DROP TABLE IF EXISTS `{pre}ad_position`;
CREATE TABLE `{pre}ad_position` (
  `id` int(11) unsigned NOT NULL auto_increment COMMENT '广告位ID',
  `name` varchar(30) NOT NULL COMMENT '广告位名称',
  `width` varchar(255) NOT NULL COMMENT '广告位宽度,px或者%',
  `height` varchar(255) NOT NULL COMMENT '广告位高度,px或者%',
  `fashion` tinyint(1) NOT NULL COMMENT '1:轮显;2:随即',
  `status` tinyint(1) NOT NULL default '0' COMMENT '1:开启; 0: 关闭',
  `_hash` int(11) unsigned NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY  (`id`,`_hash`),
  index (`name`,`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='广告位记录表';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}announcement`
--

DROP TABLE IF EXISTS `{pre}announcement`;
CREATE TABLE `{pre}announcement` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL COMMENT '公告标题',
  `content` text COMMENT '公告内容',
  `time` datetime NOT NULL COMMENT '发布时间',
  `keywords` varchar(255) default NULL COMMENT '关键词',
  `description` varchar(255) default NULL COMMENT '描述',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='公告消息表';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}session`
--

DROP TABLE IF EXISTS `{pre}session`;
CREATE TABLE `{pre}session` (
  `id` char(32) NOT NULL COMMENT 'session的唯一id',
  `value` text COMMENT 'session序列化数据',
  `time` datetime NOT NULL COMMENT '存储时间',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='session会话表';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}areas`
--

DROP TABLE IF EXISTS `{pre}areas`;
CREATE TABLE `{pre}areas` (
  `area_id` int(10) unsigned NOT NULL auto_increment,
  `parent_id` int(10) unsigned NOT NULL COMMENT '上一级的id值',
  `area_name` varchar(50) NOT NULL COMMENT '地区名称',
  `sort` int(10) unsigned NOT NULL default '99' COMMENT '排序',
  PRIMARY KEY  (`area_id`),
  index (`area_name`),
  index (`parent_id`),
  index (`sort`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='地区信息';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}banner`
--

DROP TABLE IF EXISTS `{pre}banner`;
CREATE TABLE `{pre}banner` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `order` smallint(5) unsigned NOT NULL COMMENT '排序',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT 'Banner名称',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '链接地址',
  `img` varchar(255) NOT NULL DEFAULT '' COMMENT '图片文件',
  `type` enum('mobile','pc') NOT NULL DEFAULT 'pc' COMMENT '类型,pc:电脑端;mobile:手机端',
  `_hash` int(11) unsigned NOT NULL COMMENT '预留散列字段',
  `seller_id` int(11) unsigned NOT NULL default '0' COMMENT '商家ID',
  PRIMARY KEY (`id`,`_hash`),
  index (`seller_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='幻灯片表';
-- --------------------------------------------------------

--
-- 表的结构 `{pre}search`
--
DROP TABLE IF EXISTS `{pre}search`;
CREATE TABLE `{pre}search` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `keyword` varchar(255) NOT NULL COMMENT '搜索关键字',
  `num` int(11) unsigned NOT NULL default '0' COMMENT '搜索次数',
  PRIMARY KEY  (`id`),
  index (`keyword`(12))
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='搜索关键字';



-- --------------------------------------------------------

--
-- 表的结构 `{pre}email_registry`
--

DROP TABLE IF EXISTS `{pre}email_registry`;
CREATE TABLE `{pre}email_registry` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `email` varchar(80) NOT NULL COMMENT 'Email',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `email` (`email`(15))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Email订阅表';

-- --------------------------------------------------------


--
-- 表的结构 `{pre}guide`
--

DROP TABLE IF EXISTS `{pre}guide`;
CREATE TABLE `{pre}guide` (
  `order` smallint(5) unsigned NOT NULL COMMENT '排序',
  `name` varchar(255) NOT NULL COMMENT '导航名字',
  `link` varchar(255) NOT NULL COMMENT '链接地址',
  `_hash` int(11) unsigned NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY  (`order`,`_hash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='首页导航栏';


INSERT INTO `{pre}guide` (`order`, `name`, `link`) VALUES
(0, '产品官网', 'http://wwj.bthgame.com'),
(1, '后台管理', 'admin/index'),
(2, '商家管理', 'seller/index');

-- --------------------------------------------------------

--
-- 表的结构 `{pre}help`
--

DROP TABLE IF EXISTS `{pre}help`;
CREATE TABLE `{pre}help` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `cat_id` int(11) unsigned default NULL COMMENT '帮助分类，如果为0则代表着是下面的帮助单页',
  `sort` smallint(5) NOT NULL default '99' COMMENT '顺序',
  `name` varchar(50) NOT NULL COMMENT '标题',
  `content` text NOT NULL COMMENT '内容',
  `dateline` int(11) NOT NULL COMMENT '发布时间',
  PRIMARY KEY (`id`),
  index (`cat_id`),
  index (`sort`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='帮助内容';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}help_category`
--

DROP TABLE IF EXISTS `{pre}help_category`;
CREATE TABLE `{pre}help_category` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(10) NOT NULL COMMENT '标题',
  `sort` smallint(5) NOT NULL COMMENT '顺序',
  `position_left` tinyint(1) NOT NULL COMMENT '是否在帮助内容、列表页面的左侧显示',
  `position_foot` tinyint(1) NOT NULL COMMENT '是否在整站页面下方显示',
  PRIMARY KEY  (`id`),
  index (`sort`),
  index (`position_left`),
  index (`position_foot`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='帮助分类';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}keyword`
--

DROP TABLE IF EXISTS `{pre}keyword`;
CREATE TABLE `{pre}keyword` (
  `word` varchar(15) NOT NULL COMMENT '关键词',
  `goods_nums` int(11) NOT NULL default '1' COMMENT '产品数量',
  `hot` tinyint(1) NOT NULL default '0' COMMENT '是否为热门',
  `order` smallint(5) NOT NULL default '99' COMMENT '关键词排序',
  `_hash` int(11) unsigned NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY  (`word`,`_hash`),
  index (`hot`),
  index (`order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='关键词';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}log_error`
--

DROP TABLE IF EXISTS `{pre}log_error`;
CREATE TABLE `{pre}log_error` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `file` varchar(200) NOT NULL COMMENT '文件',
  `line` smallint(5) unsigned NOT NULL COMMENT '出错文件行数',
  `content` varchar(255) NOT NULL COMMENT '内容',
  `datetime` datetime NOT NULL COMMENT '时间',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='错误日志表';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}log_operation`
--

DROP TABLE IF EXISTS `{pre}log_operation`;
CREATE TABLE `{pre}log_operation` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `author` varchar(80) NOT NULL COMMENT '操作人员',
  `action` varchar(200) NOT NULL COMMENT '动作',
  `content` text COMMENT '内容',
  `datetime` datetime NOT NULL COMMENT '时间',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='日志操作记录';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}log_sql`
--

DROP TABLE IF EXISTS `{pre}log_sql`;
CREATE TABLE `{pre}log_sql` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `content` varchar(255) NOT NULL COMMENT '执行的SQL语句',
  `runtime` decimal(15,2) unsigned NOT NULL COMMENT '语句执行时间(秒)',
  `datetime` datetime NOT NULL COMMENT '发生的时间',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='SQL日志记录';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}marketing_sms`
--

DROP TABLE IF EXISTS `{pre}marketing_sms`;
CREATE TABLE IF NOT EXISTS `{pre}marketing_sms` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `content` varchar(255) NOT NULL COMMENT '短信内容',
  `send_nums` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '发送成功数',
  `time` datetime NOT NULL COMMENT '发送时间',
  `rev_info` text COMMENT '收件人信息',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='营销短信';

-- --------------------------------------------------------
-- --------------------------------------------------------


--
-- 表的结构 `{pre}message`
--

DROP TABLE IF EXISTS `{pre}message`;
CREATE TABLE `{pre}message` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL COMMENT '标题',
  `content` text COMMENT '内容',
  `time` datetime NOT NULL COMMENT '发送时间',
  `rev_info` text COMMENT '收件人信息',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='会员消息';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}notify_registry`
--

DROP TABLE IF EXISTS `{pre}notify_registry`;
CREATE TABLE `{pre}notify_registry` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `goods_id` int(11) unsigned NOT NULL COMMENT '商品ID',
  `user_id` int(11) unsigned NOT NULL COMMENT '用户ID',
  `email` varchar(255) default NULL COMMENT 'emaill',
  `mobile` varchar(20) default NULL COMMENT '手机',
  `register_time` datetime NOT NULL COMMENT '登记时间',
  `notify_time` datetime default NULL COMMENT '通知时间',
  `notify_status` tinyint(1) NOT NULL default '0' COMMENT '0未通知1仅邮件通知2仅短信通知3已邮件、短信通知',
  PRIMARY KEY  (`id`),
  index (`goods_id`),
  index (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='到货通知表';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}payment`
--

DROP TABLE IF EXISTS `{pre}payment`;
CREATE TABLE `{pre}payment` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL COMMENT '支付名称',
  `type` tinyint(1) NOT NULL default '1' COMMENT '1:线上、2:线下',
  `class_name` varchar(50) NOT NULL COMMENT '支付类名称',
  `description` text COMMENT '描述',
  `logo` varchar(255) NOT NULL COMMENT '支付方式logo图片路径',
  `status` tinyint(1) NOT NULL default '1' COMMENT '安装状态 0启用 1禁用',
  `order` smallint(5) NOT NULL default '99' COMMENT '排序',
  `note` text COMMENT '支付说明',
  `config_param` text COMMENT '配置参数,json数据对象',
  `client_type` tinyint(1) NOT NULL default '1' COMMENT '1:PC端 2:移动端 3:通用',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='支付方式表';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}point_log`
--

DROP TABLE IF EXISTS `{pre}point_log`;
CREATE TABLE `{pre}point_log` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) unsigned  NOT NULL COMMENT '用户id',
  `datetime` datetime NOT NULL COMMENT '发生时间',
  `value` int(11) NOT NULL COMMENT '积分增减 增加正数 减少负数',
  `intro` varchar(50) NOT NULL COMMENT '积分改动说明',
  PRIMARY KEY  (`id`),
  index (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='积分增减记录表';

-- --------------------------------------------------------


--
-- 表的结构 `{pre}online_recharge`
--

DROP TABLE IF EXISTS `{pre}online_recharge`;
CREATE TABLE `{pre}online_recharge` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) unsigned  NOT NULL COMMENT '用户id',
  `recharge_no` varchar(20) NOT NULL COMMENT '充值单号',
  `account` decimal(15,2) NOT NULL default '0.00' COMMENT '充值金额',
  `time` datetime NOT NULL COMMENT '时间',
  `payment_name` varchar(80) NOT NULL COMMENT '充值方式名称',
  `status` tinyint(1) NOT NULL default '0' COMMENT '充值状态 0:未成功 1:充值成功',
  PRIMARY KEY  (`id`),
  index (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='在线充值表';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}oauth_user`
--

DROP TABLE IF EXISTS `{pre}oauth_user`;
CREATE TABLE `{pre}oauth_user` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `oauth_user_id` varchar(80) NOT NULL COMMENT '第三方平台的用户唯一标识',
  `oauth_id` smallint(5) unsigned NOT NULL COMMENT 'oauth表关联平台id',
  `user_id` int(11) unsigned  NOT NULL COMMENT '系统内部的用户id',
  `datetime` datetime NOT NULL COMMENT '绑定时间',
  `union_id` varchar(100) NOT NULL default '' COMMENT 'union_id参数只针对微信',
  PRIMARY KEY  (`id`),
  index (`oauth_user_id`,`oauth_id`),
  index (`union_id`),
  index (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='oauth开发平台绑定用户表';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}oauth`
--

DROP TABLE IF EXISTS `{pre}oauth`;
CREATE TABLE `{pre}oauth` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `name` varchar(80) NOT NULL COMMENT '名称',
  `config` text default NULL COMMENT '配置信息',
  `file` varchar(80) NOT NULL COMMENT '接口文件名称',
  `description` varchar(80) default NULL COMMENT '描述',
  `is_close` tinyint(1) NOT NULL default '0' COMMENT '是否关闭;0开启,1关闭',
  `logo` varchar(255) default NULL COMMENT 'logo',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='认证方案oauth2.0';

INSERT INTO {pre}oauth VALUES (NULL, '人人网', '', 'renren', '人人网开放平台，申请地址：http://dev.renren.com/website ', 1, 'plugins/oauth/images/renren.gif');
INSERT INTO {pre}oauth VALUES (NULL, 'QQ', '', 'qq', '腾讯开发平台，申请地址：https://connect.qq.com ', 1, 'plugins/oauth/images/qq.gif');
INSERT INTO {pre}oauth VALUES (NULL, '新浪', '', 'sina', '新浪微博的开发平台，申请地址： http://open.weibo.com/connect ', 1, 'plugins/oauth/images/sina.gif');
INSERT INTO {pre}oauth VALUES (NULL, '淘宝', '', 'taobao', '淘宝的开放平台，申请地址：https://open.taobao.com ', 1, 'plugins/oauth/images/taobao.gif');
INSERT INTO {pre}oauth VALUES (NULL, '微信', '', 'wechatOauth', '微信的开放平台，申请地址： https://open.weixin.qq.com ', 1, 'plugins/oauth/images/wechat.png');

-- --------------------------------------------------------


--
-- 表的结构 `{pre}quick_naviga`
--

DROP TABLE IF EXISTS `{pre}quick_naviga`;
CREATE TABLE `{pre}quick_naviga` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `admin_id` int(11) unsigned NOT NULL COMMENT '管理员id',
  `naviga_name` varchar(255) NOT NULL COMMENT '导航名称',
  `url` varchar(255) NOT NULL COMMENT '导航链接',
  `is_del` tinyint(1) NOT NULL default '0' COMMENT '是否删除1为删除',
  PRIMARY KEY  (`id`),
  index (`admin_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='管理员快速导航';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}refer`
--

DROP TABLE IF EXISTS `{pre}refer`;
CREATE TABLE `{pre}refer` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `question` text NOT NULL COMMENT '咨询内容',
  `user_id` int(11) unsigned default NULL COMMENT '咨询人会员ID，非会员为空',
  `goods_id` int(11) unsigned NOT NULL COMMENT '产品ID',
  `answer` text COMMENT '回复内容',
  `admin_id` int(11) unsigned default '0' COMMENT '回复的管理员ID',
  `seller_id` int(11) unsigned default '0' COMMENT '回复的商户ID',
  `status` tinyint(1) default '0' COMMENT '0：待回复 1已回复',
  `time` datetime default NULL COMMENT '咨询时间',
  `reply_time` datetime default NULL COMMENT '回复时间',
  `_hash` int(11) unsigned NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY  (`id`,`_hash`),
  index (`user_id`),
  index (`goods_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='咨询表';

-- --------------------------------------------------------


--
-- 表的结构 `{pre}right`
--

DROP TABLE IF EXISTS `{pre}right`;
CREATE TABLE `{pre}right` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(80) NOT NULL COMMENT '权限名字',
  `right` text COMMENT '权限码(控制器+动作)',
  `is_del` tinyint(1) NOT NULL default '0' COMMENT '删除状态 1删除,0正常',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='权限资源码';

-- --------------------------------------------------------


--
-- 表的结构 `{pre}suggestion`
--

DROP TABLE IF EXISTS `{pre}suggestion`;
CREATE TABLE `{pre}suggestion` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) unsigned  NOT NULL COMMENT '用户ID',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `content` text NOT NULL COMMENT '内容',
  `time` datetime NOT NULL COMMENT '提问时间',
  `admin_id` int(11) unsigned default NULL COMMENT '管理员ID',
  `re_content` text COMMENT '回复内容',
  `re_time` datetime default NULL COMMENT '回复时间',
  `_hash` int(11) unsigned NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY  (`id`,`_hash`),
  index (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='意见箱表';

-- --------------------------------------------------------


--
-- 表的结构 `{pre}member`
--

DROP TABLE IF EXISTS `{pre}member`;
CREATE TABLE `{pre}member` (
  `user_id` int(11) unsigned  NOT NULL COMMENT '用户ID',
  `true_name` varchar(50) default NULL COMMENT '真实姓名',
  `telephone` varchar(50) default NULL COMMENT '联系电话',
  `mobile` varchar(20) default NULL COMMENT '手机',
  `area` varchar(255) default NULL COMMENT '地区',
  `contact_addr` varchar(250) default NULL COMMENT '联系地址',
  `qq` varchar(15) default NULL COMMENT 'QQ',
  `sex` tinyint(1) NOT NULL default '1' COMMENT '性别1男2女',
  `birthday` date default NULL COMMENT '生日',
  `group_id` int(11) default NULL COMMENT '分组',
  `exp` int(11) NOT NULL default '0' COMMENT '经验值',
  `point` int(11) NOT NULL default '0' COMMENT '积分',
  `message_ids` text COMMENT '消息ID',
  `time` datetime default NULL COMMENT '注册日期时间',
  `zip` varchar(10) default NULL COMMENT '邮政编码',
  `status` tinyint(1) NOT NULL default '1' COMMENT '用户状态 1正常状态 2 删除至回收站 3锁定',
  `prop` text default NULL COMMENT '用户拥有的工具',
  `balance` decimal(15,2) NOT NULL default '0.00' COMMENT '用户余额',
  `last_login` datetime default NULL COMMENT '最后一次登录时间',
  `custom` varchar(255) default NULL COMMENT '用户习惯方式,配送和支付方式等信息',
  `email` varchar(255) default NULL COMMENT 'Email',
  `logo` varchar(255) DEFAULT NULL COMMENT '图标',
  `idcode` varchar(20) DEFAULT NULL COMMENT '身份证号码',
  PRIMARY KEY  (`user_id`),
  index (`group_id`),
  index (`mobile`),
  index (`email`),
  index (`status`,`true_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户信息表';

--
-- 表的结构 `{pre}user_group`
--

DROP TABLE IF EXISTS `{pre}user_group`;
CREATE TABLE `{pre}user_group` (
  `id` int(11) unsigned NOT NULL auto_increment COMMENT '用户组ID',
  `group_name` varchar(20) NOT NULL COMMENT '组名',
  `discount` decimal(15,2) NOT NULL default '100' COMMENT '折扣率',
  `minexp` int(11) default NULL COMMENT '最小经验',
  `maxexp` int(11) default NULL COMMENT '最大经验',
  `message_ids` varchar(255) default NULL COMMENT '消息ID',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='用户组';

--
-- 表的结构 `{pre}user`
--

DROP TABLE IF EXISTS `{pre}user`;
CREATE TABLE `{pre}user` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `username` varchar(20) NOT NULL COMMENT '用户名',
  `password` char(32) NOT NULL COMMENT '密码',
  `head_ico` varchar(255) default NULL COMMENT '头像',
  PRIMARY KEY  (`id`),
  UNIQUE KEY (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='用户表';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}admin`
--

DROP TABLE IF EXISTS `{pre}admin`;
CREATE TABLE `{pre}admin` (
  `id` int(11) unsigned NOT NULL auto_increment COMMENT '管理员ID',
  `admin_name` varchar(20) NOT NULL COMMENT '用户名',
  `password` varchar(32) NOT NULL COMMENT '密码',
  `role_id` int(11) unsigned NOT NULL COMMENT '角色ID',
  `create_time` datetime default NULL COMMENT '创建时间',
  `email` varchar(255) default NULL COMMENT 'Email',
  `last_ip` varchar(30) default NULL COMMENT '最后登录IP',
  `last_time` datetime default NULL COMMENT '最后登录时间',
  `is_del` tinyint(1) NOT NULL default '0' COMMENT '删除状态 1删除,0正常',
  `level` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:区县,1:市级',
  `province` int(11) unsigned NOT NULL COMMENT '省ID',
  `city` int(11) unsigned NOT NULL COMMENT '市ID',
  `area` int(11) unsigned NOT NULL COMMENT '区ID',
  PRIMARY KEY  (`id`),
  index (`admin_name`),
  index (`role_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='管理员用户表';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}admin_role`
--

DROP TABLE IF EXISTS `{pre}admin_role`;
CREATE TABLE `{pre}admin_role` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(20) NOT NULL COMMENT '角色名称',
  `rights` text COMMENT '权限',
  `is_del` tinyint(1) NOT NULL default '0' COMMENT '删除状态 1删除,0正常',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='后台角色分组表';


--
-- 表的结构 `{pre}plugin`
--

DROP TABLE IF EXISTS `{pre}plugin`;
CREATE TABLE `{pre}plugin` (
  `id` int(11) unsigned NOT NULL auto_increment COMMENT '插件ID',
  `name` varchar(255) NOT NULL COMMENT '插件名称',
  `class_name` varchar(255) NOT NULL COMMENT '插件类库名称',
  `config_param` text COMMENT '配置参数',
  `description` text COMMENT '描述说明',
  `is_open` tinyint(1) NOT NULL default '1' COMMENT '安装状态 0禁用 1启用',
  `sort` smallint(5) NOT NULL default '99' COMMENT '排序',
  PRIMARY KEY  (`id`),
  UNIQUE KEY (`class_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='插件表';

-- --------------------------------------------------------


--
-- 导出表中的数据 `{pre}delivery`
--
INSERT INTO `{pre}delivery` (`id`, `name`, `description`, `area_groupid`, `firstprice`, `secondprice`, `type`, `first_weight`, `second_weight`, `first_price`, `second_price`, `status`, `sort`, `is_save_price`, `save_rate`, `low_price`, `price_type`, `open_default`, `is_delete`) VALUES
(NULL, '快递', '直接由第三方物流公司配送', 'N;', 'N;', 'N;', 0, 1000, 1000, 20.00, 20.00, 1, 0, 0, 0.00, 0.00, 0, 0, 0);

INSERT INTO `{pre}delivery` (`id`, `name`, `description`, `area_groupid`, `firstprice`, `secondprice`, `type`, `first_weight`, `second_weight`, `first_price`, `second_price`, `status`, `sort`, `is_save_price`, `save_rate`, `low_price`, `price_type`, `open_default`, `is_delete`) VALUES
(NULL, '货到付款', '直接由第三方物流公司配送', 'N;', 'N;', 'N;', 1, 1000, 1000, 20.00, 20.00, 1, 0, 0, 0.00, 0.00, 0, 0, 0);

--
-- 导出表中的数据 `{pre}payment`
--

INSERT INTO `{pre}payment` VALUES (0, '货到付款', 2, 'freight_collect', '货到付款', '/payments/logos/pay_freight_collect.jpg', 1, 99, NULL, NULL,3);
INSERT INTO `{pre}payment` VALUES (NULL, '预存款', 1, 'balance', '预存款是客户在您网站上的虚拟资金帐户，在个人用户中心可以充值获得。', '/payments/logos/pay_deposit.gif',0,99,NULL,NULL,3);
INSERT INTO `{pre}payment` VALUES (NULL, '网银在线', 1, 'chinabank', '网银在线是中国领先的电子支付解决方案提供商之一。 <a href="http://www.chinabank.com.cn/" target="_blank">立即申请</a>', '/payments/logos/pay_chinabank.gif',1, 99, NULL,NULL,1);
INSERT INTO `{pre}payment` VALUES (NULL, '中国银联', 1, 'unionpay', '中国银联unionpay平台接口。费率相对较低，而且支持银行数量最广泛，注意：商户的 <签名证书>和<密码加密证书>都必须放置到商城根目录下的 "/plugins/payments/pay_unionpay/key" 目录中。<a href="https://open.unionpay.com/ajweb/index" target="_blank">立即申请</a>', '/payments/logos/pay_unionpay.png',1, 99, NULL,NULL,1);
INSERT INTO `{pre}payment` VALUES (NULL, '中国银联手机支付', 1, 'wap_unionpay', '中国银联unionpay手机网站支付接口。费率相对较低，而且支持银行数量最广泛，注意：商户的 <签名证书>和<密码加密证书>都必须放置到商城根目录下的 "/plugins/payments/pay_wap_unionpay/key" 目录中。<a href="https://open.unionpay.com/ajweb/index" target="_blank">立即申请</a>', '/payments/logos/pay_wap_unionpay.jpg',1, 99, NULL,NULL,2);
INSERT INTO `{pre}payment` VALUES (NULL, '中国银联B2B企业支付', 1, 'b2b_unionpay', '中国银联unionpay企业对公付款接口。费率相对较低，而且支持银行数量最广泛，注意：商户的 <签名证书>和<密码加密证书>都必须放置到商城根目录下的 "/plugins/payments/pay_b2b_unionpay/key" 目录中。<a href="https://open.unionpay.com/ajweb/index" target="_blank">立即申请</a>', '/payments/logos/pay_b2b_unionpay.jpg',1, 99, NULL,NULL,1);
INSERT INTO `{pre}payment` VALUES (NULL, '腾讯财付通', 1, 'tenpay', '财付通是腾讯公司创办的中国领先的在线支付平台，致力于为互联网用户和企业提供安全、便捷、专业的在线支付服务。 <a href="https://www.tenpay.com/v2/" target="_blank">立即申请</a>','/payments/logos/pay_tenpay.gif', 1, 99, NULL,NULL,1);
INSERT INTO `{pre}payment` VALUES (NULL, '快钱', 1, 'bill99', '快钱是国内领先的独立第三方支付企业，旨在为各类企业及个人提供安全、便捷和保密的支付清算与账务服务。 <a href="https://www.99bill.com/" target="_blank">立即申请</a>', '/payments/logos/pay_99bill.gif', 1, 99, NULL,NULL,1);
INSERT INTO `{pre}payment` VALUES (NULL, '快钱手机支付', 1, 'wap_bill99', '快钱是国内领先的独立第三方支付企业，旨在为各类企业及个人提供安全、便捷和保密的支付清算与账务服务。 <a href="https://www.99bill.com/" target="_blank">立即申请</a>', '/payments/logos/pay_wap_99bill.jpg', 1, 99, NULL,NULL,2);
INSERT INTO `{pre}payment` VALUES (NULL, '支付宝即时到帐', 1, 'direct_alipay', '即时到帐支付方式，买家的交易资金直接打入卖家支付宝账户，快速回笼交易资金。 <a href="http://www.alipay.com/" target="_blank">立即申请</a>', '/payments/logos/pay_alipay.gif', 1, 99, NULL,NULL,1);
INSERT INTO `{pre}payment` VALUES (NULL, '支付宝手机支付', 1, 'wap_alipay', '支付宝的手机网站支付方式。需要企业账号单独签约设置密钥。不能与电脑版本的支付宝混用。<a href="http://www.alipay.com/" target="_blank">立即申请</a>', '/payments/logos/pay_wap_alipay.png', 1, 99, NULL,NULL,2);
INSERT INTO `{pre}payment` VALUES (NULL, '贝宝', 1, 'paypal', '是全球最大的在线支付平台，同时也是目前全球贸易网上支付标准。<a href="https://www.paypal-biz.com/" target="_blank">立即申请</a>', '/payments/logos/pay_paypal.gif', 1, 99, NULL,NULL,1);
INSERT INTO `{pre}payment` VALUES (NULL, '微信公众号支付', 1, 'wap_wechat', '微信公众号里商城专用支付接口。必须在微信客户端中使用<a href="https://mp.weixin.qq.com/cgi-bin/registermidpage?action=index" target="_blank">立即申请</a>', '/payments/logos/pay_wap_wechat.png', 1, 99, NULL,NULL,2);
INSERT INTO `{pre}payment` VALUES (NULL, '微信扫码支付', 1, 'scan_wechat', '微信二维码支付接口，适合PC电脑支付<a href="https://mp.weixin.qq.com/cgi-bin/registermidpage?action=index" target="_blank">立即申请</a>', '/payments/logos/pay_scan_wechat.gif', 1, 99, NULL,NULL,1);
INSERT INTO `{pre}payment` VALUES (NULL, '微信APP支付', 1, 'app_wechat', '微信APP支付接口，必须有官方打包的独立APP才可以使用此支付方式。<a href="https://open.weixin.qq.com/" target="_blank">立即申请</a>', '/payments/logos/pay_app_wechat.png', 1, 99, NULL,NULL,2);
INSERT INTO `{pre}payment` VALUES (NULL, '微信H5支付', 1, 'h5_wechat', '微信H5支付接口，去微信公众平台申请。<a href="https://mp.weixin.qq.com/cgi-bin/registermidpage?action=index" target="_blank">立即申请</a>', '/payments/logos/pay_h5_wechat.jpg', 1, 99, NULL,NULL,2);
INSERT INTO `{pre}payment` VALUES (NULL, '微信小程序支付', 1, 'mini_wechat', '微信小程序支付接口，去微信公众平台申请。<a href="https://mp.weixin.qq.com/cgi-bin/registermidpage?action=index" target="_blank">立即申请</a>', '/payments/logos/pay_mini_wechat.png', 1, 99, NULL,NULL,2);
INSERT INTO `{pre}payment` VALUES (NULL, '线下转账', 2, 'offline', '线下转账结算，通过银行柜台，电汇等方式付款', '/payments/logos/pay_offline.gif', 1, 99, NULL, 0.00, 0, NULL,3);
INSERT INTO `{pre}payment` VALUES (NULL, '偶可贝', 1, 'allpay', '偶可贝跨境在线收款服务，支持支付宝，财付通，银联 <a href="https://www.allpayx.com" target="_blank">立即申请</a>', '/payments/logos/pay_allpay.jpg', 1, 99, NULL,NULL,1);

--
-- 建立外键关系
--
ALTER TABLE `{pre}notify_registry` ADD foreign key(user_id) references `{pre}user`(id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `{pre}quick_naviga` ADD foreign key(admin_id) references `{pre}admin`(id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `{pre}oauth_user` ADD foreign key(user_id) references `{pre}user`(id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `{pre}point_log` ADD foreign key(user_id) references `{pre}user`(id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `{pre}member` ADD foreign key(user_id) references `{pre}user`(id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `{pre}help` ADD foreign key(cat_id) references `{pre}help_category`(id) on delete SET NULL on update SET NULL;;
ALTER TABLE `{pre}find_password` ADD foreign key(user_id) references `{pre}user`(id) on delete cascade on update cascade;
ALTER TABLE `{pre}account_log` ADD foreign key(user_id) references `{pre}user`(id) on delete SET NULL on update SET NULL;
