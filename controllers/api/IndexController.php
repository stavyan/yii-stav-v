<?php
/**
 * Created by PhpStorm.
 * User: stav
 * Date: 2019-01-03
 * Time: 21:06
 */

namespace app\controllers\api;

use Yii;
use app\controllers\BaseController;
use app\models\Admin;
use yii\helpers\ArrayHelper;

class IndexController extends BaseController {
    public function actionIndex()
    {
        return $this->echoJson(0, 'a');
    }
    /**
     * 用户列表
     */
    public function actionUsers()
    {
        $model = Admin::find()->all();
        $data = ArrayHelper::toArray($model);
        return $this->echoJson(0, $data);
    }



}