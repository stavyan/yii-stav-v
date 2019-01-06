<?php

namespace app\controllers\api;

use Yii;
use app\controllers\BaseController;
use app\models\Admin;
use yii\helpers\ArrayHelper;

class UserController extends BaseController {

    public function actionList()
    {
        $model = Admin::find()->all();
        $data = ArrayHelper::toArray($model);
        return $this->echoJson(0, $data);
    }

    public function actionDel()
    {

        $adminuser = $this->params['adminuser'];
        Admin::deleteAll('adminuser = :user', [':user' => $adminuser]);
//        User::findOne($id)->delete();
//        User::deleteAll('age > :age AND sex = :sex', [':age' => '20', ':sex' => '1']);
        return $this->echoJson(0, 用户名删除成功);
    }

    public function actionAdd()
    {
        $adminuser = $this->params['adminuser'];
        $adminpass = $this->params['adminpass'];
        $model = new Admin();

        if ($this->checkPhone($adminuser)) {
            return $this->echoJson(1, '用户名格式错误');
        }
        $model->adminuser = $adminuser;
        $model->adminpass = md5($adminpass);
        $model->insert();
        return $this->echoJson(0, 用户添加成功);
    }

    public function actionEdit()
    {
//        $rules = [
//            'adminemail' => 'required|string'
//        ];
        if ($this->checkPhone($this->params['adminemail'])) {
            return $this->echoJson(1, '邮箱格式错误');
        }
        $model = Admin::find()->one();
        $data = ArrayHelper::toArray($model);
        return $this->echoJson(0, $data);
    }

}