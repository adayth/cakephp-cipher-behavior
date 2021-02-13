<?php
declare(strict_types=1);

namespace CipherBehavior\Model\Behavior;

use ArrayObject;
use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Database\Type;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\Utility\Security;

/**
 * Cipher behavior
 */
class CipherBehavior extends Behavior
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'fields' => [],
        'key' => null,
        'salt' => null,
    ];

    /**
     * Initialize behavior configuration
     * @param array $config Configuration passed to the behavior
     * @return void
     * @throws Exception
     */
    public function initialize(array $config): void
    {
        $fields = $this->getConfig('fields');
        if (empty($fields)) {
            throw new Exception('Empty fields in CipherBehavior');
        }

        foreach ($this->getConfig('fields') as $fieldName => $fieldType) {
            if (!is_string($fieldName) || !is_string($fieldType) || empty($fieldName) || empty($fieldType)) {
                throw new Exception('Field type need to be specified in CypherBehavior fields');
            }
            try {
                // Throws exception if type is not valid
                Type::build($fieldType);
            } catch (\InvalidArgumentException $ex) {
                throw new Exception(sprintf('Field type %s not valid for field %s', $fieldType, $fieldName));
            }
        }

        $key = $this->getConfig('key');
        if (empty($key)) {
            $key = Configure::read('App.Encrypt.key');
            if (empty($key)) {
                throw new Exception('App.Encrypt.key config value is empty');
            }
            $this->getConfig('key', $key);
        }

        $salt = $this->getConfig('salt');
        if (empty($salt)) {
            $salt = Configure::read('App.Encrypt.salt');
            if (empty($salt)) {
                throw new Exception('App.Encrypt.salt config value is empty');
            }
            $this->getConfig('salt', $salt);
        }
    }

    /**
     * Encrypt values before saving to DB
     * @param Event $event Event object
     * @param Entity $entity Entity object
     * @return void
     */
    public function beforeSave(Event $event, Entity $entity)
    {
        $entity->_cyphered = [];
        foreach ($this->getConfig('fields') as $field => $type) {
            if ($entity->has($field)) {
                $value = $entity->get($field);
                // Convert values to db representation before encrypting them
                $dbValue = Type::build($type)->toDatabase($value, $this->_table->connection()->driver());
                $cryptedValue = $this->encrypt($dbValue);
                $entity->set($field, $cryptedValue);
                $entity->_cyphered[$field] = $value;
            }
        }
    }

    /**
     * Restore original (unencrypted) values after saving to DB
     * @param Event $event Event object
     * @param Entity $entity Entity object
     * @param ArrayObject $options Save options array
     * @return void
     */
    public function afterSave(Event $event, Entity $entity, ArrayObject $options)
    {
        if ($entity->has('_cyphered') && is_array($entity->_cyphered)) {
            foreach ($entity->_cyphered as $field => $value) {
                $entity->set($field, $value);
                $entity->clean();
            }
            unset($entity->_cyphered);
        }
    }

    /**
     * Decrypt values after retrieving from DB
     * @param Event $event Event object
     * @param Query $query Query object
     * @param ArrayObject $options Query options array
     * @param type $primary Root/associated query
     * @return void
     */
    public function beforeFind(Event $event, Query $query, ArrayObject $options, $primary)
    {
        $fields = $this->getConfig('fields');
        $driver = $this->_table->connection()->driver();

        $formatter = function (\Cake\Collection\CollectionInterface $results) use ($fields, $driver) {
            return $results->each(function ($entity) use ($fields, $driver) {
                if ($entity instanceof \Cake\Datasource\EntityInterface) {
                    foreach ($fields as $field => $type) {
                        if ($entity->has($field)) {
                            $value = $entity->get($field);
                            $decryptedValue = $this->decrypt($value);
                            // Convert DB values to PHP values after decrypting them
                            $entity->set($field, Type::build($type)->toPHP($decryptedValue, $driver));
                            $entity->clean();
                        }
                    }
                }
            });
        };

        $query->formatResults($formatter);
    }

    /**
     * Helps converting data to correct type
     * @param Event $event Event object
     * @param ArrayObject $data Data array to be marshalled
     * @param ArrayObject $options Marshall option array
     * @return void
     */
    public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
    {
        $fields = $this->getConfig('fields');

        foreach ($fields as $field => $type) {
            if (isset($data[$field])) {
                $data[$field] = Type::build($type)->marshal($data[$field]);
            }
        }
    }

    /**
     * Encrypt a value
     * @param type $value Value to be encrypted
     * @return type Encrypted value
     */
    public function encrypt($value)
    {
        return Security::encrypt($value, $this->getConfig('key'), $this->getConfig('salt'));
    }

    /**
     * Decrypt an encrypted value
     * @param type $cryptedValue Value to be decrypted
     * @return type Decrypted value
     */
    public function decrypt($cryptedValue)
    {
        if (is_resource($cryptedValue)) {
            $cryptedValue = stream_get_contents($cryptedValue);
        }
        return Security::decrypt($cryptedValue, $this->getConfig('key'), $this->getConfig('salt'));
    }
}
