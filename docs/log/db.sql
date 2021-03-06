-- linzequan 20191030
-- cloudbeds access token保存表
create table `ko_cloudbeds_access_token` (
    `id` int not null auto_increment comment '自增id',
    `access_token` varchar(64) not null comment 'access token',
    `token_type` varchar(32) not null comment 'token type',
    `expires_in` int not null comment '有效期',
    `refresh_token` varchar(64) not null comment 'refresh token',
    `update_time` int not null comment '更新时间',
    primary key (`id`)
) engine = myisam character set utf8 collate utf8_general_ci comment = 'cloudbeds access token保存表';


-- linzequan 20191031
-- cloudbeds酒店表
create table `ko_cloudbeds_hotels` (
    `id` int not null auto_increment comment '自增id',
    `propertyID` int not null comment 'cloudbeds酒店id',
    `propertyName` varchar(255) comment 'cloudbeds酒店名称',
    `propertyImage` varchar(255) comment 'cloudbeds酒店图片',
    `propertyImageThumb` varchar(255) comment 'cloudbeds酒店图片缩略图',
    `propertyPhone` varchar(32) comment 'cloudbeds酒店电话',
    `propertyEmail` varchar(32) comment 'cloudbeds酒店邮箱',
    `propertyAddress1` varchar(256) comment 'cloudbeds酒店地址1',
    `propertyAddress2` varchar(256) comment 'cloudbeds酒店地址2',
    `propertyCity` varchar(256) comment 'cloudbeds酒店城市',
    `propertyState` varchar(256) comment 'cloudbeds酒店所在区域',
    `propertyZip` varchar(256) comment 'cloudbeds酒店邮政编码',
    `propertyCountry` varchar(256) comment 'cloudbeds酒店所在国家',
    `propertyLatitude` varchar(256) comment 'cloudbeds酒店所在经度',
    `propertyLongitude` varchar(256) comment 'cloudbeds酒店所在纬度',
    `propertyCheckInTime` varchar(256) comment 'cloudbeds酒店入住时间',
    `propertyCheckOutTime` varchar(256) comment 'cloudbeds酒店退房时间',
    `propertyLateCheckOutAllowed` boolean comment 'cloudbeds酒店是否允许延迟退房时间',
    `propertyLateCheckOutType` varchar(256) comment 'cloudbeds酒店延迟退房单位，允许值为value数值或者percent百分比',
    `propertyLateCheckOutValue` varchar(128) comment 'cloudbeds酒店延迟退房数值',
    `propertyTermsAndConditions` text comment 'cloudbeds酒店展示给用户条款和条件',
    `propertyAmenities` varchar(512) comment 'cloudbeds酒店设施清单',
    `propertyDescription` text comment 'cloudbeds酒店描述',
    `propertyTimezone` varchar(128) comment 'cloudbeds酒店时区',
    `propertyCurrencyCode` varchar(128) comment 'cloudbeds酒店货币编码',
    `propertyCurrencySymbol` varchar(128) comment 'cloudbeds酒店货币符号',
    `propertyCurrencyPosition` varchar(128) comment 'cloudbeds酒店货币位置',
    primary key (`id`)
) engine = myisam character set utf8 collate utf8_general_ci comment = 'cloudbeds酒店表';


-- linzequan 20191031
-- cloudbeds酒店中文信息表
create table `ko_cloudbeds_hotels_cn` (
    `id` int not null auto_increment comment '自增id',
    `hid` int not null comment '酒店表id',
    `propertyID` int not null comment 'cloudbeds酒店id',
    `propertyName` varchar(255) comment 'cloudbeds酒店名称',
    `propertyDescription` text comment 'cloudbeds酒店描述',
    primary key (`id`)
) engine myisam character set utf8 collate utf8_general_ci comment = 'cloudbeds酒店中文信息表';


-- linzequan 20191031
-- 酒店订单表
create table `ko_hotel_order` (
    `id` int not null auto_increment comment '自增id',
    `openid` varchar(32) comment '订单用户微信openid',
    `propertyID` int not null comment '酒店id',
    `startDate` date comment '入住日期',
    `endDate` date comment '退房日期',
    `guestFirstName` varchar(64) default '' comment '客户名称',
    `guestLastName` varchar(64) default '' comment '客户姓',
    `guestCountry` varchar(64) default '' comment '客户所在国家编码',
    `guestZip` varchar(32) default '' comment '客户邮编',
    `guestEmail` varchar(128) default '' comment '客户邮箱',
    `guestPhone` varchar(32) default '' comment '客户手机号码',
    `rooms` text comment '预订的房间信息',
    `rooms_roomTypeID` varchar(32) default '' comment '预订的房间房型',
    `rooms_quantity` int default 0 comment '预订的房间数量',
    `adults` text comment '预订的大人房间信息',
    `adults_roomTypeID` varchar(32) default '' comment '预订的大人房间信息',
    `adults_quantity` int default 0 comment '预订的大人房间数量',
    `children` text comment '预订的儿童房间信息',
    `children_roomTypeID` varchar(32) default '' comment '预订的儿童房间信息',
    `children_quantity` int default 0 comment '预订的儿童房间数量',
    `status` int default 0 comment '0未支付，1已支付，2预定成功，-1订单取消',
    `total` varchar(32) comment '总价格（优惠后的价格）',
    `frontend_total` varchar(32) comment '小程序计算的总价格',
    `balance` varchar(32) comment '余款',
    `balanceDetailed` text comment '余款细节',
    `assigned` text comment '分配的酒店房间详情',
    `unassigned` text comment '未分配的酒店房间详情',
    `cardsOnFile` text comment '信用卡信息',
    `reservationID` int comment '预定id',
    `estimatedArrivalTime` time comment '到店时间，24小时制',
    `create_time` int comment '订单生成时间',
    `outTradeNo` varchar(64) comment '订单编号',
    `transaction_id` varchar(64) default '' comment '交易单号',
    `transaction_info` varchar(512) default '' comment '交易详情',
    primary key (`id`)
) engine myisam character set utf8 collate utf8_general_ci comment = '酒店订单表';


-- linzequan 20191105
-- cloudbeds酒店表添加推荐字段
alter table `ko_cloudbeds_hotels` add recommend int(3) default 0 comment '是否推荐。0不推荐，1推荐到首页横幅，2推荐到首页瀑布流';


-- linzequan 20191106
-- cloudbeds酒店表添加状态字段
alter table `ko_cloudbeds_hotels` add `status` int default 0 comment '状态。0下架，1上架，-1删除。默认0';


-- linzequan 20191106
-- 添加首页轮播图表
create table `ko_banner` (
    `id` int not null auto_increment comment '自增id',
    `img` varchar(255) not null comment '图片地址',
    `link` varchar(255) default '' comment '跳转地址',
    `zorder` int default 100 comment '排序。数值越大越靠前，默认值100',
    `status` int default 0 comment '状态。0显示，1隐藏',
    primary key (`id`)
) engine myisam character set utf8 collate utf8_general_ci comment = '首页轮播图表';
-- 测试数据
insert into ko_banner(img, link) values('https://img1.qunarzz.com/order/comp/1805/2e/6e407f088bfb902.png', 'https://baidu.com');
insert into ko_banner(img, link) values('https://simg1.qunarzz.com/site/images/wap/home/recommend/20160509_banner_750x376.jpg', 'https://sina.com.cn');


-- linzequan 20191106
-- 添加优惠券配置表
create table `ko_coupon` (
    `id` int not null auto_increment comment '自增id',
    `totalAmount` decimal(20, 2) not null comment '满多少金额可以使用',
    `discountAmount` decimal(20, 2) not null comment '优惠多少金额',
    `validateDate` int not null comment '有效期多少天',
    `zorder` int default 100 comment '排序。数值越大越靠前，默认值100',
    `status` int default 0 comment '优惠券状态。0显示，1隐藏',
    primary key (`id`)
) engine myisam character set utf8 collate utf8_general_ci comment = '优惠券配置表';
-- 测试数据
insert into ko_coupon(totalAmount, discountAmount, validateDate) values(500.00, 50.00, 30);
insert into ko_coupon(totalAmount, discountAmount, validateDate) values(300.00, 30.00, 15);
insert into ko_coupon(totalAmount, discountAmount, validateDate) values(200.00, 20.00, 10);
insert into ko_coupon(totalAmount, discountAmount, validateDate) values(100.00, 10.00, 5);


-- linzequan 20191107
-- 添加酒店评论表
create table `ko_reviews` (
    `id` int not null auto_increment comment '自增id',
    `propertyID` int not null comment '酒店id',
    `userid` int(11) NOT NULL COMMENT '用户id',
    `openid` varchar(32) not null comment '用户openid',
    `orderId` varchar(64) not null comment '订单id',
    `rate` float(8,1) default 5.0 comment '评星，默认5星',
    `content` text comment '评论内容',
    `create_time` int comment '评论时间',
    `status` int default 0 comment '评论状态。0显示，1隐藏。默认0',
    primary key (`id`)
) engine myisam character set utf8 collate utf8_general_ci comment = '酒店评论表';
-- 测试数据
insert into ko_reviews(propertyID, userid, rate, content, create_time) values(173691, 1, 4.5, '我是评论内容1', 1573120221);
insert into ko_reviews(propertyID, userid, rate, content, create_time) values(173691, 1, 4.5, '我是评论内容2', 1573120221);
insert into ko_reviews(propertyID, userid, rate, content, create_time) values(173691, 1, 4.5, '我是评论内容3', 1573120221);
insert into ko_reviews(propertyID, userid, rate, content, create_time) values(173691, 1, 4.5, '我是评论内容4', 1573120221);


-- linzequan 20191107
-- 添加微信用户信息表
create table `ko_user` (
    `id` int not null auto_increment comment '自增id',
    `openid` varchar(32) not null comment '微信openid',
    `userinfo` varchar(512) default '' comment '微信用户详细信息',
    `wx_avatarUrl` varchar(512) default '' comment '微信头像',
    `wx_city` varchar(255) default '' comment '微信城市',
    `wx_province` varchar(255) default '' comment '微信省份',
    `wx_country` varchar(255) default '' comment '微信国家',
    `wx_sex` int(2) default 0 comment '微信性别',
    `wx_language` varchar(32) default '' comment '微信语言',
    `wx_nickname` varchar(255) default '' comment '微信昵称',
    `lang` varchar(32) default 'en' comment '用户使用小程序的预言。可选择en、cn，默认en英文',
    primary key (`id`)
) engine myisam character set utf8 collate utf8_general_ci comment = '微信用户信息表';


-- linzequan 20191107
-- 添加微信用户领取优惠券记录表
create table `ko_coupon_record` (
    `id` int not null auto_increment comment '自增id',
    `openid` varchar(32) not null comment '微信openid',
    `cid` int comment '优惠券id',
    `status` int default 0 comment '使用状态。0未使用，1已使用，默认0',
    `create_time` int comment '领取时间',
    primary key (`id`)
) engine myisam character set utf8 collate utf8_general_ci comment = '微信用户领取优惠券记录表';


-- linzequan 20191110
-- 添加grayline票据订单表
create table `ko_grayline_ticket` (
    `id` int not null auto_increment comment '自增id',
    `openid` varchar(32) not null comment '微信openid',
    `type` varchar(32) default '' comment '票据类型',
    `productId` int not null comment '产品id',
    `travelDate` varchar(32) default '' comment '旅游日期',
    `travelTime` varchar(32) default '' comment '旅游时间',
    `turbojetDepartureDate` varchar(32) default '' comment 'TurboJET departure date',
    `turbojetReturnDate` varchar(32) default '' comment 'TurboJET return date',
    `turbojetDepartureTime` varchar(32) default '' comment 'TurboJET departure time',
    `turbojetReturnTime` varchar(32) default '' comment 'TurboJET return time',
    `turbojetDepartureFrom` varchar(32) default '' comment 'TurboJET departure location (from)',
    `turbojetDepartureTo` varchar(32) default '' comment 'TurboJET departure location (to)',
    `turbojetReturnFrom` varchar(32) default '' comment 'TurboJET return location (from)',
    `turbojetReturnTo` varchar(32) default '' comment 'TurboJET return location (to)',
    `turbojetQuantity` varchar(32) default '' comment 'TurboJET ticket quantity',
    `turbojetClass` varchar(128) default '' comment 'TurboJET class. Allowed values:‘economy’, ‘super’, ‘primer-grand’',
    `turbojetTicketType` varchar(128) default '' comment 'TurboJET ticket type. Allowed values: ‘eboarding’, ‘voucher’',
    `turbojetDepartureFlightNo` varchar(128) default '' comment 'TurboJET departure flight no., necessary when queryProduct response provides turbojet.selected.departureAvailableFlight',
    `turbojetReturnFlightNo` varchar(128) default '' comment 'TurboJET Return flight no. , necessary when queryProduct response provides turbojet.selected.returnAvailableFlight',
    `hotel` varchar(256) default '' comment '居住的酒店',
    `title` varchar(512) default '' comment '称呼。可选项：Mr、Mrs、Miss',
    `firstName` varchar(128) default '' comment 'Guest First name, English characters only',
    `lastName` varchar(128) default '' comment 'Guest last name, English characters only',
    `passport` varchar(128) default '' comment 'Guest passport nationality code (refer to getNationalityList API)',
    `guestEmail` varchar(256) default '' comment '邮箱',
    `countryCode` varchar(256) default '' comment '手机国家编码',
    `telephone` varchar(32) default '' comment '手机号码',
    `promocode` varchar(32) default '' comment 'Promo Code',
    `agentReference` varchar(256) default '' comment 'Agent Reference number',
    `remark` varchar(512) default '' comment 'Remark',
    `subQtyProductPriceId` varchar(32) default '' comment 'ID. Quantity of selected product package, necessary when queryProduct response provides productPrice',
    `subQtyValue` varchar(32) default '' comment 'Value. Quantity of selected product package, necessary when queryProduct response provides productPrice',
    `totalPrice` varchar(32) default '' comment '总价',
    `info` varchar(512) default '' comment 'Agree to receive information about or from Gray Line Tours of Hong Kong Limited Allowed values: ‘1’ – agree、null – not agree',
    `orderParamsDetail` text comment '订单参数存档',
    `create_time` int comment '订单生成时间',
    `outTradeNo` varchar(64) comment '订单编号',
    `transaction_id` varchar(64) default '' comment '交易单号',
    `transaction_info` varchar(512) default '' comment '交易详情',
    `status` int default 0 comment '订单状态。0未支付，1已支付，2预定成功，-1订单取消',
    primary key (`id`)
) engine myisam character set utf8 collate utf8_general_ci comment = 'grayline票据订单表';


-- jiang 20191112
-- 添加中文地址字段
alter table `ko_cloudbeds_hotels_cn` add `propertyAddress` varchar(256) comment 'cloudbeds酒店地址中文';


-- linzequan 20191112
-- 添加门票价格信息字段
alter table `ko_grayline_ticket` add `subQty` text comment '门票价格信息';


-- linzequan 20191113
-- 添加系统异常日志表
create table `ko_errorlog` (
    `id` int not null auto_increment comment '自增id',
    `content` varchar(1024) not null comment '日志内容',
    `create_time` int(11) not null comment '日志记录时间',
    primary key (`id`)
) engine = myisam character set utf8 collate utf8_general_ci comment = '系统异常日志表';


-- linzequan 20191113
-- 修改交易详情字段
alter table ko_hotel_order change column transaction_info transaction_info  text comment '交易详情';
alter table ko_grayline_ticket change column transaction_info transaction_info  text comment '交易详情';


-- linzequan 20191113
-- 添加退款记录表
create table `ko_refund` (
    `id` int not null auto_increment comment '自增id',
    `status` int comment '退款状态',
    `info` text comment '退款接口返回详情',
    `create_time` int comment '退款时间',
    primary key (`id`)
) engine = myisam character set utf8 collate utf8_general_ci comment = '退款记录表';


-- linzequan 20191113
-- 酒店订单表添加优惠券id
alter table `ko_hotel_order` add column `coupon_id` int default 0 comment '所使用的优惠券id';


-- linzequan 20191113
-- 酒店订单表添加原价字段
alter table `ko_hotel_order` add column `source_prize` varchar(32) comment 'cloudbeds原价';


-- linzequan 20191113
-- 门票订单表添加原价字段
alter table `ko_grayline_ticket` add column `sourcePrice` varchar(32) default '' comment '原价';


-- linzequan 20191114
-- 添加短信验证码表
create table `ko_smscode` (
    `id` int(11) not null auto_increment comment '自增id',
    `phone` varchar(32) not null comment '手机号码',
    `code` varchar(32) not null comment '验证码',
    `is_check` int(3) default 0 comment '是否已经被验证。0否，1是',
    `ip` varchar(32) default '' comment 'ip地址',
    `sendlog` text comment '发送日志',
    `create_time` int(11) not null comment '创建时间戳',
    primary key (`id`)
) engine = myisam character set utf8 collate utf8_general_ci comment = '短信验证码表';


-- linzequan 20191115
-- 酒店订单表添加预订信息字段
alter table `ko_hotel_order` add column `reservationInfo` text comment '预订信息';


-- linzequan 20191116
-- 酒店订单表添加房间类型名称、房间类型描述、房间类型图片
alter table `ko_hotel_order` add column `rooms_roomTypeName` varchar(255) comment '房间类型名称';
alter table `ko_hotel_order` add column `rooms_roomTypeDesc` text comment '房间类型描述';
alter table `ko_hotel_order` add column `rooms_roomTypeImg` varchar(1024) comment '房间类型图片';


-- linzequan 20191117
-- 酒店订单表和门票订单表添加附加信息，供客户端缓存用
alter table `ko_hotel_order` add column `extinfo` text comment '酒店订单附加信息字段';
alter table `ko_grayline_ticket` add column `extinfo` text comment '门票订单表添加附加信息字段';


-- linzequan 20191117
-- 酒店订单添加销账记录表
create table `ko_payment_log` (
    `id` int not null auto_increment comment '自增id',
    `success` int comment '销账是否成功，0成功，1失败',
    `content` varchar(1024) not null comment '日志内容',
    `create_time` int(11) not null comment '日志记录时间',
    primary key (`id`)
) engine = myisam character set utf8 collate utf8_general_ci comment = '酒店订单销账记录表';


-- linzequan 20191118
-- 门票订单表添加优惠券id
alter table `ko_grayline_ticket` add column `coupon_id` int default 0 comment '所使用的优惠券id';


-- linzequan 20191126
-- 添加酒店房型信息表
create table `ko_cloudbeds_roomtypes` (
    `id` int not null auto_increment comment '自增id',
    `propertyID` int not null comment 'cloudbeds酒店id',
    `roomTypeID` int not null comment 'cloudbeds房型id',
    `roomTypeName` varchar(512) comment 'cloudbeds房型名称',
    `roomTypeNameShort` varchar(255) comment 'cloudbeds房型短名称',
    `roomTypeDescription` text comment 'cloudbeds房型简介',
    primary key (`id`)
) engine = myisam character set utf8 collate utf8_general_ci comment = '酒店房型信息表';


-- linzequan 20191126
-- 酒店房型信息更新记录表
create table `ko_cloudbeds_roomtypes_log` (
    `id` int not null auto_increment comment '自增id',
    `roomTypeID` int not null comment 'cloudbeds房型id',
    `status` int default 0 comment '是否已读状态。0未读，1已读',
    `create_time` int comment '添加时间',
    primary key (`id`)
) engine = myisam character set utf8 collate utf8_general_ci comment = '酒店房型信息更新记录表';


-- linzequan 20191126
-- 添加酒店房型中文信息表
create table `ko_cloudbeds_roomtypes_cn` (
    `id` int not null auto_increment comment '自增id',
    `rid` int not null comment '房型信息表id',
    `propertyID` int not null comment 'cloudbeds酒店id',
    `roomTypeID` int not null comment 'cloudbeds房型id',
    `roomTypeName` varchar(512) comment 'cloudbeds房型名称',
    `roomTypeNameShort` varchar(255) comment 'cloudbeds房型短名称',
    `roomTypeDescription` text comment 'cloudbeds房型简介',
    primary key (`id`)
) engine = myisam character set utf8 collate utf8_general_ci comment = '酒店房型中文信息表';


-- linzequan 20191129
-- 添加门票额外信息表
create table `ko_grayline_ticket_info` (
    `id` int not null auto_increment comment '自增id',
    `productId` int not null comment 'grayline门票id',
    `title` varchar(1024) comment '门票标题',
    `type` varchar(128) comment '类型',
    `introduce` text comment '门票介绍',
    `clause` text comment '门票条款',
    primary key (`id`)
) engine = myisam character set utf8 collate utf8_general_ci comment = '门票额外信息表';


-- linzequan 20191129
-- 添加门票额外信息中文表
create table `ko_grayline_ticket_info_cn` (
    `id` int not null auto_increment comment '自增id',
    `productId` int not null comment 'grayline门票id',
    `title` varchar(1024) comment '门票标题',
    `type` varchar(128) comment '类型',
    `introduce` text comment '门票介绍',
    `clause` text comment '门票条款',
    primary key (`id`)
) engine = myisam character set utf8 collate utf8_general_ci comment = '门票额外信息中文表';


-- linzequan 20191203
-- 修改用户表语言默认为中文
alter table ko_user modify column lang varchar(32) default 'zh-cn' comment '用户使用小程序的语言。可选择en、zh-cn，默认zh-cn中文';


-- linzequan 20191205
-- 添加cloudbeds地区表
create table `ko_cloudbeds_city` (
    `id` int not null auto_increment comment '自增id',
    `propertyCity` varchar(255) comment '城市英文名称',
    `status` int default 0 comment '状态。0隐藏，1显示。默认0隐藏',
    primary key (`id`)
) engine = myisam character set utf8 collate utf8_general_ci comment = 'cloudbeds地区英文表';


-- linzequan 20191205
-- 添加cloudbeds地区中文表
create table `ko_cloudbeds_city_cn` (
    `id` int not null auto_increment comment '自增id',
    `cid` int comment '对应英文表id',
    `propertyCity` varchar(255) comment '城市中文名称',
    primary key (`id`)
) engine = myisam character set utf8 collate utf8_general_ci comment = 'cloudbeds地区中文表';


-- linzequan 20191222
-- 添加门票信息表
create table `ko_grayline_ticket_info_v2` (
    `id` int not null auto_increment comment '自增id',
    `productId` int not null comment 'grayline门票id',
    `title` varchar(1024) comment '标题',
    `code` varchar(128) comment '产品编码',
    `image` varchar(512) comment '产品图片',
    `type` varchar(32) comment '类型',
    `introduce` text comment '门票介绍',
    `clause` text comment '门票条款',
    `status` int default 0 comment '状态。0下架，1上架，-1删除。默认0',
    primary key (`id`)
) engine = myisam character set utf8 collate utf8_general_ci comment = '门票信息表v2';


-- linzequan 20191222
-- 添加门票中文信息表
create table `ko_grayline_ticket_info_cn_v2` (
    `id` int not null auto_increment comment '自增id',
    `tiid` int not null comment '门票英文信息表id',
    `productId` int not null comment 'grayline门票id',
    `title` varchar(1024) comment '标题',
    `introduce` text comment '门票介绍',
    `clause` text comment '门票条款',
    primary key (`id`)
) engine = myisam character set utf8 collate utf8_general_ci comment = '门票中文信息表v2';
