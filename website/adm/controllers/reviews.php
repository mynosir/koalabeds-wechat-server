<?php
/**
 * 酒店评论管理
 *
 * @author jiang <qoohj@qoohj.com>
 *
 */
class Reviews extends MY_Controller {


    public function __construct() {
        parent::__construct();
        $this->checkLogin();
        // $this->load->model('reviews_model');
        $data['resource_url'] = $this->resource_url;
        $data['admin_info'] = isset($_SESSION['loginInfo']) ? $_SESSION['loginInfo'] : '';
        $data['base_url'] = $this->config->item('base_url');
        $data['current_menu'] = 'reviews';
        $data['sub_menu'] = array();
        $data['current_menu_text'] = 'Reviews';
        $data['menu_list'] = $this->getMenuList();
        $this->load->model('hotel_model');
        $this->load->model('reviews_model');
        $this->data = $data;
    }


    public function index() {
        $this->data['propertyList'] = $this->hotel_model->getPropertyList();
        $this->showPage('reviews_index', $this->data);
    }

    public function get() {
        $actionxm = $this->get_request('actionxm');
        $result = array();
        switch($actionxm) {
            case 'getReviews':
                $page = $this->get_request('page');
                $size = $this->get_request('size');
                $propertyId = $this->get_request('propertyId');
                $keyword = $this->get_request('keyword');
                $result = $this->reviews_model->getReviews($page, $size, $keyword, $propertyId);

                break;
            case 'getDetail':
                $id = $this->get_request('id');
                $result = $this->reviews_model->getDetail($id);
                break;
        }
        echo json_encode($result);
    }

    public function post() {
        $actionxm = $this->get_request('actionxm');
        $result = array();
        switch($actionxm) {
            case 'addUser':
                $data = $this->get_request('params');
                $result = $this->reviews_model->addUser($data);
                break;
            case 'updateStatus':
                $id = $this->get_request('id');
                $params = $this->get_request('params');
                $result = $this->reviews_model->updateStatus($id,$params);
                // $classify = $this->get_request('classify');
                // $keyword = $this->get_request('keyword');
                // $result = $this->reviews_model->updateReviews($page, $size);

                break;

            case 'updateUser':
                $id = $this->get_request('id');
                $data = $this->get_request('params');
                $result = $this->reviews_model->updateUser($id, $data);
                break;
            case 'delete':
                $id = $this->get_request('id');
                $result = $this->reviews_model->deleteItem($id);
                break;
            case 'upload_photo':
                if(!empty($_FILES)) {
                    $fileParts = pathinfo($_FILES['uploadfile']['name']);
                    $tempFile = $_FILES['uploadfile']['tmp_name'];
                    $targetFolder = '/public/reviews/index/';
                    $targetPath = $_SERVER['DOCUMENT_ROOT'] . $targetFolder;
                    if(!is_dir($targetPath)) mkdir($targetPath, 0777, true);
                    $now = time();
                    $fileName = $now . '_org.' . $fileParts['extension'];
                    $compressFileName = $now . '.' . $fileParts['extension'];
                    $targetFile = rtrim($targetPath, '/') . '/' . $fileName;
                    $compressTargetFile = rtrim($targetPath, '/') . '/' . $compressFileName;
                    $fileTypes = array('jpg', 'jpeg', 'gif', 'png');
                    if(in_array(strtolower($fileParts['extension']), $fileTypes)) {
                        move_uploaded_file($tempFile, $targetFile);
                        // 开始压缩图片
                        $this->compressImage($targetFile, $compressTargetFile, 1920);
                        $result = array('status'=> 0, 'name'=> 'http://' . $_SERVER['HTTP_HOST'] . $targetFolder . $compressFileName);
                    } else {
                        $result = array('status'=> -1, 'msg'=> '文件格式不正确');
                    }
                }
                break;
        }
        echo json_encode($result);
    }
}
