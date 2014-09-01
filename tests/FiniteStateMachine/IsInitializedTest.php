<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../FsmTestCase.php')));

/**
 * public function test_IsInitialized_BeforeStates_ReturnsFalse()
 * public function test_IsInitialized_AfterStates_ReturnsTrue()
 */
class Fsm_IsInitializedTest extends FsmTestCase
{
    public function setUp()
    {
        $this->_fsm = $this->getMockBuilder(self::FSM_CLASS_NAME)->
            disableOriginalConstructor()->
            setMethods(array('verifyStateSet', 'setState'))->
            getMock();
    }

    public function test_IsInitialized_BeforeStates_ReturnsFalse()
    {
        $result = $this->_fsm->isInitialized();
        $this->assertFalse($result);
    }

    /**
     * @dataProvider provideValidStateSets
     */
    public function test_IsInitialized_AfterStates_ReturnsTrue($stateSet)
    {
        $this->_fsm->setStates($stateSet);
        $result = $this->_fsm->isInitialized();
        $this->assertTrue($result);
    }
}
