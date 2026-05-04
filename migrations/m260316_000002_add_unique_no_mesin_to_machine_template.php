<?php

use yii\db\Migration;

class m260316_000002_add_unique_no_mesin_to_machine_template extends Migration
{
    public function safeUp()
    {
        // Keep the newest mapping per machine if historical duplicates exist.
        $this->execute(
            'DELETE mt1 FROM machine_template mt1
             INNER JOIN machine_template mt2
               ON mt1.no_mesin = mt2.no_mesin
              AND mt1.id < mt2.id'
        );

        $this->createIndex(
            'ux_machine_template_no_mesin',
            '{{%machine_template}}',
            'no_mesin',
            true
        );
    }

    public function safeDown()
    {
        $this->dropIndex('ux_machine_template_no_mesin', '{{%machine_template}}');
    }
}
