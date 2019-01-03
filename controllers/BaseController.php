<?php
namespace app\controllers;


use Yii;
use app\components\Common;
use yii\web\Controller;

/**
 * project base controller
 */
class BaseController extends Controller
{

    public $enableCsrfValidation = false;
    public $params;
    protected $common;
    protected $session;
    protected $request;

    /**
     * 验证输入信息
     * @param  array $rules
     * @return response
     */
    public function validateInput($rules)
    {
        $validator = (new Validator(new Translator('Validator')))->make($this->params, $rules);
        if ($validator->fails()) {
            $error_msg = $validator->messages()->keys();
            return $this->echoJson(1, '字段' . (implode(',', $error_msg)) . '不符合要求.');
        } else {
            return false;
        }
    }


    public function renderJSON($data = false)
    {
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $response->data = $data;
    }

    public function echoJson($status, $data = [])
    {
        $result = [
            'status' => $status,
            'timestamp' => $this->formatDate(),
        ];
        if ($status == 0) {
            if (is_array($data)) {
                //页码分析
                array_key_exists('page', $data) && $data['page'] = (int)$data['page'];
                array_key_exists('pager', $data) && $data['pager'] = (int)$data['pager'];
                array_key_exists('count', $data) && $data['count'] = (int)$data['count'];
            }

            $result['data'] = $this->arrayFilter($data);
        } else {
            $result['error'] = $data;
        }
        if (YII_ENV_DEV) {
            $result['debug'] = [
                '_POST' => $_POST,
                '_GET' => $_GET,
            ];
        }

        exit(json_encode($result));

    }

    public function formatDate($format = '')
    {
        return date($format ? $format : 'Y-m-d H:i:s', time());
    }

    public function arrayFilter($array)
    {
        if (is_array($array)) {
            foreach ($array as $k => $a) {
                is_array($a) && $array[$k] = $this->arrayFilter($a);
                is_null($a) && $array[$k] = '';
            }
        }

        return $array;
    }

    public function jsonDecode($str)
    {
        return json_decode($str);
    }

}
