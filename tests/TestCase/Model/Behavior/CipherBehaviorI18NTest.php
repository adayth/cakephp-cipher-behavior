<?php
namespace CipherBehavior\Test\TestCase\Model\Behavior;

use Cake\TestSuite\TestCase;

/**
 * CipherBehavior\Model\Behavior\CipherBehavior Test Case
 */
class CipherBehaviorI18NTest extends CipherBehaviorTest
{    

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {        
        parent::setUp();
        \Cake\I18n\I18n::locale('es_ES');        
    }    

}
