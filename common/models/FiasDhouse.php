<?php

namespace ejen\fias\common\models;

class FiasDhouse extends FiasHouse
{
    public static function getDb()
    {
        return \ejen\fias\Module::getInstance()->getDb();
    }

    public static function tableName()
    {
        return '{{%fias_dhouse}}';
    }
}
