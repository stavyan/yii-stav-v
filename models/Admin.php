<?php
/**
 * Des: 管理员用户
 * Created by PhpStorm.
 * User: stav stavyan@qq.com
 * Time: 18/11/10 下午12:01
 */

namespace app\models;
use yii\db\ActiveRecord;
use Yii;

class Admin extends ActiveRecord
{

    public $rememberMe = true;
    public $repass;

    public static function tableName()
    {
        return "{{%admin}}";
    }

    public function attributeLabels() {
        return [
            'adminuser' => '管理员账号',
            'adminemail' => '管理员邮箱',
            'adminpass' => '管理员密码',
            'repass' => '确认密码',
        ];
    }

    public function rules()
    {
        return [
            ['adminuser', 'required', 'message' => '管理员账号不能为空', 'on' => ['login', 'seekpass', 'changepass', 'adminadd', 'changeemail']],
            ['adminpass', 'required', 'message' => '管理员密码不能为空', 'on' => ['login', 'changepass', 'adminadd', 'changeemail']],
            ['rememberMe', 'boolean', 'on' => ['login']],
            ['adminpass', 'validatePass', 'on' => ['login', 'changeemail']],
            ['adminemail', 'required', 'message' => '管理员邮箱不能为空', 'on' => ['seekpass', 'adminadd', 'changeemail']],
            ['adminemail', 'email', 'message' => '管理员邮箱格式不正确', 'on' => ['seekpass', 'adminadd', 'changeemail']],
            ['adminemail', 'validateEmail', 'on' => ['seekpass']],
            ['adminemail', 'unique', 'message' => '管理员邮箱已被注册', 'on' => ['adminadd', 'changeemail']],
            ['adminuser', 'unique', 'message' => '管理员已被注册', 'on' => ['adminadd']],
            ['repass', 'required', 'message' => '确认密码不能为空', 'on' => ['changepass', 'adminadd']],
            ['repass', 'compare', 'compareAttribute' => 'adminpass', 'message' => '两次密码输入不一致', 'on' => ['changepass', 'adminadd']]
        ];
    }

    public function validatePass()
    {
        if (!$this->hasErrors()) {
            $data = self::find()->where('adminuser = :user and adminpass = :pass',
                [":user" => $this->adminuser,
                    ":pass" => md5($this->adminpass)])->one();
            if (is_null($data)) {
                $this->addError("adminpass", "用户名或者密码错误");
            }
        }
    }

    public function validateEmail()
    {
        if (!$this->hasErrors()) {
            $data = self::find()->where('adminuser = :user and adminemail = :email',
                [':user' => $this->adminuser, ':email' => $this->adminemail])->one();
            if (is_null($data)) {
                $this->addError("adminemail", "管理员账号或邮箱不正确！");
            }
        }
    }

    public function login($data)
    {
        $this->scenario = "login";
        if ($this->load($data) && $this->validate()) {
            // 存储登录信息
            $lefttime = $this->rememberMe ? 24 * 3600 : 0;
            $session = Yii::$app->session;
            session_set_cookie_params($lefttime);
            $session['admin'] = [
                'adminuser' => $this->adminuser,
                'isLogin' => 1,
            ];
            $this->updateAll(['logintime' => time(),
                'loginip' => ip2long(Yii::$app->request->userIP)],
                'adminuser = :user',
                [':user' => $this->adminuser]);
            return (bool)$session['admin']['isLogin'];
        }
        return false;
    }

    public function seekPass($data)
    {
        $this->scenario = 'seekpass';
        if ($this->load($data) && $this->validate()) {
            // 发送电子邮件
            $time = time();
            $token = $this->createToken($data['Admin']['adminuser'], $time);
            $mailer = Yii::$app->mailer->compose('seekpass', ['adminuser' => $data['Admin']['adminuser'], 'time' => $time, 'token' => $token]);
            $mailer->setFrom("15083980039@163.com");
            $mailer->setTo($data['Admin']['adminemail']);
            $mailer->setSubject("斯塔夫商城-找回密码");
            if ($mailer->send()) {
                return true;
            }
        }
        return false;
    }

    public function createToken($adminuser, $time)
    {
        return md5(md5($adminuser) . base64_encode(Yii::$app->request->userIP) . md5($time));
    }

    public function changePass($data)
    {
        $this->scenario = "changepass";
        if ($this->load($data) && $this->validate()) {
            return (bool)$this->updateAll(['adminpass'=> md5($this->adminpass)], 'adminuser = :user', [':user' => $this->adminuser]);
        }
        return false;
    }

    /**
     * Returns static class instance, which can be used to obtain meta information.
     * @param bool $refresh whether to re-create static instance even, if it is already cached.
     * @return static class instance.
     */
    public static function instance($refresh = false)
    {
        // TODO: Implement instance() method.
    }

    public function reg($data) {
//        $this->scenario = 'adminadd';
        if($this->load($data) && $this->validate()) {
            $this->adminpass = md5($this->adminpass);
            if ($this->save(false)) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function changeemail($data) {
        $this->scenario = "changeemail";
        if ($this->load($data) && $this->validate()) {
            return (bool)$this->updateAll(['adminemail' => $this->adminemail], 'adminuser = :user', [":user" => $this->adminuser]);
        }
        return false;
    }
}