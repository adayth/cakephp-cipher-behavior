<?php
namespace CipherBehavior\Test\TestCase\Model\Behavior;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CipherBehavior\Model\Behavior\CipherBehavior;

/**
 * CipherBehavior\Model\Behavior\CipherBehavior Test Case
 */
class CipherBehaviorTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.cipher_behavior.binary_values',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        Configure::write('App.Encrypt.key', 'W9I<lHZ%$=Tbh`+xXxUE,b{%}d.9]&6)^0Tc uT1=9j$ GC-4v|g>2~eO%e/p?@?');
        Configure::write('App.Encrypt.salt', '@2H0jZLy|&tf#mQ{tGX>JZ{; KtNg}7l*;Kqck5z>X~Fv-!l^ZN]LvQ3?a%uh,C_');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
    }
    
    public function testEmptyFieldsInitialization()
    {
        $this->setExpectedException('\Cake\Core\Exception\Exception');
        $table = TableRegistry::get('BinaryValues');
        $config = [];
        $cypherBehaviorInstance = new CipherBehavior($table, $config);
    }
        
    public function testWrongFieldsInitialization()
    {
        $this->setExpectedException('\Cake\Core\Exception\Exception');
        $table = TableRegistry::get('BinaryValues');
        $config = ['fields' => ['name' => 'string', 'wrong_config_field']];
        $cypherBehaviorInstance = new CipherBehavior($table, $config);
    }
        
    public function testWrongFieldTypeInitialization()
    {
        $this->setExpectedException('\Cake\Core\Exception\Exception');
        $table = TableRegistry::get('BinaryValues');
        $config = ['fields' => ['name' => 'error_type_error']];
        $cypherBehaviorInstance = new CipherBehavior($table, $config);
    }
        
    public function testEmptyEncryptKeyInitialization()
    {
        Configure::consume('App.Encrypt.key');
        $this->setExpectedException('\Cake\Core\Exception\Exception');
        $table = TableRegistry::get('BinaryValues');
        $config = ['fields' => ['name' => 'string']];
        $cypherBehaviorInstance = new CipherBehavior($table, $config);
    }
        
    public function testEmptyEncryptSaltInitialization()
    {
        Configure::consume('App.Encrypt.salt');
        $this->setExpectedException('\Cake\Core\Exception\Exception');
        $table = TableRegistry::get('BinaryValues');
        $config = ['fields' => ['name' => 'string']];
        $cypherBehaviorInstance = new CipherBehavior($table, $config);
    }
    
    public function testEncrypt()
    {
        $table = TableRegistry::get('BinaryValues');
        $config = ['fields' => ['name' => 'string']];
        $cypherBehaviorInstance = new CipherBehavior($table, $config);
        $value = 'foo value to be encrypted';
        $this->assertNotEquals($value, $cypherBehaviorInstance->encrypt($value));
    }
    
    public function testDecrypt()
    {
        $table = TableRegistry::get('BinaryValues');
        $config = ['fields' => ['name' => 'string']];
        $cypherBehaviorInstance = new CipherBehavior($table, $config);
        $value = 'foo value to be decrypted';
        $this->assertNotEquals($value, $cypherBehaviorInstance->decrypt($value));
    }
    
    public function testCipher()
    {
        $table = TableRegistry::get('BinaryValues');
        $config = ['fields' => ['name' => 'string']];
        $cypherBehaviorInstance = new CipherBehavior($table, $config);
        $value = 'foo value to be encrypted';
        $this->assertNotEquals($value, $cypherBehaviorInstance->encrypt($value));
        $this->assertEquals($value, $cypherBehaviorInstance->decrypt($cypherBehaviorInstance->encrypt($value)));
    }
    
    public function testBeforeMarshall()
    {
        $table = TableRegistry::get('BinaryValues');
        $config = ['fields' => ['my_name' => 'string', 'my_date' => 'date', 'my_time' => 'time', 'my_datetime' => 'datetime']];
        $cypherBehaviorInstance = new CipherBehavior($table, $config);
        $table->behaviors()->set('Cipher', $cypherBehaviorInstance);
        
        // Data for beforemarshall function
        $data = ['my_name' => 'foo-name', 'my_date' => '2015-06-01', 'my_time' => '12:34:56', 'my_datetime' => '2015-06-01 12:34:56'];
        $options = [];
        
        // Dispatch beforeMarshalll event
        $data = new \ArrayObject($data);
        $options = new \ArrayObject($options);
        $table->dispatchEvent('Model.beforeMarshal', compact('data', 'options'));
        
        // Verify data after beforeMarshall being dispatched
        $this->assertEquals('foo-name', $data['my_name']);
        
        $this->assertInstanceOf('\Cake\I18n\Time', $data['my_date']);
        $this->assertEquals('2015-06-01', $data['my_date']->toDateString());
        
        $this->assertInstanceOf('\Cake\I18n\Time', $data['my_time']);
        $this->assertEquals('12:34:56', $data['my_time']->toTimeString());
        
        $this->assertInstanceOf('\Cake\I18n\Time', $data['my_datetime']);
        $this->assertEquals('2015-06-01 12:34:56', $data['my_datetime']->toDateTimeString());
    }
    
    public function testBeforeAfterSave()
    {
        $table = TableRegistry::get('BinaryValues');
        $config = ['fields' => ['type' => 'string', 'number' => 'integer', 'expire_date' => 'date']];
        $cypherBehaviorInstance = new CipherBehavior($table, $config);
        $table->behaviors()->set('Cipher', $cypherBehaviorInstance);
        
        // Data for beforeSave function
        $data = ['id' => 1, 'type' => 'my-type', 'number' => 123456789, 'expire_date' => '2015-06-01'];
        $options = [];

        // Dispatch beforeSave event
        $entity = $table->newEntity($data);
        $options = new \ArrayObject($options);
        $table->dispatchEvent('Model.beforeSave', compact('entity', 'options'));
        
        // Verify that entity data has been cyphered
        $this->assertEquals($data['id'], $entity->id);
        $this->assertNotEquals($data['type'], $entity->type);
        $this->assertNotEquals($data['number'], $entity->number);
        $this->assertNotEquals($data['expire_date'], $entity->expire_date);
        $this->assertNotEmpty($entity->_cyphered);

        // Dispatch afterSave event
        $options = new \ArrayObject($options);
        $table->dispatchEvent('Model.afterSave', compact('entity', 'options'));
        
        // Verify that entity data has been restored
        $this->assertEmpty($entity->dirty());
        $this->assertInstanceOf('\Cake\I18n\Time', $entity->expire_date);
        $entity->expire_date = $entity->expire_date->toDateString();
        $this->assertEquals($data, $entity->toArray());
    }
    
    public function testDecryptOnFind()
    {
        $table = TableRegistry::get('BinaryValues');
        $config = ['fields' => ['type' => 'string', 'number' => 'integer', 'expire_date' => 'date']];
        $cypherBehaviorInstance = new CipherBehavior($table, $config);
        $table->behaviors()->set('Cipher', $cypherBehaviorInstance);
        
        $data = ['type' => 'my-type', 'number' => 123456789, 'expire_date' => '2015-06-01'];
        
        // Save entity to be stored ciphered in the DB
        $entity = $table->newEntity($data);
        $this->assertNotFalse($table->save($entity));
        
        // Retrieve entity from DB deciphered
        $entity2 = $table->get($entity->id);
        
        // Check retrieved values with stored values
        $this->assertEquals($entity->toArray(), $entity2->toArray());
        
        // Retrieve raw data from DB and check that its ciphered
        $rawData = $table->connection()->execute('SELECT * FROM binary_values WHERE id = ?', [$entity->id])->fetch('assoc');
        $this->assertEquals($entity->id, $rawData['id']);
        unset($rawData['id']);
        $this->assertNotEquals($data, $rawData);
    }
}
