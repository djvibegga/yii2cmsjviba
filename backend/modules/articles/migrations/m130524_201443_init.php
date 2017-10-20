<?php

use yii\db\Migration;

class m130524_201443_init extends Migration
{
    public function up()
    {
        $this->createTable('{{%article_category}}', [ // https://github.com/paulzi/yii2-nested-sets
            'id' => $this->primaryKey(),
            'tree' => $this->integer(),
            'lft' => $this->integer()->notNull(),
            'rgt' => $this->integer()->notNull(),
            'depth' => $this->integer()->notNull(),
            'object_id' =>  $this->bigInteger()->defaultExpression("nextval('object')"),
            'status' => $this->smallInteger()->notNull()->defaultValue(0),
            'created_at' => $this->timestamp()->notNull(),
            'updated_at' => $this->timestamp()->notNull(),
            'name' => $this->string(64)->notNull()->unique()
        ]);
        $this->createIndex('lft', '{{%article_category}}', ['tree', 'lft', 'rgt']);
        $this->createIndex('rgt', '{{%article_category}}', ['tree', 'rgt']);
        
        $this->createTable('{{%article_category_info}}', [
            'id' => $this->primaryKey(),
            'article_category_id' => $this->integer()->notNull(),
            'lang_id' => $this->integer()->notNull(),
            'url' => $this->string(256)->notNull()->unique()
        ]);
        $this->createIndex(
            'article_category_info_category_lang_idx',
            '{{%article_category_info}}',
            ['article_category_id', 'lang_id']
        );
        $this->addForeignKey(
            'fk_article_category_info_category',
            '{{%article_category_info}}',
            'article_category_id',
            'article_category',
            'id'
        );

        $this->createTable('{{%article}}', [
            'id' => $this->primaryKey(),
            'article_category_ids' => 'integer[]',
            'user_id' => $this->integer()->notNull(),
            'object_id' => $this->bigInteger()->defaultExpression("nextval('object')"),
            'name' => $this->string()->notNull()->unique(),
            'status' => $this->smallInteger()->notNull()->defaultValue(0),
            'created_at' => $this->timestamp()->notNull(),
            'updated_at' => $this->timestamp()->notNull(),
            'published_at' => $this->dateTime(),
        ]);

        $this->createIndex('user_idx', '{{%article}}', 'user_id');
        $this->addForeignKey('fk_user', '{{%article}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%article_info}}', [
            'id' => $this->primaryKey(),
            'lang_id' => $this->integer()->notNull(),
            'article_id' => $this->integer()->notNull(),
            'title' => $this->string()->notNull()->unique(),
            'teaser' => $this->string()->defaultValue(null),
            'text' => $this->text()->defaultValue(null),
            'meta' => 'jsonb',
            'url' => $this->string(255)->notNull()->defaultValue('')
        ]);

        $this->execute('CREATE INDEX fk_article_category_ids_idx on "article" USING GIN ("article_category_ids");');
        $this->addForeignKey('fk_article', '{{%article_info}}', 'article_id', '{{%article}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%article_category_info}}');
        $this->dropTable('{{%article_category}}');
        $this->dropTable('{{%article_info}}');
        $this->dropTable('{{%article}}');
    }
}
