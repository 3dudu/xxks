
--
-- 表的结构 `{pre}relation`
--

DROP TABLE IF EXISTS `{pre}relation`;
CREATE TABLE `{pre}relation` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `goods_id` int(11) unsigned NOT NULL COMMENT '商品ID',
  `article_id` int(11) unsigned NOT NULL COMMENT '文章ID',
  PRIMARY KEY  (`id`),
  index (`goods_id`),
  index (`article_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='文章商品关系表';

-- --------------------------------------------------------


--
-- 表的结构 `{pre}attribute`
--

DROP TABLE IF EXISTS `{pre}attribute`;
CREATE TABLE `{pre}attribute` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `model_id` int(11) unsigned default NULL COMMENT '模型ID',
  `type` tinyint(1) default NULL COMMENT '输入控件的类型,1:单选,2:复选,3:下拉,4:输入框',
  `name` varchar(50) default NULL COMMENT '名称',
  `value` text COMMENT '属性值(逗号分隔)',
  `search` tinyint(1) NOT NULL default '0' COMMENT '是否支持搜索0不支持1支持',
  PRIMARY KEY  (`id`),
  index (`model_id`,`search`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='属性表';

-- --------------------------------------------------------


--
-- 表的结构 `{pre}brand`
--

DROP TABLE IF EXISTS `{pre}brand`;
CREATE TABLE `{pre}brand` (
  `id` int(11) unsigned NOT NULL auto_increment COMMENT '品牌ID',
  `name` varchar(255) NOT NULL COMMENT '品牌名称',
  `logo` varchar(255) default NULL COMMENT 'logo地址',
  `url` varchar(255) default NULL COMMENT '网址',
  `description` text COMMENT '描述',
  `sort` smallint(5) NOT NULL default '0' COMMENT '排序',
  `category_ids` varchar(255) default NULL COMMENT '品牌分类,逗号分割id',
  `_hash` int(11) unsigned NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY  (`id`,`_hash`),
  index (`sort`),
  index (`category_ids`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='品牌表';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}brand_category`
--

DROP TABLE IF EXISTS `{pre}brand_category`;
CREATE TABLE `{pre}brand_category` (
  `id` int(11) unsigned NOT NULL auto_increment COMMENT '分类ID',
  `name` varchar(255) NOT NULL COMMENT '分类名称',
  `goods_category_id` int(11) unsigned not null default '0' comment '商品分类ID',
  `_hash` int(11) unsigned NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY  (`id`,`_hash`),
  index (`goods_category_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='品牌分类表';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}category_extend`
--

DROP TABLE IF EXISTS `{pre}category_extend`;
CREATE TABLE `{pre}category_extend` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `goods_id` int(11) unsigned NOT NULL COMMENT '商品ID',
  `category_id` int(11) unsigned NOT NULL COMMENT '商品分类ID',
  PRIMARY KEY  (`id`),
  index (`goods_id`),
  index (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品与其分类关系表';


-- --------------------------------------------------------
--
-- 表的结构 `{pre}category_extend_seller`
--

DROP TABLE IF EXISTS `{pre}category_extend_seller`;
CREATE TABLE `{pre}category_extend_seller` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `goods_id` int(11) unsigned NOT NULL COMMENT '商品ID',
  `category_id` int(11) unsigned NOT NULL COMMENT '商品分类ID',
  PRIMARY KEY  (`id`),
  index (`goods_id`),
  index (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商家店内商品分类与商品关系表';

-- --------------------------------------------------------
--
-- 表的结构 `{pre}category_rate`
--

DROP TABLE IF EXISTS `{pre}category_rate`;
CREATE TABLE `{pre}category_rate` (
  `category_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商品分类ID',
  `category_rate` decimal(15,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '商品分类手续费',
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品分类手续费设置表';

----------------------------------------------------------

--
-- 表的结构 `{pre}category_seller`
--

DROP TABLE IF EXISTS `{pre}category_seller`;
CREATE TABLE `{pre}category_seller` (
  `id` int(11) unsigned NOT NULL auto_increment COMMENT '分类ID',
  `name` varchar(50) NOT NULL COMMENT '分类名称',
  `parent_id` int(11) unsigned NOT NULL COMMENT '父分类ID',
  `sort` smallint(5) NOT NULL default '0' COMMENT '排序',
  `keywords` varchar(255) default NULL COMMENT 'SEO关键词和检索关键词',
  `descript` varchar(255) default NULL COMMENT 'SEO描述',
  `title` varchar(255) default NULL COMMENT 'SEO标题title',
  `seller_id` int(11) unsigned NOT NULL COMMENT '商家ID',
  PRIMARY KEY  (`id`),
  index (`parent_id`),
  index (`sort`),
  index (`seller_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='商家店内商品分类表';

-- --------------------------------------------------------
--
-- 表的结构 `{pre}collection_doc`
--

DROP TABLE IF EXISTS `{pre}collection_doc`;
CREATE TABLE `{pre}collection_doc` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `order_id` int(11) unsigned NOT NULL COMMENT '订单号',
  `user_id` int(11) unsigned NOT NULL COMMENT '用户ID',
  `amount` decimal(15,2) NOT NULL default '0.00' COMMENT '金额',
  `time` datetime NOT NULL COMMENT '时间',
  `payment_id` int(11) unsigned NOT NULL COMMENT '支付方式ID',
  `admin_id` int(11) unsigned default NULL COMMENT '管理员id',
  `pay_status` tinyint(1) NOT NULL default '0' COMMENT '支付状态，0:准备，1:支付成功',
  `note` text COMMENT '收款备注',
  `if_del` tinyint(1) NOT NULL default '0' COMMENT '0:未删除 1:删除',
  `_hash` int(11) unsigned NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY  (`id`,`_hash`),
  index (`order_id`),
  index (`user_id`),
  index (`if_del`),
  index (`payment_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='收款单';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}commend_goods`
--

DROP TABLE IF EXISTS `{pre}commend_goods`;
CREATE TABLE `{pre}commend_goods` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `commend_id` int(11) unsigned NOT NULL COMMENT '推荐类型ID 1:最新商品 2:特价商品 3:热卖排行 4:推荐商品',
  `goods_id` int(11) unsigned NOT NULL COMMENT '商品ID',
  `_hash` int(11) unsigned NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY  (`id`,`_hash`),
  index (`goods_id`),
  index (`commend_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='推荐类商品';

-- --------------------------------------------------------



--
-- 表的结构 `{pre}comment`
--

DROP TABLE IF EXISTS `{pre}comment`;
CREATE TABLE `{pre}comment` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `goods_id` int(11) unsigned NOT NULL COMMENT '商品ID',
  `order_no` varchar(20) NOT NULL COMMENT '订单编号',
  `user_id` int(11) unsigned NOT NULL COMMENT '用户ID',
  `time` datetime NOT NULL COMMENT '购买时间',
  `comment_time` date NOT NULL COMMENT '评论时间',
  `contents` text COMMENT '评论内容',
  `recontents` text COMMENT '回复评论内容',
  `recomment_time` date NOT NULL COMMENT '回复评论时间',
  `point` tinyint(1) NOT NULL default '0' COMMENT '评论的分数',
  `status` tinyint(1) NOT NULL default '0' COMMENT '评论状态：0：未评论 1:已评论',
  `seller_id` int(11) unsigned NOT NULL default '0' COMMENT '商家ID',
  `order_goods_id` int(11) unsigned default NULL COMMENT '订单商品表中的ID',
  `img_list` text COMMENT '评价图片',
  `_hash` int(11) unsigned NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY  (`id`,`_hash`),
  index (`goods_id`),
  index (`order_goods_id`),
  index (`user_id`,`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='商品评论表';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}delivery`
--

DROP TABLE IF EXISTS `{pre}delivery`;
CREATE TABLE `{pre}delivery` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(50) default NULL COMMENT '快递名称',
  `description` varchar(50) default NULL COMMENT '快递描述',
  `area_groupid` text COMMENT '配送区域id',
  `firstprice` text COMMENT '配送地址对应的首重价格',
  `secondprice` text COMMENT '配送地区对应的续重价格',
  `type` tinyint(1) NOT NULL default '0' COMMENT '配送类型 0先付款后发货 1先发货后付款',
  `first_weight` int(11) unsigned NOT NULL COMMENT '首重重量(克)',
  `second_weight` int(11) unsigned NOT NULL COMMENT '续重重量(克)',
  `first_price` decimal(15,2) NOT NULL default '0.00' COMMENT '首重价格',
  `second_price` decimal(15,2) NOT NULL default '0.00' COMMENT '续重价格',
  `status` tinyint(1) NOT NULL default '1' COMMENT '开启状态 1启用 0关闭',
  `sort` smallint(5) NOT NULL default '99' COMMENT '排序',
  `is_save_price` tinyint(1) NOT NULL default '0' COMMENT '是否支持物流保价 1支持保价 0  不支持保价',
  `save_rate` decimal(15,2) NOT NULL default '0.00' COMMENT '保价费率',
  `low_price` decimal(15,2) NOT NULL default '0.00' COMMENT '最低保价',
  `price_type` tinyint(1) NOT NULL default '0' COMMENT '费用类型 0统一设置 1指定地区费用',
  `open_default` tinyint(1) NOT NULL default '1' COMMENT '其他地区是否启用默认费用 1启用 0 不启用',
  `is_delete` tinyint(1) NOT NULL default '0' COMMENT '是否删除 0:未删除 1:删除',
  PRIMARY KEY  (`id`),
  index (`status`),
  index (`sort`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='配送方式表';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}delivery_extend`
--

DROP TABLE IF EXISTS `{pre}delivery_extend`;
CREATE TABLE `{pre}delivery_extend` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `delivery_id` int(11) unsigned NOT NULL COMMENT '配送方式关联ID',
  `area_groupid` text COMMENT '单独配置地区id',
  `firstprice` text COMMENT '单独配置地区对应的首重价格',
  `secondprice` text COMMENT '单独配置地区对应的续重价格',
  `first_weight` int(11) unsigned NOT NULL COMMENT '首重重量(克)',
  `second_weight` int(11) unsigned NOT NULL COMMENT '续重重量(克)',
  `first_price` decimal(15,2) NOT NULL default '0.00' COMMENT '默认首重价格',
  `second_price` decimal(15,2) NOT NULL default '0.00' COMMENT '默认续重价格',
  `is_save_price` tinyint(1) NOT NULL default '0' COMMENT '是否支持物流保价 1支持保价 0  不支持保价',
  `save_rate` decimal(15,2) NOT NULL default '0.00' COMMENT '保价费率',
  `low_price` decimal(15,2) NOT NULL default '0.00' COMMENT '最低保价',
  `price_type` tinyint(1) NOT NULL default '0' COMMENT '费用类型 0统一设置 1指定地区费用',
  `open_default` tinyint(1) NOT NULL default '1' COMMENT '其他地区是否启用默认费用 1启用 0 不启用',
  `seller_id` int(11) unsigned NOT NULL COMMENT '商家ID',
  PRIMARY KEY  (`id`),
  index (`delivery_id`),
  index (`seller_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='商家配送方式扩展表';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}delivery_doc`
--

DROP TABLE IF EXISTS `{pre}delivery_doc`;
CREATE TABLE `{pre}delivery_doc` (
  `id` int(11) unsigned NOT NULL auto_increment COMMENT '发货单ID',
  `order_id` int(11) unsigned NOT NULL COMMENT '订单ID',
  `user_id` int(11) unsigned NOT NULL COMMENT '用户ID',
  `admin_id` int(11) unsigned default '0' COMMENT '管理员ID',
  `seller_id` int(11) unsigned default '0' COMMENT '商户ID',
  `name` varchar(255) NOT NULL COMMENT '收货人',
  `postcode` varchar(6) default NULL COMMENT '邮编',
  `telphone` varchar(20) default NULL COMMENT '联系电话',
  `country` int(11) unsigned default NULL COMMENT '国ID',
  `province` int(11) unsigned NOT NULL COMMENT '省ID',
  `city` int(11) unsigned NOT NULL COMMENT '市ID',
  `area` int(11) unsigned NOT NULL COMMENT '区ID',
  `address` varchar(250) NOT NULL COMMENT '收货地址',
  `mobile` varchar(20) default NULL COMMENT '手机',
  `time` datetime NOT NULL COMMENT '创建时间',
  `freight` decimal(15,2) NOT NULL default '0.00' COMMENT '运费',
  `delivery_code` varchar(255) NOT NULL COMMENT '物流单号',
  `delivery_type` int(11) NOT NULL COMMENT '物流方式',
  `note` text COMMENT '管理员添加的备注信息',
  `if_del` tinyint(1) NOT NULL default '0' COMMENT '0:未删除 1:已删除',
  `freight_id` int(11) unsigned default NULL COMMENT '货运公司ID',
  `express_template` mediumtext default NULL COMMENT '快递单模板',
  `_hash` int(11) unsigned NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY  (`id`,`_hash`),
  index (`order_id`),
  index (`user_id`),
  index (`delivery_code`),
  index (`freight_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='发货单';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}discussion`
--

DROP TABLE IF EXISTS `{pre}discussion`;
CREATE TABLE `{pre}discussion` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `goods_id` int(11) unsigned NOT NULL COMMENT '商品ID',
  `user_id` int(11) unsigned  NOT NULL COMMENT '用户ID',
  `time` datetime NOT NULL COMMENT '评论时间',
  `contents` text COMMENT '评论内容',
  `is_check` tinyint(1) NOT NULL default '0' COMMENT '审核状态,0未审核 1已审核',
  `_hash` int(11) unsigned NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY  (`id`,`_hash`),
  index (`user_id`),
  index (`goods_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='商品讨论表';



--
-- 表的结构 `{pre}favorite`
--

DROP TABLE IF EXISTS `{pre}favorite`;
CREATE TABLE `{pre}favorite` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) unsigned  NOT NULL COMMENT '用户ID',
  `goods_id` int(11) unsigned NOT NULL COMMENT '商品ID',
  `time` datetime NOT NULL COMMENT '收藏时间',
  `summary` varchar(255) default NULL COMMENT '备注',
  `cat_id` int(11) unsigned NOT NULL COMMENT '商品分类',
  PRIMARY KEY  (`id`),
  index (`user_id`),
  index (`cat_id`),
  index (`goods_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='收藏夹表';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}goods_attribute`
--

DROP TABLE IF EXISTS `{pre}goods_attribute`;
CREATE TABLE `{pre}goods_attribute` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `goods_id` int(11) unsigned NOT NULL COMMENT '商品ID',
  `attribute_id` int(11) unsigned default NULL COMMENT '属性ID',
  `attribute_value` varchar(255) default NULL COMMENT '属性值',
  `model_id` int(11) unsigned default NULL COMMENT '模型ID',
  `order` smallint(5) NOT NULL default '99' COMMENT '排序',
  PRIMARY KEY  (`id`),
  index (`goods_id`),
  index (`attribute_id`,`attribute_value`),
  index (`order`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='属性值表';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}goods_car`
--

DROP TABLE IF EXISTS `{pre}goods_car`;
CREATE TABLE `{pre}goods_car` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) unsigned NOT NULL COMMENT '用户ID',
  `content` text COMMENT '购物内容',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `unselected` TEXT COMMENT '未选择结算的商品信息',
  PRIMARY KEY  (`id`),
  index (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='购物车';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}goods_photo`
--

DROP TABLE IF EXISTS `{pre}goods_photo`;
CREATE TABLE `{pre}goods_photo` (
  `id` char(32) NOT NULL COMMENT '图片的md5值',
  `img` varchar(255) default NULL COMMENT '原始图片路径',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='图片表';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}goods_photo_relation`
--

DROP TABLE IF EXISTS `{pre}goods_photo_relation`;
CREATE TABLE `{pre}goods_photo_relation` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `goods_id` int(11) unsigned NOT NULL COMMENT '商品ID',
  `photo_id` char(32) NOT NULL default '' COMMENT '图片ID,图片的md5值',
  PRIMARY KEY  (`id`),
  index (`goods_id`),
  index (`photo_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='相册商品关系表';

----------------------------------------------------------

--
-- 表的结构 `{pre}goods_rate`
--

DROP TABLE IF EXISTS `{pre}goods_rate`;
CREATE TABLE `{pre}goods_rate` (
  `goods_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商品ID',
  `goods_rate` decimal(15,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '单品手续费',
  PRIMARY KEY (`goods_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='单个商品手续费设置表';

----------------------------------------------------------

--
-- 表的结构 `{pre}group_price`
--

DROP TABLE IF EXISTS `{pre}group_price`;
CREATE TABLE `{pre}group_price` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `goods_id` int(11) unsigned NOT NULL COMMENT '产品ID',
  `product_id` int(11) unsigned default NULL COMMENT '货品ID',
  `group_id` int(11) unsigned NOT NULL COMMENT '用户组ID',
  `price` decimal(15,2) NOT NULL default '0.00' COMMENT '价格',
  PRIMARY KEY  (`id`),
  index (`goods_id`),
  index (`group_id`),
  index (`product_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='记录某件商品对于某组会员的价格关系表，优先权大于组设定的折扣率';

-- --------------------------------------------------------


--
-- 表的结构 `{pre}merch_ship_info`
--

DROP TABLE IF EXISTS `{pre}merch_ship_info`;
CREATE TABLE `{pre}merch_ship_info` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `ship_name` varchar(255) NOT NULL COMMENT '发货点名称',
  `ship_user_name` varchar(255) NOT NULL COMMENT '发货人姓名',
  `sex` tinyint(1) NOT NULL default '0' COMMENT '性别 0:女 1:男',
  `country` int(11) default NULL COMMENT '国id',
  `province` int(11) NOT NULL COMMENT '省id',
  `city` int(11) NOT NULL COMMENT '市id',
  `area` int(11) NOT NULL COMMENT '地区id',
  `postcode` varchar(6) default NULL COMMENT '邮编',
  `address` varchar(255) NOT NULL COMMENT '具体地址',
  `mobile` varchar(20) NOT NULL COMMENT '手机',
  `telphone` varchar(20) default NULL COMMENT '电话',
  `is_default` tinyint(1) NOT NULL default '0' COMMENT '1为默认地址，0则不是',
  `note` text COMMENT '备注',
  `addtime` datetime NOT NULL COMMENT '保存时间',
  `is_del` tinyint(1) NOT NULL default '0' COMMENT '1为删除，0为未删除',
  `seller_id` int(11) unsigned  NOT NULL COMMENT '商家ID',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='商家发货点信息';

-- --------------------------------------------------------


--
-- 表的结构 `{pre}order_goods`
--

DROP TABLE IF EXISTS `{pre}order_goods`;
CREATE TABLE `{pre}order_goods` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `order_id` int(11) unsigned NOT NULL COMMENT '订单ID',
  `goods_id` int(11) unsigned NOT NULL COMMENT '商品ID',
  `img` varchar(255) NOT NULL COMMENT '商品图片',
  `product_id` int(11) unsigned default '0' COMMENT '货品ID',
  `goods_price` decimal(15,2) NOT NULL default '0.00' COMMENT '商品原价',
  `real_price` decimal(15,2) NOT NULL default '0.00' COMMENT '实付金额',
  `goods_nums` int(11) NOT NULL default '1' COMMENT '商品数量',
  `goods_weight` decimal(15,2) NOT NULL default '0' COMMENT '重量',
  `goods_array` text COMMENT '商品和货品名称name和规格value串json数据格式',
  `is_send` tinyint(1) NOT NULL default '0' COMMENT '是否已发货 0:未发货;1:已发货;2:已经退款',
  `delivery_id` int(11) NOT NULL default '0' COMMENT '配送单ID',
  `seller_id` int(11) unsigned NOT NULL default '0' COMMENT '商家ID',
  PRIMARY KEY  (`id`),
  index (`goods_id`),
  index (`order_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='订单商品表';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}order_goods_servicefee`
--
DROP TABLE IF EXISTS `{pre}order_goods_servicefee`;
CREATE TABLE `{pre}order_goods_servicefee` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` int(11) UNSIGNED NOT NULL COMMENT '订单ID',
  `order_goods_id` int(11) UNSIGNED NOT NULL COMMENT '订单商品ID',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '手续费类型 0:默认;1:单品;2:分类',
  `rate` decimal(15,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '手续费率',
  `discount` decimal(15,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '商户结算折扣率',
  `amount` decimal(15,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '手续费总额',
  PRIMARY KEY  (`id`),
  index (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单商品手续费表';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}order_log`
--

DROP TABLE IF EXISTS `{pre}order_log`;
CREATE TABLE `{pre}order_log` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `order_id` int(11) unsigned default NULL COMMENT '订单id',
  `user` varchar(20) default NULL COMMENT '操作人：顾客或admin或seller',
  `action` varchar(20) default NULL COMMENT '动作',
  `addtime` datetime default NULL COMMENT '添加时间',
  `result` varchar(10) default NULL COMMENT '操作的结果',
  `note` varchar(100) default NULL COMMENT '备注',
  PRIMARY KEY  (`id`),
  index (`order_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='订单日志表';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}order_delivery_trace`
--

DROP TABLE IF EXISTS `{pre}order_delivery_trace`;
CREATE TABLE `{pre}order_delivery_trace` (
  `delivery_code` varchar(30) NOT NULL COMMENT '快递单号',
  `content` text COMMENT '物流跟踪信息',
  PRIMARY KEY  (`delivery_code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='订单物流跟踪表';

-- --------------------------------------------------------


--
-- 表的结构 `{pre}products`
--

DROP TABLE IF EXISTS `{pre}products`;
CREATE TABLE `{pre}products` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `goods_id` int(11) unsigned NOT NULL COMMENT '商品ID',
  `products_no` varchar(20) NOT NULL COMMENT '货品的货号(以商品的货号加横线加数字组成)',
  `spec_array` text COMMENT 'json规格数据',
  `store_nums` int(11) NOT NULL default '0' COMMENT '库存',
  `market_price` decimal(15,2) NOT NULL default '0.00' COMMENT '市场价格',
  `sell_price` decimal(15,2) NOT NULL default '0.00' COMMENT '销售价格',
  `cost_price` decimal(15,2) NOT NULL default '0.00' COMMENT '成本价格',
  `weight` decimal(15,2) NOT NULL default '0.00' COMMENT '重量',
  PRIMARY KEY  (`id`),
  index (`goods_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='货品表';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}promotion`
--

DROP TABLE IF EXISTS `{pre}promotion`;
CREATE TABLE `{pre}promotion` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `start_time` datetime NOT NULL COMMENT '开始时间',
  `end_time` datetime NOT NULL COMMENT '结束时间',
  `sort` smallint(5) NOT NULL COMMENT '顺序',
  `condition` text NOT NULL COMMENT '活动生效条件 当type=0<促销规则消费额度>,当type=1<限时抢购商品ID>,type=2<特价商品分类ID>,type=3<特价商品ID>,type=4<特价商品品牌ID>,type=5<无意义>',
  `type` tinyint(1) NOT NULL default '0' COMMENT '活动类型 0:购物车促销规则 1:商品限时抢购 2:商品分类特价 3:商品单品特价 4:商品品牌特价 5:新用户注册促销规则',
  `award_value` varchar(255) default NULL COMMENT '奖励值 type=0,5<奖励值>,type=1<抢购价格>,type=2,3,4<特价折扣>',
  `name` varchar(20) NOT NULL COMMENT '活动名称',
  `intro` text COMMENT '活动介绍',
  `award_type` tinyint(1) NOT NULL default '0' COMMENT '奖励方式:0商品限时抢购 1减金额 2奖励折扣 3赠送积分 4赠送优惠券 5赠送赠品 6免运费 7商品特价 8赠送经验',
  `is_close` tinyint(1) NOT NULL default '0' COMMENT '是否关闭 0:否 1:是',
  `user_group` text COMMENT '允许参与活动的用户组,all表示所有用户组',
  `seller_id` int(11) unsigned NOT NULL default '0' COMMENT '商家ID',
  `_hash` int(11) unsigned NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY  (`id`,`_hash`),
  index (`type`,`seller_id`),
  index (`start_time`,`end_time`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='记录促销活动的表';

-- --------------------------------------------------------


--
-- 表的结构 `{pre}prop`
--

DROP TABLE IF EXISTS `{pre}prop`;
CREATE TABLE `{pre}prop` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL COMMENT '道具名称',
  `card_name` varchar(32) default NULL COMMENT '道具的卡号',
  `card_pwd` varchar(32) default NULL COMMENT '道具的密码',
  `start_time` datetime NOT NULL COMMENT '开始时间',
  `end_time` datetime NOT NULL COMMENT '结束时间',
  `value` decimal(15,2) NOT NULL default '0.00' COMMENT '面值',
  `type` tinyint(1) NOT NULL default '0' COMMENT '道具类型 0:优惠券',
  `condition` varchar(255) default NULL COMMENT '条件数据 type=0时,表示ticket的表id,模型id',
  `is_close` tinyint(1) NOT NULL default '0' COMMENT '是否关闭 0:正常,1:关闭,2:下订单未支付时临时锁定',
  `img` varchar(255) default NULL COMMENT '道具图片',
  `is_userd` tinyint(1) NOT NULL default '0' COMMENT '是否被使用过 0:未使用,1:已使用',
  `is_send` tinyint(1) NOT NULL default '0' COMMENT '是否被发送过 0:否 1:是',
  `seller_id` int(11) unsigned default '0' COMMENT '所属商户ID',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='道具表';

-- --------------------------------------------------------


--
-- 表的结构 `{pre}refundment_doc`
--

DROP TABLE IF EXISTS `{pre}refundment_doc`;
CREATE TABLE `{pre}refundment_doc` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `order_no` varchar(20) NOT NULL COMMENT '订单号',
  `order_id` int(11) unsigned NOT NULL COMMENT '订单ID',
  `user_id` int(11) unsigned default 0 COMMENT '用户ID',
  `amount` decimal(15,2) NOT NULL default '0.00' COMMENT '退款金额',
  `time` datetime default NULL COMMENT '时间',
  `admin_id` int(11) unsigned default NULL COMMENT '管理员ID',
  `pay_status` tinyint(1) NOT NULL default '0' COMMENT '状态,0:申请中 1:已拒绝 2:已完成 3:等待买家发货 4:等待商家确认',
  `content` text COMMENT '申请原因',
  `dispose_time` datetime default NULL COMMENT '处理时间',
  `dispose_idea` text COMMENT '处理意见',
  `if_del` tinyint(1) NOT NULL default '0' COMMENT '0:未删除 1:已删除',
  `order_goods_id` text COMMENT '订单与商品关联ID集合',
  `seller_id` int(11) unsigned NOT NULL default '0' COMMENT '商家ID',
  `way` varchar(20) NOT NULL default '' COMMENT '退款方式,balance:用户余额 other:其他方式 origin:原路退回',
  `trade_no` varchar(255) NOT NULL default '' COMMENT '支付平台退款流水号',
  `img_list` text COMMENT '图片',
  `user_freight_id` int(11) unsigned default NULL COMMENT '用户发货时货运公司ID',
  `user_delivery_code` varchar(30) default NULL COMMENT '用户发货时快递单号',
  `user_send_time` datetime default NULL COMMENT '发货时间',
  `_hash` int(11) unsigned NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY  (`id`,`_hash`),
  index (`order_id`),
  index (`seller_id`),
  index (`if_del`),
  index (`user_id`),
  index (`pay_status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='售后退款单';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}exchange_doc`
--

DROP TABLE IF EXISTS `{pre}exchange_doc`;
CREATE TABLE `{pre}exchange_doc` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `order_no` varchar(20) NOT NULL COMMENT '订单号',
  `order_id` int(11) unsigned NOT NULL COMMENT '订单ID',
  `user_id` int(11) unsigned default 0 COMMENT '用户ID',
  `time` datetime default NULL COMMENT '时间',
  `admin_id` int(11) unsigned default NULL COMMENT '管理员ID',
  `status` tinyint(1) NOT NULL default '0' COMMENT '状态,0:申请中 1:已拒绝 2:已完成 3:等待买家发货 4:等待商家确认',
  `content` text COMMENT '申请原因',
  `dispose_time` datetime default NULL COMMENT '处理时间',
  `dispose_idea` text COMMENT '处理意见',
  `if_del` tinyint(1) NOT NULL default '0' COMMENT '0:未删除 1:已删除',
  `order_goods_id` text COMMENT '订单与商品关联ID集合',
  `seller_id` int(11) unsigned NOT NULL default '0' COMMENT '商家ID',
  `img_list` text COMMENT '图片',
  `user_freight_id` int(11) unsigned default NULL COMMENT '用户发货时货运公司ID',
  `user_delivery_code` varchar(30) default NULL COMMENT '用户发货时快递单号',
  `user_send_time` datetime default NULL COMMENT '发货时间',
  `seller_freight_id` int(11) unsigned default NULL COMMENT '商家发货时货运公司ID',
  `seller_delivery_code` varchar(30) default NULL COMMENT '商家发货时快递单号',
  `seller_send_time` datetime default NULL COMMENT '发货时间',
  `_hash` int(11) unsigned NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY  (`id`,`_hash`),
  index (`order_id`),
  index (`seller_id`),
  index (`if_del`),
  index (`user_id`),
  index (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='售后换货单';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}fix_doc`
--

DROP TABLE IF EXISTS `{pre}fix_doc`;
CREATE TABLE `{pre}fix_doc` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `order_no` varchar(20) NOT NULL COMMENT '订单号',
  `order_id` int(11) unsigned NOT NULL COMMENT '订单ID',
  `user_id` int(11) unsigned default 0 COMMENT '用户ID',
  `time` datetime default NULL COMMENT '时间',
  `admin_id` int(11) unsigned default NULL COMMENT '管理员ID',
  `status` tinyint(1) NOT NULL default '0' COMMENT '状态,0:申请中 1:已拒绝 2:已完成 3:等待买家发货 4:等待商家确认',
  `content` text COMMENT '申请原因',
  `dispose_time` datetime default NULL COMMENT '处理时间',
  `dispose_idea` text COMMENT '处理意见',
  `if_del` tinyint(1) NOT NULL default '0' COMMENT '0:未删除 1:已删除',
  `order_goods_id` text COMMENT '订单与商品关联ID集合',
  `seller_id` int(11) unsigned NOT NULL default '0' COMMENT '商家ID',
  `img_list` text COMMENT '图片',
  `user_freight_id` int(11) unsigned default NULL COMMENT '用户发货时货运公司ID',
  `user_delivery_code` varchar(30) default NULL COMMENT '用户发货时快递单号',
  `user_send_time` datetime default NULL COMMENT '发货时间',
  `seller_freight_id` int(11) unsigned default NULL COMMENT '商家发货时货运公司ID',
  `seller_delivery_code` varchar(30) default NULL COMMENT '商家发货时快递单号',
  `seller_send_time` datetime default NULL COMMENT '发货时间',
  `_hash` int(11) unsigned NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY  (`id`,`_hash`),
  index (`order_id`),
  index (`seller_id`),
  index (`if_del`),
  index (`user_id`),
  index (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='售后维修单';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}regiment`
--

DROP TABLE IF EXISTS `{pre}regiment`;
CREATE TABLE `{pre}regiment` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL COMMENT '团购标题',
  `start_time` datetime NOT NULL COMMENT '开始时间',
  `end_time` datetime NOT NULL COMMENT '结束时间',
  `store_nums` int(11) NOT NULL default '0' COMMENT '库存量',
  `sum_count` int(11) NOT NULL default '0' COMMENT '已销售量',
  `limit_min_count` int(11) NOT NULL default '0' COMMENT '每人限制最少购买数量',
  `limit_max_count` int(11) NOT NULL default '0' COMMENT '每人限制最多购买数量',
  `intro` varchar(255) default NULL COMMENT '介绍',
  `is_close` tinyint(1) NOT NULL default '0' COMMENT '0开启,1关闭,2待审核',
  `regiment_price` decimal(15,2) NOT NULL default '0.00' COMMENT '团购价格',
  `sell_price` decimal(15,2) NOT NULL default '0.00' COMMENT '原来价格',
  `goods_id` int(11) unsigned NOT NULL COMMENT '关联商品id',
  `img` varchar(255) default NULL COMMENT '商品图片',
  `sort` smallint(5) NOT NULL DEFAULT '99' COMMENT '排序',
  `seller_id` int(11) unsigned NOT NULL default '0' COMMENT '商家ID',
  `_hash` int(11) unsigned NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY  (`id`,`_hash`),
  index (`is_close`,`start_time`,`end_time`),
  index (`goods_id`),
  index (`sort`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='团购';

-- --------------------------------------------------------


--
-- 表的结构 `{pre}spec`
--

DROP TABLE IF EXISTS `{pre}spec`;
CREATE TABLE `{pre}spec` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL COMMENT '规格名称',
  `value` text COMMENT '规格值',
  `type` tinyint(1) NOT NULL default '1' COMMENT '显示类型 1文字 2图片',
  `note` varchar(255) default NULL COMMENT '备注说明',
  `is_del` tinyint(1) default '0' COMMENT '是否删除1删除',
  `seller_id` int(11) default '0' COMMENT '商家ID',
  PRIMARY KEY  (`id`),
  index (`is_del`),
  index (`seller_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='规格表';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}spec_photo`
--

DROP TABLE IF EXISTS `{pre}spec_photo`;
CREATE TABLE `{pre}spec_photo` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `address` varchar(255) default NULL COMMENT '图片地址',
  `name` varchar(100) default NULL COMMENT '图片名称',
  `create_time` datetime default NULL COMMENT '创建时间',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='规格图片表';

-- --------------------------------------------------------


--
-- 导出表中的数据 `{pre}seller`
--
DROP TABLE IF EXISTS `{pre}seller`;
CREATE TABLE `{pre}seller` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `seller_name` varchar(80) NOT NULL COMMENT '商家登录用户名',
  `password` char(32) NOT NULL COMMENT '商家密码',
  `create_time` datetime DEFAULT NULL COMMENT '加入时间',
  `login_time` datetime DEFAULT NULL COMMENT '最后登录时间',
  `is_vip` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否是特级商家',
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:未删除,1:已删除',
  `is_lock` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:未锁定,1:已锁定',
  `true_name` varchar(80) NOT NULL COMMENT '商家真实名称',
  `email` varchar(255) NOT NULL DEFAULT '' COMMENT '电子邮箱',
  `mobile` varchar(20) NOT NULL COMMENT '手机号码',
  `phone` varchar(20) NOT NULL COMMENT '座机号码',
  `paper_img` varchar(255) DEFAULT NULL COMMENT '执照证件照片',
  `cash` decimal(15,2) DEFAULT NULL COMMENT '保证金',
  `country` int(11) unsigned default NULL COMMENT '国ID',
  `province` int(11) unsigned NOT NULL COMMENT '省ID',
  `city` int(11) unsigned NOT NULL COMMENT '市ID',
  `area` int(11) unsigned NOT NULL COMMENT '区ID',
  `address` varchar(255) NOT NULL COMMENT '地址',
  `account` text COMMENT '收款账号信息',
  `server_num` varchar(20) default NULL COMMENT 'QQ号码',
  `home_url` varchar(255) default NULL COMMENT '企业URL网站',
  `sort` smallint(5) NOT NULL default '99' COMMENT '排序',
  `tax` decimal(15,2) NOT NULL default '0.00' COMMENT '税率',
  `seller_message_ids` text COMMENT '商户消息ID',
  `grade` int(11) NOT NULL default '0' COMMENT '评分总数',
  `sale` int(11) NOT NULL default '0' COMMENT '总销量',
  `comments` int(11) NOT NULL default '0' COMMENT '评论次数',
  `logo` varchar(255) NOT NULL default '' COMMENT 'LOGO图标',
  `discount` decimal(15,2) unsigned NOT NULL DEFAULT '100.00' COMMENT '商户结算折扣率',
  `_hash` int(11) unsigned NOT NULL COMMENT '预留散列字段',
  UNIQUE KEY `seller_name` (`seller_name`,`_hash`),
  PRIMARY KEY  (`id`,`_hash`),
  INDEX(`seller_name`,`password`),
  INDEX(`true_name`),
  INDEX(`is_vip`),
  INDEX(`is_del`),
  INDEX(`is_lock`),
  INDEX(`email`),
  INDEX(`sort`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='商家表';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}seller_message`
--

DROP TABLE IF EXISTS `{pre}seller_message`;
CREATE TABLE IF NOT EXISTS `{pre}seller_message` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL COMMENT '标题',
  `content` text COMMENT '内容',
  `time` datetime NOT NULL COMMENT '发送时间',
  `rev_info` text COMMENT '收件人信息',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='商家消息';

-- --------------------------------------------------------
--
-- 表的结构 `{pre}goods`
--

DROP TABLE IF EXISTS `{pre}goods`;
CREATE TABLE `{pre}goods` (
  `id` int(11) unsigned NOT NULL auto_increment COMMENT '商品ID',
  `name` varchar(255)NOT NULL COMMENT '商品名称',
  `goods_no` varchar(20) NOT NULL COMMENT '商品的货号',
  `model_id` int(11) unsigned NOT NULL COMMENT '模型ID',
  `sell_price` decimal(15,2) NOT NULL COMMENT '销售价格',
  `market_price` decimal(15,2) default NULL COMMENT '市场价格',
  `cost_price` decimal(15,2) default NULL COMMENT '成本价格',
  `up_time` datetime default NULL COMMENT '上架时间',
  `down_time` datetime default NULL COMMENT '下架时间',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `store_nums` int(11) NOT NULL default '0' COMMENT '库存',
  `img` varchar(255) default NULL COMMENT '原图',
  `ad_img` varchar(255) default NULL COMMENT '宣传图',
  `is_del` tinyint(1) NOT NULL default '0' COMMENT '商品状态 0正常 1已删除 2下架 3申请上架',
  `content` text COMMENT '商品描述',
  `keywords` varchar(255) default NULL COMMENT 'SEO关键词',
  `description` varchar(255) default NULL COMMENT 'SEO描述',
  `search_words` varchar(50) default NULL COMMENT '产品搜索词库,逗号分隔',
  `weight` decimal(15,2) NOT NULL default '0.00' COMMENT '重量',
  `point` int(11) NOT NULL default '0' COMMENT '积分',
  `unit` varchar(10) default NULL COMMENT '计件单位。如:件,箱,个',
  `brand_id` int(11) NOT NULL default '0' COMMENT '品牌ID',
  `visit` int(11) NOT NULL default '0' COMMENT '浏览次数',
  `favorite` int(11) NOT NULL default '0' COMMENT '收藏次数',
  `sort` smallint(5) NOT NULL default '99' COMMENT '排序',
  `spec_array` text COMMENT '商品信息json数据',
  `exp` int(11) NOT NULL default '0' COMMENT '经验值',
  `comments` int(11) NOT NULL default '0' COMMENT '评论次数',
  `sale` int(11) NOT NULL default '0' COMMENT '销量',
  `grade` int(11) NOT NULL default '0' COMMENT '评分总数',
  `seller_id` int(11) unsigned default '0' COMMENT '卖家ID',
  `is_share` tinyint(1) NOT NULL default '0' COMMENT '共享商品 0不共享 1共享',
  `is_delivery_fee` tinyint(1) NOT NULL default '0' COMMENT '免运费 0收运费 1免运费',
  `promo` varchar(50) NOT NULL DEFAULT '' COMMENT '默认:普通,groupon:团购,time:限时抢购,costpoint:积分兑换',
  `active_id` int(11) NOT NULL DEFAULT '0' COMMENT '活动ID主键',
  `type` varchar(20) NOT NULL DEFAULT 'default' COMMENT 'default:实体,code:到店服务,download:知识付费下载',
  `_hash` int(11) unsigned NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY  (`id`,`_hash`),
  index (`is_del`),
  index (`sort`),
  index (`sale`),
  index (`grade`),
  index (`sell_price`),
  index (`name`),
  index (`goods_no`),
  index (`is_share`),
  index (`brand_id`,`is_del`),
  index (`brand_id`,`sell_price`),
  index (`brand_id`,`grade`),
  index (`brand_id`,`sale`),
  index (`store_nums`,`is_del`),
  index (`seller_id`,`is_del`),
  index (`seller_id`,`sell_price`),
  index (`seller_id`,`grade`),
  index (`seller_id`,`sale`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='商品信息表';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}order`
--

DROP TABLE IF EXISTS `{pre}order`;
CREATE TABLE `{pre}order` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `order_no` varchar(20) NOT NULL COMMENT '订单号',
  `user_id` int(11) unsigned NOT NULL COMMENT '用户ID',
  `pay_type` int(11) NOT NULL COMMENT '用户支付方式ID,当为0时表示货到付款',
  `distribution` int(11) default NULL COMMENT '用户选择的配送ID',
  `status` tinyint(1) default '1' COMMENT '订单状态 1生成订单,2支付订单,3取消订单(客户触发),4作废订单(管理员触发),5完成订单,6退款(订单完成后),7部分退款(订单完成后)',
  `pay_status` tinyint(1) default '0' COMMENT '支付状态 0：未支付; 1：已支付;',
  `distribution_status` tinyint(1) default '0' COMMENT '配送状态 0：未发送,1：已发送,2：部分发送',
  `accept_name` varchar(20) NOT NULL COMMENT '收货人姓名',
  `postcode` varchar(6) default NULL COMMENT '邮编',
  `telphone` varchar(20) default NULL COMMENT '联系电话',
  `country` int(11) default NULL COMMENT '国ID',
  `province` int(11) default NULL COMMENT '省ID',
  `city` int(11) default NULL COMMENT '市ID',
  `area` int(11) default NULL COMMENT '区ID',
  `address` varchar(250) default NULL COMMENT '收货地址',
  `mobile` varchar(20) default NULL COMMENT '手机',
  `payable_amount` decimal(15,2) default '0.00' COMMENT '应付商品总金额',
  `real_amount` decimal(15,2) NOT NULL default '0.00' COMMENT '实付商品总金额(会员折扣,促销规则折扣)',
  `payable_freight` decimal(15,2) NOT NULL default '0.00' COMMENT '总运费金额',
  `real_freight` decimal(15,2) NOT NULL default '0.00' COMMENT '实付运费',
  `pay_time` datetime default NULL COMMENT '付款时间',
  `send_time` datetime default NULL COMMENT '发货时间',
  `create_time` datetime default NULL COMMENT '下单时间',
  `completion_time` datetime default NULL COMMENT '订单完成时间',
  `invoice` tinyint(1) NOT NULL default '0' COMMENT '发票：0不索要1索要',
  `postscript` varchar(255) default NULL COMMENT '用户附言',
  `note` text COMMENT '管理员备注和促销规则描述',
  `if_del` tinyint(1) default '0' COMMENT '是否删除1为删除',
  `insured` decimal(15,2) NOT NULL default '0.00' COMMENT '保价',
  `invoice_info` text  COMMENT '发票信息JSON数据',
  `taxes` decimal(15,2) NOT NULL default '0.00' COMMENT '税金',
  `promotions` decimal(15,2) NOT NULL default '0.00' COMMENT '促销优惠金额和会员折扣',
  `discount` decimal(15,2) NOT NULL default '0.00' COMMENT '订单折扣或涨价',
  `order_amount` decimal(15,2) NOT NULL default '0.00' COMMENT '订单总金额',
  `prop` varchar(255) default NULL COMMENT '使用的道具id',
  `accept_time` varchar(80) default NULL COMMENT '用户收货时间',
  `exp` smallint(5) unsigned NOT NULL default '0' COMMENT '增加的经验',
  `point` smallint(5) unsigned NOT NULL default '0' COMMENT '增加的积分',
  `type` varchar(50) NOT NULL DEFAULT '' COMMENT '默认:普通,groupon:团购,time:限时抢购,costpoint:积分兑换',
  `trade_no` varchar(255) default NULL COMMENT '支付平台交易号',
  `takeself` int(11) unsigned NOT NULL default '0' COMMENT '自提点ID',
  `checkcode` varchar(255) default NULL COMMENT '自提方式的验证码',
  `active_id` int(11) unsigned NOT NULL default '0' COMMENT '促销活动ID',
  `seller_id` int(11) unsigned NOT NULL default '0' COMMENT '商家ID',
  `is_checkout` tinyint(1) NOT NULL default '0' COMMENT '是否给商家结算货款 0:未结算 1:已结算',
  `prorule_ids` varchar(255) NOT NULL default '' COMMENT '促销活动规格ID串，逗号分隔',
  `spend_point` int(11) NOT NULL DEFAULT '0' COMMENT '花费的积分数',
  `servicefee_amount` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '订单手续费总金额',
  `goods_type` varchar(20) NOT NULL default 'default' COMMENT 'default:实体,code:到店服务,download:知识付费下载',
  `_hash` int(11) unsigned NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY  (`id`,`_hash`),
  index (`if_del`),
  index (`order_no`),
  index (`user_id`),
  index (`seller_id`),
  index (`status`),
  index (`order_amount`),
  index (`completion_time`),
  index (`send_time`),
  index (`create_time`),
  index (`distribution_status`),
  index (`pay_status`),
  index (`accept_name`),
  index (`is_checkout`),
  index (`checkcode`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='订单表';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}cost_point`
--

DROP TABLE IF EXISTS `{pre}cost_point`;
CREATE TABLE `{pre}cost_point` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL COMMENT '活动名称',
  `sort` smallint(5) NOT NULL COMMENT '顺序',
  `goods_id` int(11) NOT NULL COMMENT '商品id',
  `point` int(11) NOT NULL COMMENT '所需要的积分',
  `is_close` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否关闭 0:否 1:是',
  `user_group` text COMMENT '允许参与活动的用户组,all表示所有用户组',
  `seller_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商家ID',
  `_hash` int(11) unsigned NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY (`id`,`_hash`),
  KEY `type` (`seller_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='商品积分兑换表';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}category`
--

DROP TABLE IF EXISTS `{pre}category`;
CREATE TABLE `{pre}category` (
  `id` int(11) unsigned NOT NULL auto_increment COMMENT '分类ID',
  `name` varchar(50) NOT NULL COMMENT '分类名称',
  `parent_id` int(11) unsigned NOT NULL COMMENT '父分类ID',
  `sort` smallint(5) NOT NULL default '0' COMMENT '排序',
  `visibility` tinyint(1) NOT NULL default '1' COMMENT '首页是否显示 1显示 0 不显示',
  `keywords` varchar(255) default NULL COMMENT 'SEO关键词和检索关键词',
  `descript` varchar(255) default NULL COMMENT 'SEO描述',
  `title` varchar(255) default NULL COMMENT 'SEO标题title',
  `_hash` int(11) unsigned NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY  (`id`,`_hash`),
  index (`parent_id`,`visibility`),
  index (`sort`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='产品分类表';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}model`
--

DROP TABLE IF EXISTS `{pre}model`;
CREATE TABLE `{pre}model` (
  `id` int(11) unsigned NOT NULL auto_increment COMMENT '模型ID',
  `name` varchar(50) NOT NULL COMMENT '模型名称',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='模型表';

-- --------------------------------------------------------

--
-- 导出表中的数据 `{pre}bill`
--
DROP TABLE IF EXISTS `{pre}bill`;
CREATE TABLE `{pre}bill` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `seller_id` int(11) unsigned NOT NULL COMMENT '商家ID',
  `apply_time` datetime DEFAULT NULL COMMENT '申请结算时间',
  `pay_time` datetime DEFAULT NULL COMMENT '支付结算时间',
  `admin_id` int(11) unsigned DEFAULT NULL COMMENT '管理员ID',
  `is_pay` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:未结算,1:已结算',
  `apply_content` text COMMENT '申请结算文本',
  `pay_content` text COMMENT '支付结算文本',
  `start_time` date DEFAULT NULL COMMENT '结算起始时间',
  `end_time` date DEFAULT NULL COMMENT '结算终止时间',
  `log` text COMMENT '结算明细',
  `order_ids` text COMMENT 'order表主键ID，结算的ID',
  `amount` decimal(15,2) NOT NULL default '0.00' COMMENT '结算的金额',
  `_hash` int(11) unsigned NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY  (`id`,`_hash`),
  index (`seller_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='商家货款结算单表';

--
-- 导出表中的数据 `{pre}takeself`
--
DROP TABLE IF EXISTS `{pre}takeself`;
CREATE TABLE `{pre}takeself` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '名称',
  `sort` smallint(5) NOT NULL DEFAULT '99' COMMENT '排序',
  `province` int(11) NOT NULL COMMENT '省份ID',
  `city` int(11) NOT NULL COMMENT '城市ID',
  `area` int(11) NOT NULL COMMENT '地区ID',
  `address` varchar(255) NOT NULL COMMENT '地址',
  `phone` varchar(30) DEFAULT NULL COMMENT '座机号码',
  `mobile` varchar(30) DEFAULT NULL COMMENT '手机号码',
  `seller_id` int(11) unsigned default 0 COMMENT '商家ID',
  `logo` varchar(255) DEFAULT NULL COMMENT 'logo图片',
  `_hash` int(11) unsigned NOT NULL COMMENT '预留散列字段',
  PRIMARY KEY (`id`,`_hash`),
  index (`seller_id`),
  index (`province`,`city`,`area`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='自提点';

-- --------------------------------------------------------


--
-- 表的结构 `{pre}withdraw`
--

DROP TABLE IF EXISTS `{pre}withdraw`;
CREATE TABLE `{pre}withdraw` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) unsigned NOT NULL COMMENT '用户ID',
  `time` datetime NOT NULL COMMENT '时间',
  `amount` decimal(15,2) NOT NULL default '0.00' COMMENT '金额',
  `name` varchar(255) NOT NULL COMMENT '开户姓名',
  `status` tinyint(1) NOT NULL default '0' COMMENT '-1失败,0未处理,1处理中,2成功',
  `note` varchar(255) default NULL COMMENT '用户备注',
  `re_note` varchar(255) default NULL COMMENT '回复备注信息',
  `is_del` tinyint(1) NOT NULL default '0' COMMENT '0未删除,1已删除',
  PRIMARY KEY  (`id`),
  index (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='提现记录';

--
-- 表的结构 `{pre}order_code_relation`
--

DROP TABLE IF EXISTS `{pre}order_code_relation`;
CREATE TABLE `{pre}order_code_relation` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `order_id` int(11) NOT NULL COMMENT '订单ID',
  `goods_id` int(11) NOT NULL COMMENT '商品ID',
  `code` varchar(50) NOT NULL COMMENT '验证码字符串',
  `seller_id` int(11) unsigned default '0' COMMENT '商家ID',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `create_time` datetime DEFAULT NULL COMMENT '生成时间',
  `use_time` datetime DEFAULT NULL COMMENT '使用时间',
  `is_used` tinyint(1) NOT NULL default '0' COMMENT '使用状态 0未用 1已用',
  PRIMARY KEY  (`id`),
  index (`order_id`),
  index (`code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='虚拟商品订单服务验证码关系';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}order_download_relation`
--

DROP TABLE IF EXISTS `{pre}order_download_relation`;
CREATE TABLE `{pre}order_download_relation` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `order_id` int(11) NOT NULL COMMENT '订单ID',
  `goods_id` int(11) NOT NULL COMMENT '商品ID',
  `seller_id` int(11) unsigned default '0' COMMENT '商家ID',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `create_time` datetime DEFAULT NULL COMMENT '生成时间',
  `num` smallint(6) NOT NULL default '0' COMMENT '下载次数',
  PRIMARY KEY  (`id`),
  index (`order_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='虚拟商品订单下载关系';

-- --------------------------------------------------------

--
-- 表的结构 `{pre}address`
--

DROP TABLE IF EXISTS `{pre}address`;
CREATE TABLE `{pre}address` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) unsigned NOT NULL COMMENT '用户ID',
  `accept_name` varchar(20) NOT NULL COMMENT '收货人姓名',
  `zip` varchar(6) default NULL COMMENT '邮编',
  `telphone` varchar(20) default NULL COMMENT '联系电话',
  `country` int(11) unsigned default NULL COMMENT '国ID',
  `province` int(11) unsigned NOT NULL COMMENT '省ID',
  `city` int(11) unsigned NOT NULL COMMENT '市ID',
  `area` int(11) unsigned NOT NULL COMMENT '区ID',
  `address` varchar(250) NOT NULL COMMENT '收货地址',
  `mobile` varchar(20) default NULL COMMENT '手机',
  `is_default` tinyint(1) NOT NULL default '0' COMMENT '是否默认,0:为非默认,1:默认',
  PRIMARY KEY  (`id`),
  index (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='收货信息表';

--
-- 表的结构 `{pre}invoice`
--

DROP TABLE IF EXISTS `{pre}invoice`;
CREATE TABLE `{pre}invoice` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) unsigned default NULL COMMENT '用户id',
  `type` tinyint(1) NOT NULL default '1' COMMENT '发票类型,1:普通发票,2:增值税专用发票',
  `company_name` varchar(80) NOT NULL COMMENT '单位名称',
  `taxcode` varchar(30) NOT NULL COMMENT '纳税人识别码',
  `address` varchar(100) default NULL COMMENT '注册地址',
  `telphone` varchar(20) default NULL COMMENT '注册电话',
  `bankname` varchar(80) default NULL COMMENT '开户银行',
  `bankno` varchar(30) default NULL COMMENT '银行账户',
  PRIMARY KEY  (`id`),
  index (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='发票表';

--
-- 表的结构 `{pre}goods_extend_download`
--

DROP TABLE IF EXISTS `{pre}goods_extend_download`;
CREATE TABLE `{pre}goods_extend_download` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `goods_id` int(11) NOT NULL COMMENT '商品ID',
  `url` varchar(255) NOT NULL COMMENT '下载地址',
  `seller_id` int(11) unsigned default '0' COMMENT '商家ID',
  `end_time` date DEFAULT NULL COMMENT '截至时间',
  `limit_num` smallint(6) DEFAULT '0' COMMENT '限制下载次数',
  PRIMARY KEY  (`id`),
  index (`goods_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='商品下载资源地址';

-- --------------------------------------------------------

INSERT INTO `{pre}right` VALUES (NULL, '[商品]商品列表', 'goods@goods_list,goods@search,goods@search_result', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[商品]商品添加修改', 'goods@update_price,goods@update_commend,goods@update_store,goods@goods_stats,goods@goods_img_upload,goods@goods_edit,goods@goods_update,goods@member_price,goods@model_init,goods@ajax_sort,goods@goods_share', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[商品]商品删除', 'goods@goods_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[商品]商品回收站', 'goods@goods_recycle_del,goods@goods_recycle_restore,goods@goods_recycle_list', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[商品]商品分类列表', 'goods@category_list,goods@category_sort', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[商品]商品分类添加修改', 'goods@category_edit,goods@category_save', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[商品]商品分类删除', 'goods@category_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[商品]商品导出excel', 'goods@goods_report', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[商品]商品批量设置', 'goods@goods_setting,goods@goods_setting_save', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[商品]品牌列表', 'brand@brand_list,goods@brand_sort', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[商品]品牌添加修改', 'brand@brand_save,brand@brand_edit', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[商品]品牌删除', 'brand@brand_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[商品]品牌分类列表', 'brand@category_list', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[商品]品牌分类添加修改', 'brand@category_save,brand@category_edit', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[商品]品牌分类删除', 'brand@category_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[商品]模型列表', 'goods@model_list', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[商品]模型添加修改', 'goods@model_update,goods@model_edit', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[商品]模型删除', 'goods@model_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[商品]规格列表', 'goods@spec_list', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[商品]规格添加修改', 'goods@spec_edit,goods@spec_update', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[商品]规格删除', 'goods@spec_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[商品]规格图库', 'goods@spec_photo,goods@spec_photo_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[商品]规格回收站', 'goods@spec_recycle_list,goods@spec_recycle_restore,goods@spec_recycle_del,goods@spec_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]订单列表', 'order@order_list,order@search', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]订单添加修改', 'order@order_note,order@order_update,order@order_edit', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]核销验证码', 'order@check_code_ajax,order@get_code_info_ajax,order@order_code_check', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]订单详情', 'order@order_show', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]订单回收站', 'order@order_recycle_restore,order@order_recycle_del,order@order_recycle_list', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]订单打印', 'order@expresswaybill_print,order@expresswaybill_ajax,order@expresswaybill_template,order@expresswaybill_edit,order@expresswaybill_update,order@merge_template,order@pick_template,order@shop_template,order@print_template_update,order@print_template', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]订单删除', 'order@order_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]订单状态修改', 'order@order_delivery_doc,order@order_collection_doc,order@order_complete,order@order_refundment,order@order_collection,order@order_deliver', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]收款单列表', 'order@order_collection_list', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]收款单详情', 'order@order_collection,order@collection_show', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]收款单删除', 'order@collection_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]收款单回收站', 'order@collection_recycle_restore,order@collection_recycle_del,order@collection_recycle_list', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]退款单列表', 'order@order_refundment_list', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]退款单删除', 'order@order_refundment_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]退款单回收站', 'order@refundment_recycle_list,order@refundment_recycle_restore,order@refundment_recycle_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]退款单详情', 'order@refundment_show,order@order_refundment', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]配货单列表', 'order@order_delivery_list', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]配货单更新', 'order@delivery_doc_update', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]配货单删除', 'order@delivery_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]配货单回收站', 'order@delivery_recycle_list,order@delivery_recycle_restore,order@delivery_recycle_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]退款申请单列表', 'order@refundment_list', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]退款申请单详情', 'order@refundment_doc_show', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]退款申请单删除', 'order@refundment_doc_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]退款申请单修改', 'order@refundment_doc_show_save', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]订单退款操作', 'order@order_refundment_doc', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]配货单详情', 'order@delivery_show', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]订单导出excel', 'order@order_report', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]换货申请单列表', 'order@exchange_list', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]换货申请单详情', 'order@exchange_doc_show', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]换货申请单删除', 'order@exchange_doc_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]换货申请单修改', 'order@exchange_doc_show_save', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]换货单列表', 'order@order_exchange_list', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]换货单删除', 'order@order_exchange_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]换货单回收站', 'order@exchange_recycle_list,order@exchange_recycle_restore,order@exchange_recycle_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]换货单详情', 'order@exchange_show', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]维修申请单列表', 'order@fix_list', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]维修申请单详情', 'order@fix_doc_show', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]维修申请单删除', 'order@fix_doc_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]维修申请单修改', 'order@fix_doc_show_save', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]维修单列表', 'order@order_fix_list', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]维修单删除', 'order@order_fix_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]维修单回收站', 'order@fix_recycle_list,order@fix_recycle_restore,order@fix_recycle_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[订单]维修单详情', 'order@fix_show', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[营销]促销规则列表', 'market@pro_rule_list', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[营销]促销规则添加修改', 'market@pro_rule_edit_act,market@pro_rule_edit,market@getTicketList', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[营销]促销规则删除', 'market@pro_rule_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[营销]限时抢购列表', 'market@pro_speed_list', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[营销]限时抢购删除', 'market@pro_speed_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[营销]限时抢购添加修改', 'market@pro_speed_edit,market@pro_speed_edit_act', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[营销]特价添加修改', 'market@sale_edit,market@sale_edit_act,market@sale_list', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[营销]特价删除', 'market@sale_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[营销]团购列表', 'market@regiment_list', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[营销]团购添加修改', 'market@regiment_edit_act,market@regiment_edit', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[营销]团购删除', 'market@regiment_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[营销]优惠券列表', 'market@ticket_list', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[营销]优惠券添加修改', 'market@ticket_edit_act,market@ticket_edit', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[营销]优惠券文件详情', 'market@ticket_more_list', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[营销]优惠券删除', 'market@ticket_more_del,market@ticket_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[营销]优惠券批量生成', 'market@ticket_create', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[营销]优惠券EXCEL导出', 'market@ticket_excel', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[营销]优惠券状态修改', 'market@ticket_status', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[营销]积分兑换列表', 'market@cost_point_list', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[营销]积分兑换添加修改', 'market@cost_point_edit_act,market@cost_point_edit', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[营销]积分兑换删除', 'market@cost_point_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[会员]会员提现列表', 'member@withdraw_list', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[会员]会员提现删除', 'member@withdraw_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[会员]会员提现回收站', 'member@withdraw_update,member@withdraw_recycle', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[会员]会员提现状态修改', 'member@withdraw_status', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[会员]会员提现详情', 'member@withdraw_detail', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[会员]到货通知列表', 'message@notify_list', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[会员]到货通知删除', 'message@notify_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[会员]到货通知发送邮件', 'message@notify_email_send', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[会员]到货通知发送短信', 'message@notify_sms_send', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[会员]商户列表', 'member@seller_list', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[会员]商户添加修改', 'member@seller_edit,member@seller_add,member@ajax_seller_lock', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[会员]商户删除', 'member@seller_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[会员]商户回收站', 'member@seller_recycle_list,member@seller_recycle_del,member@seller_recycle_restore', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[会员]邮件订阅列表', 'message@registry_list', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[会员]邮件订阅发送', 'message@registry_message_send', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[会员]邮件订阅删除', 'message@registry_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[会员]营销短信列表', 'message@marketing_sms_list', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[会员]营销短信发送', 'message@marketing_sms_send,message@start_marketing_sms', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[会员]营销短信详情', 'message@marketing_sms_show', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[会员]营销短信删除', 'message@marketing_sms_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[会员]商户消息列表', 'comment@seller_message_list', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[会员]商户消息发送', 'comment@seller_message_send,comment@start_seller_message', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[会员]商户消息详情', 'comment@seller_message_show', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[会员]商户消息删除', 'comment@seller_message_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[会员]单品手续费列表', 'goods@goods_rate_list', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[会员]单品手续费添加修改', 'goods@goods_rate_edit,goods@goods_rate_save', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[会员]单品手续费删除', 'goods@goods_rate_del', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[会员]分类手续费列表', 'goods@category_rate_list', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[会员]分类手续费添加修改', 'goods@category_rate_edit,goods@category_rate_save', 0);
INSERT INTO `{pre}right` VALUES (NULL, '[会员]分类手续费删除', 'goods@category_rate_del', 0);


--
-- 建立外键关系
--
ALTER TABLE `{pre}withdraw` ADD foreign key(user_id) references `{pre}user`(id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `{pre}invoice` ADD foreign key(user_id) references `{pre}user`(id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `{pre}online_recharge` ADD foreign key(user_id) references `{pre}user`(id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `{pre}group_price` ADD foreign key(group_id) references `{pre}user_group`(id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `{pre}goods_attribute` ADD foreign key(attribute_id) references `{pre}attribute`(id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `{pre}goods_attribute` ADD foreign key(attribute_id) references `{pre}attribute`(id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `{pre}favorite` ADD FOREIGN KEY (user_id) REFERENCES `{pre}user`(id) on delete cascade on update cascade;
ALTER TABLE `{pre}delivery_extend` ADD FOREIGN KEY (delivery_id) REFERENCES `{pre}delivery`(id) on delete cascade on update cascade;
ALTER TABLE `{pre}address` ADD foreign key(user_id) references `{pre}user`(id) on delete cascade on update cascade;
ALTER TABLE `{pre}attribute` ADD foreign key(model_id) references `{pre}model`(id) on delete cascade on update cascade;