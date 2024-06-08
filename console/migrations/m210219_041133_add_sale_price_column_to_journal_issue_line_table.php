<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%journal_issue_line}}`.
 */
class m210219_041133_add_sale_price_column_to_journal_issue_line_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%journal_issue_line}}', 'sale_price', $this->float());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%journal_issue_line}}', 'sale_price');
    }
}
