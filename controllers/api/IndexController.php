<?php
/**
 * Created by PhpStorm.
 * User: stav
 * Date: 2019-01-03
 * Time: 21:06
 */

namespace app\controllers\api;

//use Yii;
use app\controllers\BaseController;

class IndexController extends BaseController {

    public function actionIndex()
    {
//        $admin_info = $this->getAdminInfo();
//
//        $model = new Admin();
//
//        $result = $model->get($admin_info['id']);

        return $this->echoJson(0, '你好');

    }
}