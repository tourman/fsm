<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../FsmTestCase.php')));

/**
 * public function test_Sleep_CallsIsInitialized()
 * public function test_Sleep_CallsIsSleep()
 * public function test_Sleep_SetsSleep()
 * public function test_Sleep_AppendsSleepItemToLog()
 * public function test_Sleep_ReturnsLog()
 **/
class Fsm_SleepTest extends FsmTestCase
{
    public function setUp()
    {
        parent::setUp();
        $methods = array(
            'isInitialized',
            'isSleep',
            'getTimestamp',
        );
        $this->_fsm = $this->getMockBuilder('TestFiniteStateMachine')->setMethods($methods)->getMock();
        $this->_fsm->method('getTimestamp')->will($this->returnValue('1.000000'));
        $stateSet = array_shift(array_shift($this->provideValidStateSets()));
        $this->_fsm->setStateSet($stateSet);
    }

    /**
     * @group issue1
     * @group issue1_is_initialized
     * @expectedException Exception
     * @expectedExceptionMessage States are not set
     * @expectedExceptionCode 111
     */
    public function test_Sleep_CallsIsInitialized()
    {
        $this->_fsm->expects($this->once())->method('isInitialized')->with()->will($this->returnValue(false));
        $this->_fsm->sleep();
    }

    /**
     * @group issue1
     * @group issue1_sleep_protected
     * @expectedException Exception
     * @expectedExceptionMessage Sleep mode
     * @expectedExceptionCode 112
     */
    public function test_Sleep_CallsIsSleep()
    {
        $this->_fsm->expects($this->once())->method('isInitialized')->with()->will($this->returnValue(true));
        $this->_fsm->expects($this->once())->method('isSleep')->with()->will($this->returnValue(true));
        $this->_fsm->sleep();
    }

    /**
     * @group issue1
     * @group issue1_sleep_protected
     */
    public function test_Sleep_SetsSleep()
    {
        $this->_fsm->expects($this->once())->method('isInitialized')->with()->will($this->returnValue(true));
        $this->_fsm->expects($this->once())->method('isSleep')->with()->will($this->returnValue(false));
        $sleepBefore = $this->getSleep();
        $this->_fsm->sleep();
        $sleepAfter = $this->getSleep();
        $this->assertFalse($sleepBefore);
        $this->assertTrue($sleepAfter);
    }

    public function provideSleepLogs()
    {
        $log = array(
            array(
                'state' => 'INIT',
                'reason' => 'init',
                'symbol' => null,
                'timestamp' => '1.000000',
            ),
        );
        return array(
            array(
                'state' => 'INIT',
                'log' => $log,
                'expectedLog' => array_merge($log, array(
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000000', //see setUp() method
                    ),
                )),
            ),
            array(
                'state' => 'CHECKOUT',
                'log' => array(),
                'expectedLog' => array(
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000000', //see setUp() method
                    ),
                ),
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_sleep_protected
     * @dataProvider provideSleepLogs
     */
    public function test_Sleep_AppendsSleepItemToLog($state, $log, $expectedLog)
    {
        $this->_fsm->expects($this->once())->method('isInitialized')->with()->will($this->returnValue(true));
        $this->_fsm->expects($this->once())->method('isSleep')->with()->will($this->returnValue(false));
        $this->setState($state);
        $this->setLog($log);
        $this->_fsm->sleep();
        $log = $this->getLog();
        $this->assertSame($expectedLog, $log);
    }

    /**
     * @group issue1
     * @group issue1_sleep_protected
     * @dataProvider provideSleepLogs
     */
    public function test_Sleep_ReturnsLog($state, $log, $expectedLog)
    {
        $this->_fsm->expects($this->once())->method('isInitialized')->with()->will($this->returnValue(true));
        $this->_fsm->expects($this->once())->method('isSleep')->with()->will($this->returnValue(false));
        $this->setState($state);
        $this->setLog($log);
        $log = $this->_fsm->sleep();
        $this->assertSame($expectedLog, $log);
    }
}
