<?php

use yii\db\Migration;

class m260407_000001_add_approval_fields_to_form_result extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%form_result}}', 'approval_status', $this->string(32)->notNull()->defaultValue('submitted'));
        $this->addColumn('{{%form_result}}', 'leader_id', $this->integer()->null());
        $this->addColumn('{{%form_result}}', 'leader_approved_at', $this->integer()->null());
        $this->addColumn('{{%form_result}}', 'supervisor_id', $this->integer()->null());
        $this->addColumn('{{%form_result}}', 'supervisor_approved_at', $this->integer()->null());
        $this->addColumn('{{%form_result}}', 'chief_id', $this->integer()->null());
        $this->addColumn('{{%form_result}}', 'chief_approved_at', $this->integer()->null());
        $this->addColumn('{{%form_result}}', 'manager_id', $this->integer()->null());
        $this->addColumn('{{%form_result}}', 'manager_approved_at', $this->integer()->null());

        $this->addForeignKey('fk_form_result_leader', '{{%form_result}}', 'leader_id', '{{%user}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_form_result_supervisor', '{{%form_result}}', 'supervisor_id', '{{%user}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_form_result_chief', '{{%form_result}}', 'chief_id', '{{%user}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_form_result_manager', '{{%form_result}}', 'manager_id', '{{%user}}', 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_form_result_manager', '{{%form_result}}');
        $this->dropForeignKey('fk_form_result_chief', '{{%form_result}}');
        $this->dropForeignKey('fk_form_result_supervisor', '{{%form_result}}');
        $this->dropForeignKey('fk_form_result_leader', '{{%form_result}}');

        $this->dropColumn('{{%form_result}}', 'manager_approved_at');
        $this->dropColumn('{{%form_result}}', 'manager_id');
        $this->dropColumn('{{%form_result}}', 'chief_approved_at');
        $this->dropColumn('{{%form_result}}', 'chief_id');
        $this->dropColumn('{{%form_result}}', 'supervisor_approved_at');
        $this->dropColumn('{{%form_result}}', 'supervisor_id');
        $this->dropColumn('{{%form_result}}', 'leader_approved_at');
        $this->dropColumn('{{%form_result}}', 'leader_id');
        $this->dropColumn('{{%form_result}}', 'approval_status');
    }
}
