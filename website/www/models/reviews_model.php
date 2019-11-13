<?php
/**
 * 评论模型
 *
 * @author linzequan <lowkey361@gmail.com>
 *
 */
class reviews_model extends MY_Model {

    private $table = 'ko_reviews';
    private $fields = 'id, propertyID, userid, rate, content, create_time, status';

    public function __construct() {
        parent::__construct();
    }


    /**
     * 保存记录
     **/
    public function add($params) {
        $data = array(
            'propertyID'    => $params['propertyID'],
            'userid'        => $params['userid'],
            'rate'          => $params['rate'],
            'content'       => $params['content'],
            'create_time'   => time()
        );
        $this->db->insert($this->table, $params);
        $result = array(
            'status'    => 0,
            'msg'       => '保存成功！'
        );
        return $result;
    }


    /**
     * 获取酒店平均评价
     */
    public function getReviewsRate($propertyID) {
        $query = $this->db->query('select ' . $this->fields . ' from ' . $this->table . ' where status=0 and propertyID=' . $propertyID);
        $result = $query->result_array();
        $rate = 5;
        $sum = 0;
        $count = 0;
        foreach($result as $k=>$v) {
            $sum += $v['rate'];
            $count += 1;
        }
        if($count > 0) {
            $rate = round($sum / $count, 1);
        }
        $result = array(
            'rate'      => $rate,
            'rateNum'   => $count
        );
        return array(
            'status'    => 0,
            'msg'       => '查询成功',
            'data'      => $result
        );
    }
}
