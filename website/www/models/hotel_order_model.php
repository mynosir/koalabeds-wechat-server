<?php
/**
 * 酒店订单模型
 *
 * @author linzequan <lowkey361@gmail.com>
 *
 */
class hotel_order_model extends MY_Model {

    private $table = 'ko_hotel_order';
    private $payment_log_table = 'ko_payment_log';
    private $fields = 'id, openid, propertyID, startDate, endDate, guestFirstName, guestLastName, guestCountry, guestZip, guestEmail, guestPhone, rooms, rooms_roomTypeID, rooms_quantity, adults, adults_roomTypeID, adults_quantity, children, children_roomTypeID, children_quantity, status, total, frontend_total, balance, balanceDetailed, assigned, unassigned, cardsOnFile, reservationID, estimatedArrivalTime, outTradeNo, transaction_id, transaction_info, coupon_id, reservationInfo, rooms_roomTypeName, rooms_roomTypeDesc, rooms_roomTypeImg, extinfo';
    private $payment_log_fields = 'id, success, content, create_time';

    public function __construct() {
        parent::__construct();
    }


    /**
     * 生成订单
     */
    public function generateOrder($params) {
        $data = array(
            'openid'        => $params['openid'],
            'propertyID'    => $params['propertyID'],
            'startDate'     => $params['startDate'],
            'endDate'       => $params['endDate'],
            'guestFirstName'    => $params['guestFirstName'],
            'guestLastName' => $params['guestLastName'],
            'guestCountry'  => $params['guestCountry'],
            'guestZip'      => $params['guestZip'],
            'guestEmail'    => $params['guestEmail'],
            'guestPhone'    => $params['guestPhone'],
            'rooms'         => json_encode($params['rooms']),
            'rooms_roomTypeID'  => $params['rooms']['roomTypeID'],
            'rooms_quantity'    => isset($params['rooms']['quantity']) ? $params['rooms']['quantity'] : 0,
            'adults'        => json_encode($params['adults']),
            'adults_roomTypeID' => $params['adults']['roomTypeID'],
            'adults_quantity'   => isset($params['adults']['quantity']) ? $params['adults']['quantity'] : 0,
            'children'      => json_encode($params['children']),
            'children_roomTypeID'   => $params['children']['roomTypeID'],
            'children_quantity' => json_encode($params['children']['quantity']) ? $params['children']['quantity'] : 0,
            'status'        => 0,
            'frontend_total'=> $params['frontend_total'],
            // 'total'         => $params['frontend_total'],
            'outTradeNo'    => $params['outTradeNo'],
            'coupon_id'     => $params['coupon_id'],
            'rooms_roomTypeName'    => $params['rooms_roomTypeName'],
            'rooms_roomTypeDesc'    => $params['rooms_roomTypeDesc'],
            'rooms_roomTypeImg'     => $params['rooms_roomTypeImg'],
            'extinfo'       => $params['extinfo']
        );
        // 查询房间对应金额
        $this->load->model('cloudbeds_hotel_model');
        $CI = &get_instance();
        $rate = $CI->cloudbeds_hotel_model->getRoomsFeesAndTaxes($data['startDate'], $data['endDate'], $params['unRoomRate'], $data['rooms_quantity'], $data['propertyID']);
        if($rate['status'] != 0) {
            return array(
                'status'    => -1,
                'msg'       => $rate['msg']
            );
        }
        @file_put_contents('/pub/logs/getPayParams_tmp1', json_encode($rate) . '------' . $rate['data']['grandTotal'] . '+++++++' . $params['source_prize']);
        $data['total'] = $rate['data']['grandTotal'];
        if($params['source_prize'] != $rate['data']['grandTotal']) {
            return array(
                'status'    => -2,
                'msg'       => '价格异常'
            );
        }
        $data['total'] = $params['total_fee'];
        $data['source_prize'] = $params['source_prize'];
        // 变更优惠券状态
        if(isset($params['coupon_id']) && $params['coupon_id'] > 0) {
            $this->load->model('coupon_model');
            $CI->coupon_model->updateStatus($params['coupon_id'], 1);
        }
        $this->db->insert($this->table, $data);
        $insertId = $this->db->insert_id();
        return array(
            'status'    => 0,
            'msg'       => '订单生成成功',
            'data'      => array(
                'id'    => $insertId
            )
        );
    }


    /**
     * 保存交易信息
     */
    public function update_transaction_info($out_trade_no, $transaction_info) {
        $transaction_info_obj = json_decode($transaction_info, true);
        $where = array(
            'outTradeNo'    => $out_trade_no
        );
        $data = array(
            'transaction_id'    => $transaction_info_obj['transaction_id'],
            'transaction_info'  => $transaction_info
        );
        $this->db->where($where)->update($this->table, $data);
    }


    /**
     * 更新订单状态
     */
    public function update_status($out_trade_no, $status) {
        $where = array(
            'outTradeNo'    => $out_trade_no
        );
        $data = array(
            'status'    => $status
        );
        $this->db->where($where)->update($this->table, $data);
    }


    /**
     * 通过id更新订单状态
     */
    public function updateStatusById($id, $status) {
        $where = array(
            'id'    => $id
        );
        $data = array(
            'status'    => $status
        );
        $this->db->where($where)->update($this->table, $data);
        return array(
            'status'    => 0,
            'msg'       => '更新成功'
        );
    }


    /**
     * 查找订单详情
     */
    public function getDetailById($id) {
        $query = $this->db->query('select ' . $this->fields . ' from ' . $this->table . ' where id = ' . $id);
        $result = $query->result_array();
        if(count($result) > 0) {
            $CI = &get_instance();
            $this->load->model('cloudbeds_hotel_model');
            $result[0]['hotelInfo'] = $CI->cloudbeds_hotel_model->getHotelDetailsInDB($result[0]['propertyID']);
            return array(
                'status'    => 0,
                'msg'       => '查询成功',
                'data'      => $result[0]
            );
        } else {
            return array(
                'status'    => -1,
                'msg'       => '未查找到对应订单信息'
            );
        }
    }


    /**
     * 通过订单编号查找订单详情
     */
    public function getDetailByOutTradeNo($outTradeNo) {
        $query = $this->db->query('select ' . $this->fields . ' from ' . $this->table . ' where outTradeNo = "' . $outTradeNo . '"');
        $result = $query->result_array();
        if(count($result) > 0) {
            $CI = &get_instance();
            $this->load->model('cloudbeds_hotel_model');
            $result[0]['hotelInfo'] = $CI->cloudbeds_hotel_model->getHotelDetailsInDB($result[0]['propertyID']);
            return array(
                'status'    => 0,
                'msg'       => '查询成功',
                'data'      => $result[0]
            );
        } else {
            return array(
                'status'    => -1,
                'msg'       => '未查找到对应订单信息'
            );
        }
    }


    /**
     * 根据微信openid获取订单列表
     */
    public function getListByOpenid($openid, $status = -2) {
        $whereStr = ' openid = "' . $openid . '" ';
        if($status != -2) {
            $whereStr = ' openid = "' . $openid . '" and status = ' . $status . ' ';
        }
        $query = $this->db->query('select ' . $this->fields . ' from ' . $this->table . ' where ' . $whereStr . ' order by id desc');
        $result = $query->result_array();
        if(count($result) > 0) {
            $CI = &get_instance();
            $this->load->model('cloudbeds_hotel_model');
            foreach($result as $k=>$v) {
                $result[$k]['hotelInfo'] = $CI->cloudbeds_hotel_model->getHotelDetailsInDB($v['propertyID']);
            }
            return array(
                'status'    => 0,
                'msg'       => '查询成功',
                'data'      => $result
            );
        } else {
            return array(
                'status'    => -1,
                'msg'       => '未查找到对应订单信息'
            );
        }
    }


    /**
     * 保存酒店订单
     */
    public function saveOrder($openid, $id) {
        $access_token_result = $this->update_cloudbeds_access_token();
        if($access_token_result['status']) {
            return array(
                'status'    => -1,
                'msg'       => $access_token_result['msg']
            );
        }
        $url = 'https://hotels.cloudbeds.com/api/v1.1/postReservation';
        // 查询订单信息
        $orderDetail = $this->getDetailById($id);
        if($orderDetail['status'] != 0) {
            return array(
                'status'    => -2,
                'msg'       => $orderDetail['msg']
            );
        }
        $orderDetail = $orderDetail['data'];
        $roomInfo = json_decode($orderDetail['rooms'], true);
        $adultsInfo = json_decode($orderDetail['adults'], true);
        $children = json_decode($orderDetail['children'], true);
        $data = array(
            'propertyID'    => $orderDetail['propertyID'],
            'startDate'     => $orderDetail['startDate'],
            'endDate'       => $orderDetail['endDate'],
            'guestFirstName'=> $orderDetail['guestFirstName'],
            'guestLastName' => $orderDetail['guestLastName'],
            'guestCountry'  => $orderDetail['guestCountry'],
            'guestZip'      => $orderDetail['guestZip'],
            'guestEmail'    => $orderDetail['guestEmail'],
            'rooms'         => array(array(
                'roomTypeID'    => $orderDetail['rooms_roomTypeID'],
                'quantity'      => $orderDetail['rooms_quantity'],
                'rate'          => isset($roomInfo['rate']) ? $roomInfo['rate'] : 0
            )),
            'adults'        => array(array(
                'roomTypeID'    => $orderDetail['adults_roomTypeID'],
                'quantity'      => $orderDetail['adults_quantity'],
                'rate'          => isset($adultsInfo['rate']) ? $adultsInfo['rate'] : 0
            )),
            'children'      => array(array(
                'roomTypeID'    => $orderDetail['children_roomTypeID'],
                'quantity'      => $orderDetail['children_quantity'],
                'rate'          => isset($children['rate']) ? $children['rate'] : 0
            )),
            'paymentMethod' => 'cash',
            'source'        => 'WeChat'
        );
        $apiReturnStr = $this->https_request_cloudbeds($url, $access_token_result['data']['access_token'], $data, true);
        // array(13) { ["success"]=> bool(true) ["reservationID"]=> string(12) "842706099534" ["status"]=> string(9) "confirmed" ["guestID"]=> int(26820944) ["guestFirstName"]=> string(6) "zequan" ["guestLastName"]=> string(3) "lin" ["guestGender"]=> string(3) "N/A" ["guestEmail"]=> string(16) "361789273@qq.com" ["startDate"]=> string(10) "2019-11-10" ["endDate"]=> string(10) "2019-11-13" ["dateCreated"]=> string(19) "2019-11-07 15:52:29" ["grandTotal"]=> int(900) ["unassigned"]=> array(1) { [0]=> array(7) { ["subReservationID"]=> string(12) "842706099534" ["roomTypeName"]=> string(29) "4 Guests Ensuite with Windows" ["roomTypeID"]=> int(197686) ["adults"]=> int(1) ["children"]=> int(0) ["dailyRates"]=> array(3) { [0]=> array(2) { ["date"]=> string(10) "2019-11-10" ["rate"]=> int(300) } [1]=> array(2) { ["date"]=> string(10) "2019-11-11" ["rate"]=> int(300) } [2]=> array(2) { ["date"]=> string(10) "2019-11-12" ["rate"]=> int(300) } } ["roomTotal"]=> int(900) } } }
        // 根据不同返回状态更新订单状态
        @file_put_contents('/pub/logs/saveOrderParams', '[' . date('Y-m-d H:i:s', time()) . '](' . json_encode($data) . PHP_EOL, FILE_APPEND);
        @file_put_contents('/pub/logs/saveOrder', '[' . date('Y-m-d H:i:s', time()) . '](' . json_encode($apiReturnStr) . PHP_EOL, FILE_APPEND);
        if(isset($apiReturnStr['success']) && !!$apiReturnStr['success']) {
            $this->update_status($orderDetail['outTradeNo'], 2);
            $this->update_reservation($orderDetail['outTradeNo'], $apiReturnStr);
            return array(
                'status'    => 0,
                'msg'       => '预订成功',
                'data'      => $apiReturnStr
            );
        } else {
            $this->update_status($orderDetail['outTradeNo'], -1);
            // 预订失败，发起退款
            // todo
            return array(
                'status'    => -2,
                'msg'       => $apiReturnStr['message'],
                'data'      => $orderDetail
            );
        }
    }


    /**
     * 更新预订信息
     */
    public function update_reservation($outTradeNo, $info) {
        $data = array(
            'reservationID'     => $info['reservationID'],
            'reservationInfo'   => json_encode($info),
            'create_time'       => time()
        );
        $where = array(
            'outTradeNo'    => $outTradeNo
        );
        $this->db->where($where)->update($this->table, $data);
    }


    /**
     * 酒店订单销账
     */
    public function postPayment($propertyID, $reservationID, $amount) {
        $access_token_result = $this->update_cloudbeds_access_token();
        if($access_token_result['status']) {
            return array(
                'status'    => -1,
                'msg'       => $access_token_result['msg']
            );
        }
        $url = 'https://hotels.cloudbeds.com/api/v1.1/postPayment';
        $data = array(
            'propertyID'        => $propertyID,
            'reservationID'     => $reservationID,
            'type'              => 'Paid at another location.',
            'amount'            => $amount,
            'description'       => 'from Koalabeds mini program'
            // 'cardType'          => 'visa'
        );
        @file_put_contents('/pub/logs/postPaymentParams', '[' . date('Y-m-d H:i:s', time()) . ']' . json_encode($data) . PHP_EOL, FILE_APPEND);
        $apiReturnStr = $this->https_request_cloudbeds($url, $access_token_result['data']['access_token'], $data, true);
        @file_put_contents('/pub/logs/postPayment', '[' . date('Y-m-d H:i:s', time()) . ']' . json_encode($apiReturnStr) . PHP_EOL, FILE_APPEND);
        // 写入销账表
        if(isset($apiReturnStr['success']) && !!$apiReturnStr['success']) {
            $params = array(
                'success'   => 0,
                'content'   => $apiReturnStr,
                'create_time'   => time()
            );
            $this->db->insert($this->payment_log_table, $data);
            return array(
                'status'    => 0,
                'msg'       => '操作成功',
                'data'      => $params
            );
        } else {
            $params = array(
                'success'   => 1,
                'content'   => $apiReturnStr,
                'create_time'   => time()
            );
            $this->db->insert($this->payment_log_table, $data);
            return array(
                'status'    => -1,
                'msg'       => '操作失败',
                'data'      => $params
            );
        }
    }

}
