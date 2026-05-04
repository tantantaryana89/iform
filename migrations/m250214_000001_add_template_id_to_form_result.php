<?php
use yii\db\Migration;

class m250214_000001_add_template_id_to_form_result extends Migration
{
    public function up()
    {
        // Add template_id column to form_result table
        $this->addColumn('{{%form_result}}', 'template_id', $this->integer()->after('id'));
        
        // Add foreign key
        $this->addForeignKey(
            'fk_form_result_template_id',
            '{{%form_result}}',
            'template_id',
            '{{%form_template}}',
            'id',
            'SET NULL'
        );
    }

    public function down()
    {
        // Drop foreign key
        $this->dropForeignKey('fk_form_result_template_id', '{{%form_result}}');
        
        // Drop column
        $this->dropColumn('{{%form_result}}', 'template_id');
    }
}
