<?php

require_once('m160510_195737_house.php');

class m160510_213750_dhouse extends m160510_195737_house
{
    public $tableName = '{{%fias_dhouse}}';

    public function init()
    {
        $module = ejen\fias\Module::getInstance();

        if (!empty($module)) {
            $this->db = $module->getDb();
        }

        parent::init();
    }
}
