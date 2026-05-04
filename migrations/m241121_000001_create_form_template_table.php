<?php

use yii\db\Migration;

/**
 * Handles the creation of table `form_template`.
 */
class m241121_000001_create_form_template_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('form_template', [
            'id'            => $this->primaryKey(),
            'name'          => $this->string(255)->notNull(),
            'description'   => $this->text()->null(),

            // FIX: gunakan createColumnSchemaBuilder untuk LONGTEXT
            'schema_json'   => $this->getDb()->getSchema()
                                   ->createColumnSchemaBuilder('LONGTEXT')
                                   ->null(),

            'source_file'   => $this->string(255)->null(),
            'created_at'    => $this->integer()->notNull(),
            'updated_at'    => $this->integer()->notNull(),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('form_template');
    }
}
