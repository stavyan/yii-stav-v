<?php
/**
 * Des:  公共方法库
 * User: stav
 * Date: 2019-01-03
 * Time: 21:24
 */

namespace app\components;

class Common
{

    public function hash($str)
    {
        return md5($str);
    }

    public function getCode($length)
    {
        $s = $n = 0;
        switch ($length) {
            case '6' :
                $s = 100000;
                $n = 999999;
                break;
            case '8' :
                $s = 10000000;
                $n = 99999999;
                break;
            default:
                $s = 1000;
                $n = 9999;
        }

        return mt_rand($s, $n);
    }

    public function formatDate($format = '')
    {
        return date($format ? $format : 'Y-m-d H:i:s', time());
    }

    public function deviceType($ua)
    {
        $ua = strtolower($ua);
        if (strpos($ua, 'iphone') !== false || strpos($ua, 'ipad') !== false) {
            return 'ios';
        } else {
            if (strpos($ua, 'android') !== false || strpos($ua, 'okhttp') !== false) {
                return 'android';
            } else {
                return 'other';
            }
        }
    }

    /**
     * 格式化数组内容
     * @param $list
     * @param array $format ['area', 'manage_info']
     * @return mixed
     */
    public function formatData($list, Array $format)
    {
        $area_name = $manage_name = $appointment = [];
        foreach ($list as $k => $v) {

            //客户状态 订单状态
            if (isset($v['status'])) {
                $list[$k]['user_status'] = '服务用户';

                if ($v['status'] == Constant::STATUS_ORDER_DEFAULT) {
                    $list[$k]['user_status'] = '意向用户';
                }
                if ($v['status'] == Constant::STATUS_ORDER_CLOSE || $v['status'] == Constant::STATUS_ORDER_STOP) {
                    $list[$k]['user_status'] = '潜在用户';
                }
                if ($v['status'] == Constant::STATUS_ORDER_OVER) {
                    $list[$k]['user_status'] = '完工用户';
                }
                foreach (Constant::get('STATUS_ORDER') as $k_ => $s_) {
                    if ($k_ == $v['status']) {
                        $list[$k]['status_name'] = $s_;
                        break;
                    }
                }
            }

            //订单来源
            foreach (Constant::get('ORDER_FROM') as $k_ => $s_) {
                if ($k_ == $v['from']) {
                    $list[$k]['from'] = $s_;
                    break;
                }
            }

            //回访状态
            if (isset($v['customer_status'])) {
                foreach (Constant::get('ORDER_CUSTOMER_STATUS') as $k_ => $s_) {
                    if ($k_ == $v['customer_status']) {
                        $list[$k]['customer_status_name'] = $s_;
                        break;
                    }
                }
            }

            //派单状态
            if (isset($v['manage_status'])) {
                foreach (Constant::get('MANAGE_STATUS') as $manage_k => $manage_s) {
                    if ($manage_k == $v['manage_status']) {
                        $list[$k]['manage_status_name'] = $manage_s;
                        break;
                    }
                }
            }

            //派单状态
            if (isset($v['house_type'])) {
                foreach (Constant::get('PRODUCT_HOUSE_TYPE') as $manage_k => $manage_s) {
                    if ($manage_k == $v['house_type']) {
                        $list[$k]['house_type_name'] = $manage_s;
                        break;
                    }
                }
            }

            //工种
            if (isset($v['worker_type'])) {
                if ($v['worker_type'] == Constant::ORDER_WORKER_COMPANY) {
                    $list[$k]['worker_type_name'] = '装修公司';
                    continue;
                }
                if ($v['worker_type'] == Constant::ORDER_WORKER_MANAGE) {
                    $list[$k]['worker_type_name'] = '项目经理';
                    continue;
                }
                foreach (Constant::get('ORDER_WORKER') as $manage_k => $manage_s) {


                    if ($manage_k == $v['worker_type']) {
                        $list[$k]['worker_type_name'] = $manage_s;
                        break;
                    }
                }
            }

            $v['manage_name'] && $manage_name[] = $v['manage_name'];
            $area_name[$v['area']]     = $v['area'];
            $area_name[$v['province']] = $v['province'];
            $area_name[$v['city']]     = $v['city'];
            $area_name[$v['county']]   = $v['county'];
        }

        foreach ($format as $f) {
            if ($f == 'area' && $area_name) {

                $areas = (new Local())->gets(['id' => $area_name], [], 0, 0);
                foreach ($list as $k => $v) {

                    foreach ($areas as $a) {
                        if ($a['id'] == $v['area']) {
                            $list[$k]['area_name'] = $a['name'];
                        }

                        if ($a['id'] == $v['province'] && $a['level'] == 1) {
                            $list[$k]['province_name'] = $a['name'];
                        }

                        if ($a['id'] == $v['city'] && $a['level'] == 2) {
                            $list[$k]['city_name'] = $a['name'];
                        }

                        if ($a['id'] == $v['county'] && $a['level'] == 3) {
                            $list[$k]['county_name'] = $a['name'];
                        }
                    }
                }
            }

            if ($f == 'manage_info' && $manage_name) {
                $manage = (new UserManage())->gets(['name' => $manage_name], [], 0, 0);

                foreach ($manage as $m) {
                    foreach ($list as $k => $v) {
                        if ($m['name'] == $v['manage_name']) {
                            $list[$k]['manage_phone'] = $m['phone'];
                            $list[$k]['manage_img']   = $m['imgurl'];
                            break;
                        }
                    }
                }
            }

        }

        return $list;
    }

    public function parseErrorMsg($errors)
    {
        foreach ($errors as $msg) {
            return $msg[0];
        }

        return '';
    }

    /**
     * CURL 会话
     * @param $url
     * @param array $param
     * @param string $method
     * @param string $content_type
     * @param int $flag
     * @return mixed
     */
    public function curl($url, $param = [], $method = 'post', $content_type = 'application/json', $flag = 0)
    {
        $ch = curl_init();
        if (!$flag) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (strtolower($method) == 'post') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($param));
        } else {
            $param && $url .= '?' . http_build_query($param);
        }
        curl_setopt($ch, CURLOPT_URL, $url);

        //HTTPS
        if (strpos($url, 'https') === 0) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        //头部信息
        $header = [
            'Content-Type: ' . $content_type,
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        $ret = curl_exec($ch);
        curl_close($ch);

        return $ret;
    }

}

