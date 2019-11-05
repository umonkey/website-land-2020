<?php

use Phinx\Migration\AbstractMigration;

class Indexes extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    addCustomColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Any other destructive changes will result in an error when trying to
     * rollback the migration.
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('nodes_user_idx');
        $table->addColumn('email', 'string');
        $table->addForeignKey('id', 'nodes', 'id', ['delete' => 'cascade', 'update' => 'cascade']);
        $table->addIndex('email', ['name' => 'IDX_nodes_user_idx_email']);
        $table->create();

        $table = $this->table('nodes_file_idx');
        $table->addColumn('kind', 'string');
        $table->addForeignKey('id', 'nodes', 'id', ['delete' => 'cascade', 'update' => 'cascade']);
        $table->addIndex('kind', ['name' => 'IDX_nodes_file_idx_kind']);
        $table->create();
    }
}
