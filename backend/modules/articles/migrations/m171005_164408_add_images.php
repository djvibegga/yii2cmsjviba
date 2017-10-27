<?php

use yii\db\Migration;

class m171005_164408_add_images extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%article}}', 'photo', 'jsonb not null default \'{}\'');
        $this->addColumn('{{%article_category}}', 'photo', 'jsonb not null default \'{}\'');
    }

    public function safeDown()
    {
        $this->dropColumn('{{%article_category}}', 'photo');
        $this->dropColumn('{{%article}}', 'photo');
    }
}
