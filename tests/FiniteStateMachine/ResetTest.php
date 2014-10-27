<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../FsmTestCase.php')));

/**
 * public function test_Reset_CallsIsInitialized()
 * public function test_Reset_CallsIsSleep()
 * public function test_Reset_SetsState()
 * public function test_Reset_ValidArguments_PushesLog()
 */
class Fsm_ResetTest extends FsmTestCase
{
    public function setUp()
    {
        $this->_fsm = $this->getMockBuilder(self::FSM_CLASS_NAME)->
            disableOriginalConstructor()->
            setMethods(array(
                'isInitialized',
                'isSleep',
                'getTimestamp',
            ))->getMock();
    }

    /**
     * @group issue1
     * @expectedException RuntimeException
     * @expectedExceptionCode 111
     * @expectedExceptionMessage States are not set
     */
    public function test_Reset_CallsIsInitialized()
    {
        $this->_fsm->expects($this->once())->method('isInitialized')->will($this->returnValue(false));
        $this->_fsm->reset();
    }

    /**
     * @group issue1
     * @expectedException RuntimeException
     * @expectedExceptionCode 112
     * @expectedExceptionMessage Sleep mode
     */
    public function test_Reset_CallsIsSleep()
    {
        $this->_fsm->expects($this->once())->method('isInitialized')->with()->will($this->returnValue(true));
        $this->_fsm->expects($this->once())->method('isSleep')->with()->will($this->returnValue(true));
        $this->_fsm->reset();
    }

    public function provideValidStateSets()
    {
        return array(
            array(
                'stateSet' => array(
                    'INIT' => array(
                        '*' => array(
                            'state' => 'INIT',
                        ),
                        'close' => array(
                            'state' => 'CLOSE',
                        ),
                    ),
                    'CLOSE' => array(),
                ),
                'expectedState' => 'INIT',
            ),
            array(
                'stateSet' => array(
                    'START' => array(
                        '*' => array(
                            'state' => 'START',
                        ),
                        'close' => array(
                            'state' => 'CLOSE',
                        ),
                    ),
                    'CLOSE' => array(),
                ),
                'expectedState' => 'START',
            ),
        );
    }

    /**
     * @group issue1
     * @dataProvider provideValidStateSets
     */
    public function test_Reset_SetsState($stateSet, $expectedState)
    {
        $this->setStateSet($stateSet);
        $this->_fsm->expects($this->once())->method('isInitialized')->will($this->returnValue(true));
        $this->_fsm->expects($this->once())->method('isSleep')->will($this->returnValue(false));
        $this->_fsm->reset();
        $state = $this->getState();
        $this->assertEquals($expectedState, $state);
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
