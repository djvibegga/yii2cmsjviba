<?php

use yii\db\Migration;

class m130524_201444_init extends Migration
{
    public function up()
    {
        $this->createTable('{{%page}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'object_id' => $this->bigInteger()->defaultExpression("nextval('object')"),
            'name' => $this->string()->notNull()->unique(),
            'status' => $this->smallInteger()->notNull()->defaultValue(0),
            'created_at' => $this->timestamp()->notNull(),
            'updated_at' => $this->timestamp()->notNull(),
            'published_at' => $this->dateTime(),
        ]);
        
        $this->createIndex('user_idx', '{{%page}}', 'user_id');
        $this->addForeignKey('fk_user', '{{%page}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%page_info}}', [
            'id' => $this->primaryKey(),
            'lang_id' => $this->integer()->notNull(),
            'page_id' => $this->integer()->notNull(),
            'title' => $this->string()->notNull()->unique(),
            'teaser' => $this->string()->defaultValue(null),
            'text' => $this->text()->defaultValue(null),
            'meta' => 'jsonb',
            'url' => $this->string(255)->notNull()->defaultValue('')
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%page}}');
        $this->dropTable('{{%page_info}}');
    }
}
