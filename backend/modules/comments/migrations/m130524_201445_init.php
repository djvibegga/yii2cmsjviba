<?php

use yii\db\Migration;

class m130524_201445_init extends Migration
{
    public function up()
    {
        $this->createTable('{{%comment}}', [
            'id' => $this->primaryKey(),
            'lang_id' => $this->integer()->notNull(), // c языками пока так решил
            'user_id' => $this->integer()->notNull(),
            'object_id' =>  $this->bigInteger()->defaultExpression("nextval('object')"),
            'to_object_id' => $this->bigInteger()->notNull(),
            'to_comment_id' => $this->bigInteger()->notNull(), // для ускорения обхода дерева комментов
            'text' => $this->text()->defaultValue(null),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%comment}}');
    }
}
