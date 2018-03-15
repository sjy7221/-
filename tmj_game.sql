/*
Navicat MySQL Data Transfer

Source Server         : 土木金华
Source Server Version : 50637
Source Host           : 127.0.0.1:3306
Source Database       : tmjh_byzp

Target Server Type    : MYSQL
Target Server Version : 50637
File Encoding         : 65001

Date: 2018-01-09 15:21:08
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for gs_admin
-- ----------------------------
DROP TABLE IF EXISTS `gs_admin`;
CREATE TABLE `gs_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL COMMENT '用户名',
  `password` varchar(128) NOT NULL COMMENT '密码',
  `salts` varchar(64) NOT NULL COMMENT '盐值',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否拉黑0正常1拉黑',
  `last_time` datetime DEFAULT NULL COMMENT '上次登入时间',
  `last_ip` varchar(64) NOT NULL COMMENT '上次登入ip',
  `last_address` varchar(64) NOT NULL COMMENT '上次登入地点',
  `time` datetime NOT NULL COMMENT '上次登录时间',
  `ip` varchar(64) NOT NULL COMMENT '本次登入ip',
  `address` varchar(64) NOT NULL COMMENT '登入地区',
  `role_id` int(11) NOT NULL COMMENT '角色id',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建日期',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=128 DEFAULT CHARSET=utf8 COMMENT='后台用户表';

-- ----------------------------
-- Records of gs_admin
-- ----------------------------
INSERT INTO `gs_admin` VALUES ('127', 'admin', 'bcae8c87164efb9b322b06bab1c53da4', 'a7c62b', '0', '2017-11-06 14:18:06', '27.18.216.88', '湖北省武汉市', '2018-01-05 14:35:02', '59.175.97.213', '湖北省武汉市', '0', '2017-03-31 06:49:49');

-- ----------------------------
-- Table structure for gs_agent
-- ----------------------------
DROP TABLE IF EXISTS `gs_agent`;
CREATE TABLE `gs_agent` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) NOT NULL COMMENT '用户id',
  `password` varchar(128) NOT NULL COMMENT '密码',
  `salts` varchar(64) NOT NULL COMMENT '盐',
  `balance` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '账户佣金',
  `realname` varchar(64) DEFAULT NULL COMMENT '提现姓名',
  `ti_type` int(11) DEFAULT NULL COMMENT '提现方式1.支付宝 2.银行卡',
  `number` varchar(128) DEFAULT NULL COMMENT '提现账号',
  `status` int(11) NOT NULL DEFAULT '1' COMMENT '状态 0 拉黑1.正常',
  `time` int(11) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `mid` (`mid`)
) ENGINE=InnoDB DEFAULT CHARSET=gbk;

-- ----------------------------
-- Records of gs_agent
-- ----------------------------

-- ----------------------------
-- Table structure for gs_angent_info
-- ----------------------------
DROP TABLE IF EXISTS `gs_angent_info`;
CREATE TABLE `gs_angent_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sn` varchar(25) NOT NULL COMMENT '提现单号',
  `mid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '持有人',
  `num` decimal(10,2) NOT NULL COMMENT '变动的数量',
  `title` varchar(64) DEFAULT NULL COMMENT '备注',
  `type` tinyint(2) DEFAULT '1' COMMENT '1.申请提现 2.提现失败返回3.一级佣金 4.二级佣金',
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '变动时间',
  `b1` decimal(10,2) NOT NULL COMMENT '一级佣金',
  `b2` decimal(10,2) NOT NULL COMMENT '二级佣金',
  `month` varchar(64) DEFAULT NULL COMMENT '年月',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='代理的佣金变化日志';

-- ----------------------------
-- Records of gs_angent_info
-- ----------------------------

-- ----------------------------
-- Table structure for gs_angent_record
-- ----------------------------
DROP TABLE IF EXISTS `gs_angent_record`;
CREATE TABLE `gs_angent_record` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sn` varchar(25) NOT NULL COMMENT '单号',
  `mid` int(11) DEFAULT '0' COMMENT '申请人',
  `num` decimal(11,2) DEFAULT '0.00' COMMENT '申请的数量',
  `rate` decimal(10,2) DEFAULT NULL COMMENT '手续费率',
  `money` decimal(10,2) DEFAULT '0.00' COMMENT '实际到账',
  `realname` varchar(32) DEFAULT '0' COMMENT '持卡人',
  `ti_type` varchar(32) DEFAULT '0' COMMENT '提现方式 1支付宝 2银行卡',
  `number` varchar(64) DEFAULT '0' COMMENT '转账卡号',
  `creat_time` varchar(15) NOT NULL COMMENT '创建时间',
  `finish_time` varchar(15) NOT NULL DEFAULT '0' COMMENT '操作时间',
  `is_pay` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否支付 1.未转2.已转3.驳回',
  `describe` text NOT NULL COMMENT '提现驳回描述',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='充值记录表';

-- ----------------------------
-- Records of gs_angent_record
-- ----------------------------

-- ----------------------------
-- Table structure for gs_feedback
-- ----------------------------
DROP TABLE IF EXISTS `gs_feedback`;
CREATE TABLE `gs_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone` varchar(32) DEFAULT NULL,
  `content` text,
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of gs_feedback
-- ----------------------------

-- ----------------------------
-- Table structure for gs_goods
-- ----------------------------
DROP TABLE IF EXISTS `gs_goods`;
CREATE TABLE `gs_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '商品id',
  `sort` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `num` int(11) unsigned NOT NULL COMMENT '虚拟币数量',
  `money` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '需要支付的钱数',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',
  `is_show` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '前台是否显示0显示1不显示',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='虚拟商品表';

-- ----------------------------
-- Records of gs_goods
-- ----------------------------
INSERT INTO `gs_goods` VALUES ('1', '1', '10', '0.10', '0000-00-00 00:00:00', '0');
INSERT INTO `gs_goods` VALUES ('2', '2', '20', '20.00', '0000-00-00 00:00:00', '0');
INSERT INTO `gs_goods` VALUES ('3', '3', '30', '30.00', '0000-00-00 00:00:00', '0');
INSERT INTO `gs_goods` VALUES ('4', '4', '40', '40.00', '0000-00-00 00:00:00', '0');
INSERT INTO `gs_goods` VALUES ('5', '5', '50', '50.00', '0000-00-00 00:00:00', '0');
INSERT INTO `gs_goods` VALUES ('6', '6', '60', '60.00', '0000-00-00 00:00:00', '0');
INSERT INTO `gs_goods` VALUES ('7', '7', '70', '70.00', '2017-12-12 16:19:34', '0');
INSERT INTO `gs_goods` VALUES ('8', '8', '80', '80.00', '2017-12-12 16:19:41', '0');

-- ----------------------------
-- Table structure for gs_log
-- ----------------------------
DROP TABLE IF EXISTS `gs_log`;
CREATE TABLE `gs_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL COMMENT '操作人id',
  `title` varchar(64) NOT NULL COMMENT '内容',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=337 DEFAULT CHARSET=utf8 COMMENT='操作日志表';

-- ----------------------------
-- Records of gs_log
-- ----------------------------

-- ----------------------------
-- Table structure for gs_logs_info
-- ----------------------------
DROP TABLE IF EXISTS `gs_logs_info`;
CREATE TABLE `gs_logs_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rid` int(11) DEFAULT '0',
  `room_id` int(11) NOT NULL COMMENT '房间号',
  `users` varchar(128) NOT NULL COMMENT '用户输赢情况',
  `info` text NOT NULL COMMENT '游戏记录',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `room_id` (`room_id`)
) ENGINE=InnoDB AUTO_INCREMENT=592 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of gs_logs_info
-- ----------------------------

-- ----------------------------
-- Table structure for gs_member
-- ----------------------------
DROP TABLE IF EXISTS `gs_member`;
CREATE TABLE `gs_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone` varchar(11) NOT NULL DEFAULT '0' COMMENT '玩家手机号',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '上级id',
  `is_agency` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否代理',
  `room_id` int(11) NOT NULL COMMENT '房间号',
  `openid` varchar(84) NOT NULL COMMENT 'openid',
  `nickname` varchar(64) NOT NULL COMMENT '昵称',
  `headimgurl` text NOT NULL COMMENT '头像',
  `sex` int(11) NOT NULL DEFAULT '1' COMMENT '性别 1男 2女',
  `num` int(11) unsigned NOT NULL COMMENT '房卡',
  `ip` varchar(64) NOT NULL COMMENT '本次登入ip',
  `time` int(11) NOT NULL COMMENT '时间',
  `create_time` int(11) NOT NULL COMMENT '注册时间',
  `is_black` int(11) NOT NULL DEFAULT '0' COMMENT '是否黑名单',
  `version` varchar(64) NOT NULL COMMENT 'app版本号',
  `token` varchar(64) NOT NULL,
  `win` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '赢的局数',
  `sum` int(11) NOT NULL DEFAULT '0' COMMENT '输的局数',
  `status` smallint(6) DEFAULT '0' COMMENT '0.不在线 1在线',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1606 DEFAULT CHARSET=gbk;

-- ----------------------------
-- Records of gs_member
-- ----------------------------

-- ----------------------------
-- Table structure for gs_mnum_info
-- ----------------------------
DROP TABLE IF EXISTS `gs_mnum_info`;
CREATE TABLE `gs_mnum_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) DEFAULT '0' COMMENT '持有人',
  `sn` varchar(128) DEFAULT NULL COMMENT '订单号',
  `num` int(11) DEFAULT '0' COMMENT '变动的数量',
  `gid` int(11) DEFAULT '0' COMMENT '变动的对象',
  `room_id` int(11) NOT NULL DEFAULT '0' COMMENT '房间号(与开房无关为0)',
  `title` varchar(64) DEFAULT NULL COMMENT '备注',
  `type` tinyint(2) DEFAULT '0' COMMENT '0.游戏房费 1.相关奖励 2后台充值 3线上充值４.代理充值',
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '变动时间时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=368 DEFAULT CHARSET=utf8 COMMENT='虚拟币变动记录表';

-- ----------------------------
-- Records of gs_mnum_info
-- ----------------------------

-- ----------------------------
-- Table structure for gs_msg
-- ----------------------------
DROP TABLE IF EXISTS `gs_msg`;
CREATE TABLE `gs_msg` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(128) DEFAULT '' COMMENT '标题',
  `content` text NOT NULL COMMENT '内容',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1.站内信2.公告 3.玩法',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='虚拟商品表';

-- ----------------------------
-- Records of gs_msg
-- ----------------------------
INSERT INTO `gs_msg` VALUES ('1', '玩法', '一、基本规则：\r\n基本牌：\r\n筒、条、万、风共136张牌，可碰、杠、吃\r\n特殊牌：红中、发财：固定杠牌，手牌中有红中，发财不可胡牌，胡牌前必须开杠\r\n癞子牌：\r\n万能牌，可代替成为红中，发财外任意一张牌和手中的牌进行组合，但不可与其它牌组合进行吃、碰、杠操作\r\n赖子的确定：\r\n四家抓完牌后庄家起到的第一张牌为赖子皮，癞子皮加1成为赖子。其顺序按照东南西北中发白和1-9的顺序，如果翻开的是北中发，则白板为赖子\r\n二、坐庄：\r\n1.首局随机坐庄，次局谁胡牌谁坐庄\r\n2.黄庄情况下庄家继续坐庄\r\n3.剩余最后９墩牌黄庄\r\n三、胡牌要求：\r\n1.必须开口（至少吃／碰／明杠任意一次）\r\n2.手上没有固定杠牌\r\n3.赢家与输家之间分数达到３番（８分）\r\n四、牌型介绍：\r\n胡牌牌型：分为大胡和屁胡。大胡胡牌手中不限赖子数量，屁胡胡牌手中最多只能有１个赖子(有两个以上赖子杠开可以胡），屁胡必须要有258将，自摸胡牌三家出，屁胡“点炮”一家出，大胡“点炮”三家出\r\n五、番数计算：\r\n红中、发财杠：1番\r\n赖子杠：2番\r\n明杠：1番\r\n暗杠：2番\r\n开口：1番（两家之间相互开口）\r\n硬胡：1番\r\n杠开：1番（屁胡杠开算一番，大胡无番）\r\n清一色/将一色/碰碰胡：1番\r\n风一色：金顶（50分）（只要下了铺，胡了就是金鼎）\r\n全球人/抢杠：金顶（50分）（剩一个赖子不算全球人，算屁胡）\r\n点炮：大胡一番（点炮者出1番）屁胡无番\r\n自摸：大胡1番（三家各出1番）屁胡无番\r\n包牌：胡牌者所胡牌型为清一色或将一色，且吃、碰、明杠次数达到三次或三次以上，全部由第三句产生吃碰杠的玩家包牌。所赢分数为单边金顶50分\r\n六、特殊规则：\r\n海底捞：最后四张牌，无需打出不可杠，根据摸牌顺序，谁先自摸谁胡牌\r\n截冲：其他人打出一张牌自己不胡牌，在自己没有任何操作前，自己不能胡牌\r\n', '3');
INSERT INTO `gs_msg` VALUES ('2', '公告设置', '亲爱的玩家：\r\n       壕友麻将正在火热公测中，全天免费开放！', '1');
INSERT INTO `gs_msg` VALUES ('3', '通知设置', '请玩家文明游戏，严禁赌博！11', '2');

-- ----------------------------
-- Table structure for gs_recharge
-- ----------------------------
DROP TABLE IF EXISTS `gs_recharge`;
CREATE TABLE `gs_recharge` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sn` varchar(128) NOT NULL COMMENT '订单号',
  `mid` int(11) NOT NULL COMMENT '玩家id',
  `agent1` int(11) DEFAULT '0' COMMENT '上一级id',
  `agent2` int(11) DEFAULT '0' COMMENT '上二级id',
  `num` int(11) NOT NULL COMMENT '钻石数',
  `balance` decimal(11,2) NOT NULL COMMENT '金额',
  `money` decimal(11,2) NOT NULL COMMENT '实际付款金额',
  `balance1` decimal(11,2) DEFAULT '0.00' COMMENT '一级佣金',
  `balance2` decimal(11,2) DEFAULT '0.00' COMMENT '二级佣金',
  `time` int(11) DEFAULT NULL COMMENT '时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `id_2` (`id`),
  KEY `mid` (`mid`),
  KEY `agent1` (`agent1`),
  KEY `agent2` (`agent2`)
) ENGINE=InnoDB DEFAULT CHARSET=gbk;

-- ----------------------------
-- Records of gs_recharge
-- ----------------------------

-- ----------------------------
-- Table structure for gs_rooms
-- ----------------------------
DROP TABLE IF EXISTS `gs_rooms`;
CREATE TABLE `gs_rooms` (
  `rid` int(11) NOT NULL AUTO_INCREMENT,
  `status` int(11) DEFAULT '0' COMMENT '0进行中 1.进行中 2.结束',
  `room_id` int(11) DEFAULT NULL,
  `difen` int(11) DEFAULT NULL,
  `jushu` int(11) DEFAULT NULL,
  `users` int(11) DEFAULT NULL,
  `f_id` int(11) DEFAULT '0' COMMENT '房主id',
  `fangzhu` varchar(32) DEFAULT NULL COMMENT '房主名称',
  `gid` smallint(6) DEFAULT '0',
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`rid`)
) ENGINE=InnoDB AUTO_INCREMENT=1344 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of gs_rooms
-- ----------------------------

-- ----------------------------
-- Table structure for gs_rooms_user
-- ----------------------------
DROP TABLE IF EXISTS `gs_rooms_user`;
CREATE TABLE `gs_rooms_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rid` int(11) DEFAULT NULL,
  `mid` int(11) DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL,
  `type` int(11) DEFAULT '0' COMMENT '观战者还是玩玩家',
  `fen` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2705 DEFAULT CHARSET=utf8 COMMENT='玩家玩游戏时玩家-记录中间表';

-- ----------------------------
-- Records of gs_rooms_user
-- ----------------------------

-- ----------------------------
-- Table structure for gs_settings
-- ----------------------------
DROP TABLE IF EXISTS `gs_settings`;
CREATE TABLE `gs_settings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `syst` text NOT NULL COMMENT '系统参数',
  `crep` text NOT NULL COMMENT '虚拟币参数',
  `payment` text NOT NULL COMMENT '支付参数',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='系统设置表';

-- ----------------------------
-- Records of gs_settings
-- ----------------------------
INSERT INTO `gs_settings` VALUES ('1', 'a:7:{s:6:\"param1\";s:1:\"0\";s:6:\"param2\";s:3:\"100\";s:6:\"param3\";s:1:\"5\";s:6:\"param4\";s:1:\"3\";s:6:\"param5\";s:2:\"10\";s:6:\"param6\";s:3:\"100\";s:6:\"param7\";s:1:\"0\";}', '1', '1');

-- ----------------------------
-- Table structure for gs_verification
-- ----------------------------
DROP TABLE IF EXISTS `gs_verification`;
CREATE TABLE `gs_verification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone` varchar(30) NOT NULL,
  `code` int(11) NOT NULL COMMENT '验证码',
  `time` varchar(15) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of gs_verification
-- ----------------------------
