<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../FsmTestCase.php')));

/**
 * public function test_Reset_IsInitializedReturnsFalse_ThrowsException()
 * public function test_Reset_ValidArguments_SetsState()
 * public function test_Reset_ValidArguments_PushesLog()
 */
class Fsm_ResetTest extends FsmTestCase
{
    public function setUp()
    {
        $this->_fsm = $this->getMockBuilder(self::FSM_CLASS_NAME)->
            disableOriginalConstructor()->
            setMethods(array('isInitialized', 'getTimestamp'))->
            getMock();
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionCode 111
     */
    public function test_Reset_IsInitializedReturnsFalse_ThrowsException()
    {
        $this->_fsm->expects($this->once())->method('isInitialized')->will($this->returnValue(false));
        $this->_fsm->reset();
    }

    /**
     * @dataProvider provideValidStateSets
     */
    public function test_Reset_Default_SetsState($stateSet)
    {
        $states = array_keys($stateSet);
        $this->setStateSet($stateSet);
        $this->_fsm->expects($this->once())->method('isInitialized')->will($this->returnValue(true));
        $this->_fsm->reset();
        $state = $this->getState();
        $this->assertEquals($states[0], $state);
    }

    /**
     * @dataProvider provideValidStateSets
     */
    public function test_Reset_ValidArguments_PushesLog($stateSet)
    {
        $states = array_keys($stateSet);
        $expectedLogRecord = array(
            'state' => $states[0],
            'reason' => 'reset',
            'symbol' => null,
            'timestamp' => $this->generateTimestamp(),
        );
        $className = get_class($this->_fsm);
        $this->_fsm = $this->getMockBuilder($className)->setMethods(array('getTimestamp'))->getMock();
        $this->setStateSet($stateSet);
        $this->_fsm->expects($this->once())->method('getTimestamp')->with()->will($this->returnValue($expectedLogRecord['timestamp']));
        $this->_fsm->expects($this->once())->method('isInitialized')->will($this->returnValue(true));
        $this->_fsm->reset();
        $log = $this->getLog();
        $logRecord = array_pop($log);
        $this->assertSame($expectedLogRecord, $logRecord);
    }
}
