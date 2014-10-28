<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../FsmTestCase.php')));

/**
 * public function test_Reset_CallsIsInitialized()
 * public function test_Reset_CallsIsSleep()
 * public function test_Reset_SetsState()
 * public function test_Reset_PushesLog()
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
                'expectedTimestamp' => '1.867552',
                'expectedLog' => array(
                    array(
                        'state' => 'INIT',
                        'reason' => 'reset',
                        'symbol' => null,
                        'timestamp' => '1.867552',
                    ),
                ),
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
                'expectedTimestamp' => '1.867553',
                'expectedLog' => array(
                    array(
                        'state' => 'START',
                        'reason' => 'reset',
                        'symbol' => null,
                        'timestamp' => '1.867553',
                    ),
                ),
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
     * @group issue1
     * @dataProvider provideValidStateSets
     */
    public function test_Reset_PushesLog($stateSet, $expectedState, $expectedTimestamp, $expectedLog)
    {
        $this->setStateSet($stateSet);
        $this->_fsm->expects($this->once())->method('isInitialized')->will($this->returnValue(true));
        $this->_fsm->expects($this->once())->method('isSleep')->will($this->returnValue(false));
        $this->_fsm->expects($this->once())->method('getTimestamp')->will($this->returnValue($expectedTimestamp));
        $this->_fsm->reset();
        $log = $this->getLog();
        $this->assertEquals($expectedLog, $log);
    }
}
