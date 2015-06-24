# CipherBehavior plugin for CakePHP
Cipher your entities data magically with CakePHP Security class and this behavior.

## Installation
First install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The easy way to install composer packages is:
```
composer require adayth/cakephp-cipher-behavior
```

After that you should load the plugin in your app editing `config/bootstrap.php`:
```php
Plugin::load('CipherBehavior');
```

## Usage
You can add this behavior to a table to encrypt/decrypt your entities data while saving/retrieving them from DB.
To use it you should define binary columns in your table schema to store encrypted data.

Table schema example for storing encrypted credit cards:
```sql
CREATE TABLE IF NOT EXISTS `credit_cards` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` blob NOT NULL,
  `number` blob NOT NULL,
  `expire_date` blob NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
```

Following the example, to use the behavior with this table:
```php
class CreditCardsTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        // Add Cipher behavior
        $this->addBehavior('CipherBehavior.Cipher', [
            'fields' => [
                'type' => 'string',
                'number' => 'string',
                'expire_date' => 'date',
            ]
        ]);
    }
}
```

## Behavior configuration
Configuration allows to specify what fields are managed by the behavior and configure encryption key/salt.

* **fields** (required): array of fields to be managed by the behavior. *Keys* are column names and *values* are column types registered
in `Cake\Database\Type`. See [CakePHP Book - DataTypes](http://book.cakephp.org/3.0/en/orm/database-basics.html#data-types)
to view core CakePHP types.
* **key** (required/optional): you can specify a key to be used by Security class to encrypt/decrypt data as part of behavior config.
* **salt** (required/optional): you can specify a salt to be used by Security class to encrypt/decrypt data as part of behavior config.

*key* and *salt* can also be set globally using two configuration keys: `App.Encrypt.key` and `App.Encrypt.salt`.
Example:
```php
Configure::write('App.Encrypt.key', 'your long not legible key');
Configure::write('App.Encrypt.salt', 'your long not legible salt');
```

To get a good pair of key and salt, you could use [Wordpress key/salt generator](https://api.wordpress.org/secret-key/1.1/salt/), 
[Random Key Generator](http://randomkeygen.com/) or allow your cat/dog/insert your pet here to play with your keyboard a minute...

## Implementation notes
The ciphering is done with *beforeSave and beforeFind* events, using CakePHP *Security* class *encrypt / decrypt* methods
and `Cake\Database\Type` to convert data from and to DB to the right types. Type columns use is needed because all data is stored 
and ciphered in DB in binary format. So before/after encrypt/decrypt casting types is needed.

**Important:** Current tests only covers *string*, *integer* and *date* column types.

## Support
For bugs and feature requests, please use the [issues](https://github.com/adayth/cakephp-cipher-behavior/issues) section of this repository.

## Contributing
Contributions are welcome. You sohuld follow this guide:

* Pull requests must be send to the ```dev``` branch.
* Follow [CakePHP coding standard](http://book.cakephp.org/3.0/en/contributing/cakephp-coding-conventions.html).
* Please, add [Tests](http://book.cakephp.org/3.0/en/development/testing.html) to new features.

## License
Copyright 2015, Aday Talavera <aday.talavera at gmail.com>

Licensed under The MIT License.