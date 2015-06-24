<?php
namespace CipherBehavior\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * BinaryEncryptionsFixture
 *
 */
class BinaryValuesFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 10, 'unsigned' => true, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],        
        'type' => ['type' => 'binary', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'number' => ['type' => 'binary', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'expire_date' => ['type' => 'binary', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],            
        ],
        '_options' => [
            'engine' => 'InnoDB',
            'collation' => 'utf8_general_ci'
        ],
    ];
    // @codingStandardsIgnoreEnd

    /**
     * Records
     *
     * @var array
     */
    public $records = [];
}
