<?php
/**
 * cloudbeds 酒店模型
 *
 * @author linzequan <lowkey361@gmail.com>
 *
 */
class Cloudbeds_hotel_model extends MY_Model {

    private $table = 'ko_cloudbeds_hotels';
    private $fields = 'id, propertyID, propertyName, propertyImage, propertyImageThumb, propertyPhone, propertyEmail, propertyAddress1, propertyAddress2, propertyCity, propertyState, propertyZip, propertyCountry, propertyLatitude, propertyLongitude, propertyCheckInTime, propertyCheckOutTime, propertyLateCheckOutAllowed, propertyLateCheckOutType, propertyLateCheckOutValue, propertyTermsAndConditions, propertyAmenities, propertyDescription, propertyTimezone, propertyCurrencyCode, propertyCurrencySymbol, propertyCurrencyPosition';

    public function __construct() {
        parent::__construct();
    }


    /**
     * 抓取cloudbeds酒店
     **/
    public function fetch_hotels() {
        $pageSize = 20;
        $pageNumber = 1;
        $logArr = array();
        $hasNext = true;
        while($hasNext) {
            $access_token_result = $this->update_cloudbeds_access_token();
            if($access_token_result['status']) {
                return array(
                    'status'    => -1,
                    'msg'       => $access_token_result['msg']
                );
            }
            $url = 'https://hotels.cloudbeds.com/api/v1.1/getHotels?pageSize=' . $pageSize . '&pageNumber=' . $pageNumber;
            $apiReturnStr = $this->https_request_cloudbeds($url, $access_token_result['data']['access_token']);
            if(isset($apiReturnStr['success']) && !!$apiReturnStr['success']) {
                // 判断当前是否爬完所有分页数据，是的话则将循环标志位置为false
                if($apiReturnStr['total'] <= $pageSize * $pageNumber) {
                    $hasNext = false;
                } else {
                    $pageNumber++;
                }
                foreach($apiReturnStr['data'] as $k=>$v) {
                    // 判断当前记录是否已经被记录到数据库中
                    $hasExist = $this->checkExistByPropertyID($v['propertyID']);
                    if(!$hasExist) {
                        // 记录不存在，通过详情接口抓取更多信息
                        $hotelDetails = $this->getHotelDetails($v['propertyID']);
                        $imageArr = array();
                        $imageThumbArr = array();
                        foreach($hotelDetails['propertyImage'] as $x=>$y) {
                            $imageArr[] = $y['image'];
                            $imageThumbArr[] = $y['thumb'];
                        }
                        $image = implode(',', $imageArr);
                        $imageThumb = implode(',', $imageThumbArr);
                        $params = array(
                            'propertyID'    => $v['propertyID'],
                            'propertyName'  => $v['propertyName'],
                            'propertyImage' => $image,
                            'propertyImageThumb'    => $imageThumb,
                            'propertyPhone' => $hotelDetails['propertyPhone'],
                            'propertyEmail' => $hotelDetails['propertyEmail'],
                            'propertyAddress1'      => $hotelDetails['propertyAddress']['propertyAddress1'],
                            'propertyAddress2'      => $hotelDetails['propertyAddress']['propertyAddress2'],
                            'propertyCity'          => $hotelDetails['propertyAddress']['propertyCity'],
                            'propertyState'         => $hotelDetails['propertyAddress']['propertyState'],
                            'propertyZip'           => $hotelDetails['propertyAddress']['propertyZip'],
                            'propertyCountry'       => $hotelDetails['propertyAddress']['propertyCountry'],
                            'propertyLatitude'      => $hotelDetails['propertyAddress']['propertyLatitude'],
                            'propertyLongitude'     => $hotelDetails['propertyAddress']['propertyLongitude'],
                            'propertyCheckInTime'   => $hotelDetails['propertyPolicy']['propertyCheckInTime'],
                            'propertyCheckOutTime'  => $hotelDetails['propertyPolicy']['propertyCheckOutTime'],
                            'propertyLateCheckOutAllowed'   => $hotelDetails['propertyPolicy']['propertyLateCheckOutAllowed'],
                            'propertyLateCheckOutType'      => $hotelDetails['propertyPolicy']['propertyLateCheckOutType'],
                            'propertyLateCheckOutValue'     => $hotelDetails['propertyPolicy']['propertyLateCheckOutValue'],
                            'propertyTermsAndConditions'    => $hotelDetails['propertyPolicy']['propertyTermsAndConditions'],
                            'propertyAmenities'     => implode(',', $hotelDetails['propertyAmenities']),
                            'propertyDescription'   => $hotelDetails['propertyDescription'],
                            'propertyTimezone'      => $v['propertyTimezone'],
                            'propertyCurrencyCode'  => $v['propertyCurrency']['currencyCode'],
                            'propertyCurrencySymbol'        => $v['propertyCurrency']['currencySymbol'],
                            'propertyCurrencyPosition'      => $v['propertyCurrency']['currencyPosition']
                        );
                        // 入库前再次进行不存在确认
                        if(!$this->checkExistByPropertyID($v['propertyID'])) {
                            $this->db->insert($this->table, $params);
                            @file_put_contents('/pub/logs/fetch_hotels/' . date('Y-m-d', time()), '[' . date('Y-m-d H:i:s', time()) . '](' . $v['propertyID'] . ')==> ' . json_encode($params) . PHP_EOL, FILE_APPEND);
                            @file_put_contents('/pub/logs/fetch_hotels_success/' . date('Y-m', time()), '[' . date('Y-m-d H:i:s', time()) . '](' . $v['propertyID'] . ')==> ' . json_encode($params) . PHP_EOL, FILE_APPEND);
                            $logArr[] = '[' . date('Y-m-d H:i:s', time()) . '](' . $v['propertyID'] . ')==> ' . json_encode($params);
                        } else {
                            // 记录存在，写入日志文件
                            @file_put_contents('/pub/logs/fetch_hotels/' . date('Y-m-d', time()), '[' . date('Y-m-d H:i:s', time()) . '](' . $v['propertyID'] . ')==> 记录已经存在' . PHP_EOL, FILE_APPEND);
                            $logArr[] = '[' . date('Y-m-d H:i:s', time()) . '](' . $v['propertyID'] . ')==> 记录已经存在';
                        }
                    } else {
                        // 记录存在，写入日志文件
                        @file_put_contents('/pub/logs/fetch_hotels/' . date('Y-m-d', time()), '[' . date('Y-m-d H:i:s', time()) . '](' . $v['propertyID'] . ')==> 记录已经存在' . PHP_EOL, FILE_APPEND);
                        $logArr[] = '[' . date('Y-m-d H:i:s', time()) . '](' . $v['propertyID'] . ')==> 记录已经存在';
                    }
                }
            }
        }
        return $logArr;
    }


    /**
     * 获取酒店详情
     **/
    public function getHotelDetails($propertyID = 0) {
        $access_token_result = $this->update_cloudbeds_access_token();
        if($access_token_result['status']) {
            return false;
        }
        $url = 'https://hotels.cloudbeds.com/api/v1.1/getHotelDetails?propertyID=' . $propertyID;
        $apiReturnStr = $this->https_request_cloudbeds($url, $access_token_result['data']['access_token']);
        if(isset($apiReturnStr['success']) && !!$apiReturnStr['success']) {
            return $apiReturnStr['data'];
        } else {
            return false;
        }
    }


    /**
     * 检查酒店是否已经记录在数据库中
     **/
    public function checkExistByPropertyID($propertyID) {
        $query = $this->db->query('select ' . $this->fields . ' from ' . $this->table . ' where propertyID = ' . $propertyID);
        $result = $query->result_array();
        if(count($result) > 0) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * 查询推荐列表
     **/
    public function getRecommend($type = 0, $num = 10) {
        if($type == 0) {
            return array(
                'status'    => -1,
                'msg'       => '推荐位置不可为空'
            );
        }
        $query = $this->db->query('select ' . $this->fields . ' from ' . $this->table . ' where `recommend` = ' . $type . ' order by id desc limit 0, ' . $num);
        $result = $query->result_array();
        return array(
            'status'    => 0,
            'msg'       => '查询成功',
            'data'      => $result
        );
    }


    /**
     * 获取首页推荐列表瀑布流
     **/
    public function getRecommendFlow($page = 1, $num = 10) {
        $query = $this->db->query('select ' . $this->fields . ' from ' . $this->table . ' where `recommend` = 1 order by id desc limit ' . ($page - 1) * $num . ' , ' . $num);
        $result = $query->result_array();
        if(count($result) > 0) {
            $rtn = array(
                'status'    => 0,
                'msg'       => '查询成功',
                'data'      => $result
            );
        } else {
            $rtn = array(
                'status'    => -1,
                'msg'       => '没有更多数据'
            );
        }
        return $rtn;
    }


    /**
     * 获取酒店房型
     **/
    public function getRoomTypes($propertyIDs) {
        $access_token_result = $this->update_cloudbeds_access_token();
        if($access_token_result['status']) {
            return array(
                'status'    => -1,
                'msg'       => $access_token_result['msg']
            );
        }
        $url = 'https://hotels.cloudbeds.com/api/v1.1/getRoomTypes?propertyIDs=' . $propertyIDs;
        $apiReturnStr = $this->https_request_cloudbeds($url, $access_token_result['data']['access_token']);
        if(isset($apiReturnStr['success']) && !!$apiReturnStr['success']) {
            return array(
                'status'    => 0,
                'msg'       => '查询成功',
                'data'      => $apiReturnStr['data']
            );
        } else {
            return array(
                'status'    => -2,
                'msg'       => $apiReturnStr['message']
            );
        }
    }


    /**
     * 查询城市列表数据
     */
    public function getCitys() {
        $query = $this->db->query('select distinct `propertyCity` from ' . $this->table);
        $result = $query->result_array();
        if(count($result) > 0) {
            return array(
                'status'    => 0,
                'msg'       => '查询成功',
                'data'      => $result
            );
        } else {
            return array(
                'status'    => -1,
                'msg'       => '没有数据'
            );
        }
    }


    /**
     * 搜索酒店
     */
    public function searchHotels($params) {
        // 获取酒店列表，如果筛选条件有传入酒店名称hotelName或者城市city，则作为入参
        $hotels = $this->getHotels($params['hotelName'], $params['city']);
        if($hotels['status'] != 0) {
            return array(
                'status'    => -1,
                'msg'       => '酒店查询失败'
            );
        }
        // 通过筛选完成后的酒店，获取酒店propertyID，进行房间筛选
        $hotelIdArr = array();
        foreach($hotels['data'] as $k=>$v) {
            $hotelIdArr[] = $v['propertyID'];
        }
        $hotelIdStr = implode(',', $hotelIdArr);
        $availableRoomTypes = $this->getAvailableRoomTypes($hotelIdStr, $params['checkInDate'], $params['checkOutDate']);
        if($availableRoomTypes['status'] != 0) {
            return array(
                'status'    => -2,
                'msg'       => '酒店查询失败'
            );
        }
        // 获取酒店下所有房型的价格
        $amount = array();
        foreach($availableRoomTypes['data'] as $k=>$v) {
            $tmp = array();
            foreach($v['propertyRooms'] as $x=>$y) {
                // 判断是否价格区间，有的话则需要过滤
                if($params['priceStart'] > 0 && $params['priceStart'] > $y['roomRate']) {
                    continue;
                }
                if($params['priceEnd'] > 0 && $params['priceEnd'] < $y['roomRate']) {
                    continue;
                }
                $tmp[$y['roomTypeID']] = $y['roomRate'];
            }
            $amount[$v['propertyID']] = $tmp;
        }
        // 是否需要按照价格排序，0不排序，1升序，2降序
        $minAmount = array();
        $maxAmount = array();
        foreach($amount as $k=>$v) {
            $minAmount[$k] = min($v);
            $maxAmount[$k] = max($v);
        }
        $newHotels = array();
        if(isset($params['moneySort']) && $params['moneySort'] == 0) {
            $newHotels = $hotels;
        }
        if($params['moneySort'] == 1) {
            asort($minAmount);
            foreach($minAmount as $k=>$v) {
                $newHotels[] = $this->getHotelByPropertyIDWithSource($k, $hotels['data']);
            }
        }
        if($params['moneySort'] == 2) {
            arsort($maxAmount);
            foreach($maxAmount as $k=>$v) {
                $newHotels[] = $this->getHotelByPropertyIDWithSource($k, $hotels['data']);
            }
        }
        // 往酒店列表中塞入最低价格
        foreach($newHotels as $k=>$v) {
            $newHotels[$k]['minMoney'] = $minAmount[$v['propertyID']];
            $newHotels[$k]['maxMoney'] = $maxAmount[$v['propertyID']];
        }
        // 获取酒店详情
        foreach($newHotels as $k=>$v) {
            $newHotels[$k]['details'] = $this->getHotelDetailsInDB($v['propertyID']);
        }
        return array(
            'status'    => 0,
            'msg'       => '查询成功',
            'data'      => $newHotels
        );
    }


    /**
     * 从数据库中检索酒店信息
     */
    public function getHotelDetailsInDB($propertyID) {
        $query = $this->db->query('select ' . $this->fields . ' from ' . $this->table . ' where `propertyID` = ' . $propertyID);
        $result = $query->result_array();
        if(count($result) > 0) {
            return $result[0];
        } else {
            return array();
        }
    }


    /**
     * 通过propertyID在传入的原数组数据中查找酒店
     */
    public function getHotelByPropertyIDWithSource($propertyID, $source) {
        foreach($source as $k=>$v) {
            if($v['propertyID'] == $propertyID) {
                return $v;
            }
        }
        return false;
    }


    /**
     * 筛选酒店
     */
    public function getHotels($hotelName='', $city='') {
        $access_token_result = $this->update_cloudbeds_access_token();
        if($access_token_result['status']) {
            return array(
                'status'    => -1,
                'msg'       => $access_token_result['msg']
            );
        }
        $filters = array();
        if(!!$hotelName) {
            $filters[] = 'propertyName=' . str_replace(' ', '%20', $hotelName);
        }
        if(!!$city) {
            $filters[] = 'propertyCity=' . str_replace(' ', '%20', $city);
        }
        $filtersStr = '';
        if(count($filters) > 0) {
            $filtersStr = '?' . implode('&', $filters);
        }
        $url = 'https://hotels.cloudbeds.com/api/v1.1/getHotels' . $filtersStr;
        $apiReturnStr = $this->https_request_cloudbeds($url, $access_token_result['data']['access_token']);
        if(isset($apiReturnStr['success']) && !!$apiReturnStr['success']) {
            return array(
                'status'    => 0,
                'msg'       => '查询成功',
                'data'      => $apiReturnStr['data']
            );
        } else {
            return array(
                'status'    => -2,
                'msg'       => $apiReturnStr['message']
            );
        }
    }


    /**
     * 筛选可用的房间
     */
    public function getAvailableRoomTypes($propertyIDs, $checkInDate, $checkOutDate) {
        $access_token_result = $this->update_cloudbeds_access_token();
        if($access_token_result['status']) {
            return array(
                'status'    => -1,
                'msg'       => $access_token_result['msg']
            );
        }
        $filters = array('pageNumber=1', 'pageSize=100000');
        if(!!$propertyIDs) {
            // $propertyIDs = '173691,169762,26846,173694';
            $filters[] = 'propertyIDs=' . str_replace(' ', '%20', $propertyIDs);
        }
        if(!!$checkInDate) {
            $filters[] = 'startDate=' . str_replace(' ', '%20', $checkInDate);
        }
        if(!!$checkOutDate) {
            $filters[] = 'endDate=' . str_replace(' ', '%20', $checkOutDate);
        }
        $filtersStr = '';
        if(count($filters) > 0) {
            $filtersStr = '?' . implode('&', $filters);
        }
        $url = 'https://hotels.cloudbeds.com/api/v1.1/getAvailableRoomTypes' . $filtersStr;
        $apiReturnStr = $this->https_request_cloudbeds($url, $access_token_result['data']['access_token']);
        if(isset($apiReturnStr['success']) && !!$apiReturnStr['success']) {
            return array(
                'status'    => 0,
                'msg'       => '查询成功',
                'data'      => $apiReturnStr['data']
            );
        } else {
            return array(
                'status'    => -2,
                'msg'       => $apiReturnStr['message']
            );
        }
    }

}
