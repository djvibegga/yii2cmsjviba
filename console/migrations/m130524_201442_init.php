<?php

use yii\db\Migration;

class m130524_201442_init extends Migration
{
    public function up()
    {
        $sql = 'CREATE SEQUENCE object START 1;';
        $this->execute($sql);

        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'object_id' =>  $this->bigInteger()->defaultExpression("nextval('object')"),
            'status' => $this->smallInteger()->notNull()->defaultValue(0),
            'role' => $this->smallInteger()->notNull()->defaultValue(0),
            'email' => $this->string()->notNull()->unique(),
            'created_at' => $this->timestamp()->notNull(),
            'updated_at' => $this->timestamp()->notNull(),
            'username' => $this->string()->defaultValue(null),
            'auth_key' => $this->string(32)->notNull(),
            'activation_code' => $this->string(32)->notNull(),
            'password_hash' => $this->string()->notNull(),
            'password_reset_token' => $this->string()->unique(),
        ]);

        $this->createTable('{{%lang}}', [
            'id' => $this->primaryKey(),
            'object_id' =>  $this->bigInteger()->defaultExpression("nextval('object')"),
            'name' => $this->string(2)->notNull()->unique(),
            'label' => $this->string(16)->defaultValue(null),
        ]);

        $this->createTable('{{%object_seo}}', [
            'id' => $this->primaryKey(),
            'to_object_id' => $this->bigInteger()->notNull(),
            'url' => $this->string(256)->notNull()->unique(),
            'lang_id' => $this->integer()->notNull(),
            'type' => $this->string(16)->notNull()
        ]);

        $this->createTable('{{%object_view}}', [// тут будем хранить только авторизованых посетителей
            'id' => $this->primaryKey(),
            'to_object_id' => $this->bigInteger()->notNull(),
            'ipv4' => 'inet NOT NULL',
            'user_id' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('NOW()'),
        ]);

        $this->addForeignKey('fk_object_view_user', '{{%object_view}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%object_view_total}}', [// а тут все просмотры
            'id' => $this->primaryKey(),
            'to_object_id' => $this->bigInteger()->notNull(),
            'count' => $this->integer()->notNull(),
            'updated_at' => $this->timestamp()->notNull(),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%lang}}');
        $this->dropTable('{{%object_seo}}');
        $this->dropTable('{{%object_view}}');
        $this->dropTable('{{%object_view_total}}');
        $this->dropTable('{{%user}}');
        $sql = 'DROP SEQUENCE object;';
        $this->execute($sql);
    }
}
