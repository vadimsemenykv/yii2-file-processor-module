<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Class m150110_172045_create_file_table
 */
class m150110_172045_create_file_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            \metalguardian\fileProcessor\helpers\FPM::getTableName(),
            [
                'id' => $this->primaryKey(),
                'extension' => $this->string(10)->notNull()->comment("File extension"),
                'base_name' => $this->string(250)->defaultValue(null)->comment("File base name"),
                'alt_tag' => $this->string(250)->defaultValue(null)->comment('Image alt tag'),
                'created_at' => $this->integer()->notNull(),
            ],
            $tableOptions
        );
    }

    public function down()
    {
        $this->dropTable(\metalguardian\fileProcessor\helpers\FPM::getTableName());
    }
}
