<?php
namespace app\controllers;


use Yii;
use yii\web\Controller;
use app\components\Common;
use Illuminate\Validation\Factory as Validator;
use Symfony\Component\Translation\Translator;

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

    public function beforeAction($action)
    {
        header("Content-type: text/json; charset=utf-8");

        $allow_headers = [
            'Content-Type',
            'Accept',
            'Authorization',
            'X-Requested-With',
            'token',
        ];

        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header("Access-Control-Allow-Credentials:true");
            header("Access-Control-Allow-Origin:" . $_SERVER['HTTP_ORIGIN']);
        }
        header('Access-Control-Allow-Methods: POST, GET, PUT');
        header('Access-Control-Allow-Headers: ' . implode(', ', $allow_headers));

        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            header('HTTP/1.1 200 OK');
            exit;
        }

        $this->common = new Common();

        $this->request = Yii::$app->request;
        $this->params = array_merge($this->request->get(), $this->request->post());

        if ($_SERVER['HTTP_TOKEN']) {
            $this->params['_token'] = $_SERVER['HTTP_TOKEN'];
        }

        if (!array_key_exists('offset', $this->params)) {
            $this->params['offset'] = 1;
        }
        if (!array_key_exists('limit', $this->params)) {
            $this->params['limit'] = 10;
        }

        //设置登录TOKEN
        $this->session = Yii::$app->session;
        $this->session->open();

        if (array_key_exists('_token', $this->params) && ($token = $this->params['_token'])) {
            $this->session->setId($token);
        }
        //账号单处登录与权限验证
        if ( !in_array($this->params['r'],['/admin/login','/admin/login/reset'])  && explode('/',$this->params['r'])[2] !== 'upload' && explode('/',$this->params['r'])[1] == 'admin') {
            if (!$this->isLogin()) {
                return $this->echoJson(1, '该用户还未登录.');
            }
            /*关闭单处登录*/
//            $token_model = new  Token();
//            $admin_info = $this->getAdminInfo();
//            $token_info = $token_model->gets(['uid' => $admin_info['id']], []);
//            if ($token_info && $token_info[0]['token'] !== $this->params['_token']) {
//                return $this->echoJson(403, '该账户已在其他地方登陆！');
//            }
            if (method_exists($this, 'checkRole')) {
                $this->checkRole($this->params['_token']);
            }
        }

        $url = 'http://' . $_SERVER['HTTP_HOST'] . $this->params['r'];

        unset($this->params['r']);
        //参数校验
        if (!YII_DEBUG && !$this->signVerify($url, $this->params)) {
            return $this->echoJson(1, '参数校验失败');
        }
        return parent::beforeAction($action);
    }

    public function isLogin()
    {
        return true;
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
            'timestamp' => $this->common->formatDate(),
        ];
        if ($status == 0) {
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

    /*
*  签名验证,通过签名验证的才能认为是合法的请求
*/
    public function signVerify($url, $params)
    {
        reset($params);

        //时间校验 10 分钟有效期
        if ($params['_time'] && time() - $params['_time'] > 600) {
            return false;
        }

        $sign = self::sign($url, $params);
        if ($sign === $params['_sign']) {
            return true;
        }

        return false;
    }

    /*
    *  md5签名，$params 中务必包含 _time 时间来校验
    */
    static function sign($url, $params)
    {
        ksort($params);
        $string = $url . '?';
        foreach ($params as $key => $value) {
            if (substr($key, 0, 1) != '_') {
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        $string .= $key . '[' . $k . ']=' . urlencode($v) . '&';
                    }
                } else {
                    $string .= $key . '=' . urlencode($value) . '&';
                }
            }
        }

        $string = substr($string, 0, strlen($string) - 1) . date('YmdHi', $params['_time']);
        //agent校验
        $agent = $_SERVER['HTTP_USER_AGENT'];
        $keys = Yii::$app->params['sign'];
        $sign = '';
        foreach ($keys as $k => $v) {
            if (preg_match($k, $agent)) {
                $sign = $v;
                break;
            }
        }
        //兼容模式 如果未匹配到规则,则不参与校验
        if (!$sign) {
            return null;
        }

        $string .= $sign;

        return strtoupper(md5($string));
    }

    /**
     * 判断手机号
     * @param $phone
     * @return bool
     */
    public function checkPhone($phone)
    {
        $search = '/^0?1[3|4|5|6|7|8][0-9]\d{8}$/';
        if (preg_match($search, $phone)) {
            return false;
        }
        return true;
    }

    /**
     * 判断用户名
     * @param $username
     * @return bool
     */
    public function checkUsername($username)
    {
        $search = '/^[A-Za-z0-9_\x{4e00}-\x{9fa5}]+$/u';
        if (preg_match($search, $username)) {
            return false;
        }
        return true;
    }

    /**
     * 判断银行卡号
     * @param $card_number
     * @return string
     */
    public function checkBankCard($card_number)
    {
        $arr_no = str_split($card_number);
        $last_n = $arr_no[count($arr_no) - 1];
        krsort($arr_no);
        $i = 1;
        $total = 0;
        foreach ($arr_no as $n) {
            if ($i % 2 == 0) {
                $ix = $n * 2;
                if ($ix >= 10) {
                    $nx = 1 + ($ix % 10);
                    $total += $nx;
                } else {
                    $total += $ix;
                }
            } else {
                $total += $n;
            }
            $i++;
        }
        $total -= $last_n;
        $x = 10 - ($total % 10);
        if ($x == $last_n) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 校验密码位数
     * @param $password
     * @return bool
     */
    public function checkPassword($password)
    {
//        if(preg_match('/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])[A-Za-z0-9]{8,20}/',$password)){
        if (preg_match('/^[a-zA-Z0-9-*-.-_]{8}$/', $password)) {

            return false;

        }
        return true;
    }

    /**
     * 验证车牌
     * @param $plate
     * @return bool
     */
    public function checkPlate($plate)
    {
        $regular = '/^[京津沪渝冀豫云辽黑湘皖鲁新苏浙赣鄂桂甘晋蒙陕吉闽贵粤青藏川宁琼使领A-Z]{1}[A-Z]{1}[A-Z0-9]{4}[A-Z0-9挂学警港澳]{1}$/u';
        if (preg_match($regular, $plate)) {

            return false;

        }
        return true;
    }

    /**
     * 验证ip
     * @param $ip
     * @return bool
     */
    public function checkIp($ip)
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        } else {
            return true;
        }
    }

}
