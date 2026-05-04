<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user}}`.
 */
class m231128_000001_create_user_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),

            'username' => $this->string(50)->notNull()->unique(),
            'fullname' => $this->string(100)->notNull(),

            'role' => "ENUM('operator','subforeman','foreman','chief','manager','admin') NOT NULL DEFAULT 'operator'",

            'pin_hash' => $this->string(255)->null(),
            'require_pin' => $this->boolean()->defaultValue(false),

            'shift_code' => $this->string(10)->null(),

            'status' => $this->boolean()->defaultValue(true),

            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Index for login performance
        $this->createIndex('idx-user-username', '{{%user}}', 'username');
        $this->createIndex('idx-user-role', '{{%user}}', 'role');
        $this->createIndex('idx-user-status', '{{%user}}', 'status');
    }

    public function safeDown()
    {
        $this->dropTable('{{%user}}');
    }
}
