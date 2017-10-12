<?php

use yii\db\Migration;

class m171012_181619_add_system_languages extends Migration
{
    public function safeUp()
    {
        $this->insert('lang', ['name' => 'en', 'label' => 'English']);
        $this->insert('lang', ['name' => 'ru', 'label' => 'Русский']);
    }

    public function safeDown()
    {
        $this->delete('lang', '');
    }
}
