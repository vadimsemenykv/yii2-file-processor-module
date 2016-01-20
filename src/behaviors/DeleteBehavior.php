<?php
/**
 * Author: Vadym Semeniuk
 * Date: 25.11.15
 * Time: 12:40
 */

namespace backend\components;

use yii\base\Behavior;
use yii\db\BaseActiveRecord;
use metalguardian\fileProcessor\helpers\FPM;

class DeleteBehavior extends Behavior
{
    /*you can set attribute as array of attributes*/
    public $attribute = 'file_id';
    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            BaseActiveRecord::EVENT_AFTER_DELETE => 'afterDelete'
        ];
    }

    public function afterDelete($event)
    {
        if (is_array($this->attribute)) {
            foreach ($this->attribute as $attr) {
                $id = $this->owner->{$attr};
                $this->delete($id);
            }
        } else {
            $id = $this->owner->{$this->attribute};
            $this->delete($id);
        }
    }

    /**
     * @param $id
     */
    private function delete($id)
    {
        if ($id) {
            FPM::deleteFile($id);
        }
    }
}