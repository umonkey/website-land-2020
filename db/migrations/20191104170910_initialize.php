<?php

use Phinx\Migration\AbstractMigration;

class Initialize extends AbstractMigration
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
        $table = $this->table('nodes', ['id' => 'id']);
        $table->addColumn('parent', 'integer');
        $table->addColumn('lb', 'integer');
        $table->addColumn('rb', 'integer');
        $table->addColumn('type', 'string', ['length' => 32]);
        $table->addColumn('created', 'datetime');
        $table->addColumn('updated', 'datetime');
        $table->addColumn('key', 'string', ['length' => 32]);
        $table->addColumn('published', 'boolean', ['null' => false, 'default' => 1]);
        $table->addColumn('deleted', 'boolean', ['null' => false, 'default' => 0]);
        $table->addColumn('more', 'binary');
        $table->addForeignKey('parent', 'nodes', 'id', ['delete' => 'cascade', 'update' => 'cascade']);
        $table->addIndex(['lb'], ['name' => 'IDX_nodes_lb', 'unique' => false]);
        $table->addIndex(['rb'], ['name' => 'IDX_nodes_rb']);
        $table->addIndex(['type'], ['name' => 'IDX_nodes_type']);
        $table->addIndex(['created'], ['name' => 'IDX_nodes_created']);
        $table->addIndex(['updated'], ['name' => 'IDX_nodes_updated']);
        $table->addIndex(['key'], ['name' => 'IDX_nodes_key', 'unique' => true]);
        $table->addIndex(['published'], ['name' => 'IDX_nodes_published']);
        $table->addIndex(['deleted'], ['name' => 'IDX_nodes_deleted']);
        $table->create();

        $table = $this->table('nodes_rel', ['id' => false]);
        $table->addColumn('tid', 'integer');
        $table->addColumn('nid', 'integer');
        $table->addForeignKey('tid', 'nodes', 'id', ['delete' => 'cascade', 'update' => 'cascade']);
        $table->addForeignKey('nid', 'nodes', 'id', ['delete' => 'cascade', 'update' => 'cascade']);
        $table->create();

        $table = $this->table('cache', ['id' => false, 'primary_key' => 'key']);
        $table->addColumn('key', 'string', ['length' => 32]);
        $table->addColumn('added', 'integer', ['signed' => false]);
        $table->addColumn('value', 'binary');
        $table->create();

        $table = $this->table('history', ['id' => 'id']);
        $table->addColumn('node_id', 'integer');
        $table->addColumn('created', 'datetime');
        $table->addColumn('data', 'binary');
        $table->create();

        $table = $this->table('sessions', ['id' => false]);
        $table->addColumn('id', 'string', ['length' => 32]);
        $table->addColumn('updated', 'datetime');
        $table->addColumn('data', 'binary');
        $table->create();
    }

    public function up()
    {
    }
}
