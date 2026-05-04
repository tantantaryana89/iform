<?php

use yii\db\Migration;

class m231128_000002_update_user_add_password_pin_authkey extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'password_hash', $this->string()->notNull());
        $this->addColumn('{{%user}}', 'auth_key', $this->string(32)->null());

        // Pastikan pin_hash tetap string panjang yang aman
        $this->alterColumn('{{%user}}', 'pin_hash', $this->string(255)->null());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'auth_key');
        $this->dropColumn('{{%user}}', 'password_hash');
    }
}
