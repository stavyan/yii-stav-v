<?php
namespace app\models;

use Yii;
use SqlTranslator\SqlTranslator;
use SqlTranslator\Database;

class Models extends \yii\db\ActiveRecord
{
    protected $_db            = '';
    protected $_db_translator = '';
    protected $_className     = '';

    public function __construct()
    {
        $this->_className = get_class($this);
        $dsn              = Yii::$app->db->dsn;
        $username         = Yii::$app->db->username;
        $password         = Yii::$app->db->password;
        preg_match('/(\w+):host=([\d+\.]+)(:(\d+))?;dbname=(\w+)/', $dsn, $match);
        $this->_db = (new Database())->config(
            'mysql://' . $username . ':' . $password . '@' . $match[2] . ':' . $match[4] . '/' . $match[5]
        )
            ->pick('pdo');
        $this->_db_translator = new SqlTranslator();
    }

    public function database()
    {
        return $this->_db;
    }

    public function translator()
    {
        return $this->_db_translator;
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [];
    }

    public function scenarios()
    {
        return [];
    }

    //通用方法
    public function add($params)
    {
        $classname = $this->_className;
        $insert    = $this->_db_translator->insert
            ->into($classname::tableName(), array_keys($params))
            ->values(array_values($params));

        if ($this->_db->query($insert)) {
            return $this->_db->lastInsertId();
        }

        return false;
    }

    public function edit($params, $id)
    {
        $classname = $this->_className;
        try {
            $update    = $this->_db_translator->update
                ->set($classname::tableName(), $params)
                ->where('id=?', $id);
            $this->_db->query($update);
            return true;
        } catch  (\Exception $e) {
            return false;
        }
    }

    public function remove($id)
    {
        $classname = $this->_className;
        $delete    = $this->_db_translator->delete
            ->from($classname::tableName())
            ->where('id=?', $id);

        return $this->_db->query($delete);
    }

    public function get($id)
    {
        $classname = $this->_className;
        $select    = $this->_db_translator->select
            ->from(['a' => $classname::tableName()], ['*'])
            ->where('a.id=?', $id);

        return $this->_db->fetch($select);
    }

    public function count($condition)
    {
        $classname = $this->_className;
        $select    = $this->_db_translator->select
            ->from(['a' => $classname::tableName()], ['count(1)']);
        $condition && $this->_condition($condition, $select);

        return $this->_db->fetchOne($select);
    }

    public function gets($condition, $order, $limit = 20, $offset = 0)
    {
        $classname = $this->_className;
        $select    = $this->_db_translator->select
            ->from(['a' => $classname::tableName()], '*')
            ->limit($limit, $offset);
        $this->_condition($condition, $select);
        $this->_order($order, $select);
        $result = $this->_db->fetchAll($select);

        return $result;
    }

    protected function _condition($types, $select)
    {
        if ($select && is_object($select)) {
            foreach ($types as $type => $value) {
                switch ($type) {
                    case 'id' :
                    case 'product_id' :
                        $value && $select->where(
                            $this->translator()->quoteId('a.' . $type, $value, false)
                        );
                        break;
                }
            }
        }
    }

    protected function _order($order, $select)
    {
        if ($select && is_object($select)) {
            foreach ($order as $type => $value) {
                switch ($type) {
                    case 'id' :
                    case 'create_time' :
                        $select->order('a.' . $type, $value);
                        break;
                }
            }
        }
    }
}
