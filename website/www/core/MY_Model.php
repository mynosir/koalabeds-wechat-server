<?php
/**
 * 应用基础模型（初始化分库访问）
 *
 * @author linzequan <lowkey361@gmail.com>
 *
 */
class MY_Model extends CI_Model {

    /**
     * 初始化分库访问静态变量
     */
    public function __construct(){
        parent::__construct();
    }


    /**
     * 获取用户请求ip地址
     * @return [type] [description]
     */
    public function getIP() {
        $ip = '0.0.0.0';
        if(isset($_SERVER['HTTP_X_REAL_IP'])) {
            // nginx 代理模式下，获取客户端真实ip
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        } elseif(isset($_SERVER['HTTP_CLIENT_IP'])) {
            // 客户端的ip
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // 浏览当前页面的用户计算机的网关
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if(false!==$pos) unset($arr[$pos]);
            $ip = trim($arr[0]);
        } elseif(isset($_SERVER['REMOTE_ADDR'])) {
            // 浏览当前页面的用户计算机的ip地址
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }


    /**
     * 标准化返回结果
     *
     * @param string $success
     * @param number $error
     * @param mixed $data
     * @return string array(success=>false,error=>0,data=>'')
     */
    protected function create_result($success=false, $error=0, $data='') {
        return array('success'=>$success, 'error'=>$error, 'data'=>$data);
        exit;
    }


    /**
     * 发起https请求
     * @param  [type] $url  [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function https_request($url, $data=null) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if(!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }


    /**
     * 发起https请求(cloudbeds专用)
     * @param  [type] $url  [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function https_request_cloudbeds($url, $access_token, $data=null, $httpBuildQuery=false) {
        $curl = curl_init();
        // $access_token = 'FkqeKbMe7vZxyc9Ymoanc5YoRuFD1MC9QDk2ojvR';
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Bearer '. $access_token));
        if(!empty($data)) {
            if(!!$httpBuildQuery) $data = http_build_query($data);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = json_decode(curl_exec($curl), true);
        curl_close($curl);
        return $output;
    }


    /**
     * 发起http请求(grayline)
     * @param  [type] $url  [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function http_request_grayline($url, $data=null, $httpBuildQuery=false) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        if(!empty($data)) {
            if(!!$httpBuildQuery) $data = http_build_query($data);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = json_decode(curl_exec($curl), true);
        curl_close($curl);
        return $output;
    }


    /**
     * 更新cloudbeds access token
     **/
    public function update_cloudbeds_access_token() {
        // 判断是否在更新请求中
        $lockFile = '/pub/koalabeds-server.kakaday.com/update_cloudbeds_access_token.lock';
        if(file_exists($lockFile)) {
            $lockStatus = file_get_contents($lockFile);
            if($lockStatus == 1) {
                $rtn = array(
                    'status'    => -2,
                    'msg'       => '上一个更新请求未返回'
                );
                return $rtn;
            }
        }
        file_put_contents($lockFile, 1, LOCK_EX);
        $table = 'ko_cloudbeds_access_token';
        $fields = 'id, access_token, token_type, expires_in, refresh_token, update_time';
        $query = $this->db->query('select ' . $fields . ' from ' . $table);
        $result = $query->result_array();
        if(count($result) > 0) {
            // 判断更新时间
            if(($result[0]['expires_in'] + $result[0]['update_time']) - time() < 600) {
                // 更新token
                $url = 'https://hotels.cloudbeds.com/api/v1.1/access_token';
                $params = array(
                    'grant_type'    => 'refresh_token',
                    'client_id'     => 'live1_assoc163958_2Mk4KHb1qDeawdVxCzQlRmEF',
                    'client_secret' => 'te1ml5AvSdMjk0JoWFhUc8P7y4gV9bIx',
                    'refresh_token' => $result[0]['refresh_token']
                );
                $apiReturnStr = $this->https_request($url, $params);
                @file_put_contents('/pub/logs/update_cloudbeds_access_token', '[' . date('Y-m-d H:i:s', time()) . ']==> ' . $apiReturnStr . PHP_EOL, FILE_APPEND);
                @file_put_contents('/pub/logs/update_cloudbeds_access_token_ip', '[' . date('Y-m-d H:i:s', time()) . ']==> ' . $this->getIP() . PHP_EOL, FILE_APPEND);
                $apiReturn = json_decode($apiReturnStr, true);
                // {
                //     "access_token": "I1xDWLawVoUwZq7kjDDsbCpaTrIjwqZ4LSZM80Nh",
                //     "token_type": "Bearer",
                //     "expires_in": 3600,
                //     "refresh_token": "wnowH0N7RnCaGY58qoef8d3Kt6oKwZtVTjWQHfph"
                // }
                if(isset($apiReturn['access_token']) && isset($apiReturn['refresh_token'])) {
                    $data = array(
                        'access_token'  => $apiReturn['access_token'],
                        'token_type'    => $apiReturn['token_type'],
                        'expires_in'    => $apiReturn['expires_in'],
                        'refresh_token' => $apiReturn['refresh_token'],
                        'update_time'   => time()
                    );
                    $this->db->where('id', $result[0]['id'])->update($table, $data);
                    $rtn = array(
                        'status'    => 1,
                        'msg'       => '有效期小于10分钟，已更新access token',
                        'data'      => $data
                    );
                    file_put_contents($lockFile, 0, LOCK_EX);
                    return $rtn;
                } else {
                    // 接口异常
                    $rtn = array(
                        'status'    => -3,
                        'msg'       => 'cloudbeds api接口异常'
                    );
                    file_put_contents($lockFile, 0, LOCK_EX);
                    return $rtn;
                }
            } else {
                // 有效期还比较长，无需更新
                $rtn = array(
                    'status'    => 0,
                    'msg'       => '有效期较长，无需更新',
                    'data'      => $result[0]
                );
                file_put_contents($lockFile, 0, LOCK_EX);
                return $rtn;
            }
        } else {
            // 系统异常
            $rtn = array(
                'status'    => -1,
                'msg'       => '未查找到access_token记录'
            );
            file_put_contents($lockFile, 0, LOCK_EX);
            return $rtn;
        }
    }
}
