<?php
use yii\db\Migration;

class m250214_000000_create_rbac_tables extends Migration
{
    public function up()
    {
        // Create auth_item table
        $this->createTable('{{%auth_item}}', [
            'name' => $this->string(64)->notNull(),
            'type' => $this->integer()->notNull(),
            'description' => $this->text(),
            'rule_name' => $this->string(64),
            'data' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'PRIMARY KEY(name)',
        ]);

        // Create auth_item_child table
        $this->createTable('{{%auth_item_child}}', [
            'parent' => $this->string(64)->notNull(),
            'child' => $this->string(64)->notNull(),
            'PRIMARY KEY(parent, child)',
        ]);

        // Create auth_assignment table
        $this->createTable('{{%auth_assignment}}', [
            'item_name' => $this->string(64)->notNull(),
            'user_id' => $this->integer()->notNull(),
            'created_at' => $this->integer(),
            'PRIMARY KEY(item_name, user_id)',
        ]);

        // Create auth_rule table
        $this->createTable('{{%auth_rule}}', [
            'name' => $this->string(64)->notNull(),
            'data' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'PRIMARY KEY(name)',
        ]);

        // Add foreign keys
        $this->addForeignKey(
            'fk_auth_item_child_parent',
            '{{%auth_item_child}}',
            'parent',
            '{{%auth_item}}',
            'name',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_auth_item_child_child',
            '{{%auth_item_child}}',
            'child',
            '{{%auth_item}}',
            'name',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_auth_assignment_item_name',
            '{{%auth_assignment}}',
            'item_name',
            '{{%auth_item}}',
            'name',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_auth_assignment_user_id',
            '{{%auth_assignment}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_auth_rule_item_name',
            '{{%auth_item}}',
            'rule_name',
            '{{%auth_rule}}',
            'name',
            'CASCADE'
        );
    }

    public function down()
    {
        // Drop foreign keys
        $this->dropForeignKey('fk_auth_rule_item_name', '{{%auth_item}}');
        $this->dropForeignKey('fk_auth_assignment_user_id', '{{%auth_assignment}}');
        $this->dropForeignKey('fk_auth_assignment_item_name', '{{%auth_assignment}}');
        $this->dropForeignKey('fk_auth_item_child_child', '{{%auth_item_child}}');
        $this->dropForeignKey('fk_auth_item_child_parent', '{{%auth_item_child}}');

        // Drop tables
        $this->dropTable('{{%auth_rule}}');
        $this->dropTable('{{%auth_assignment}}');
        $this->dropTable('{{%auth_item_child}}');
        $this->dropTable('{{%auth_item}}');
    }
}
