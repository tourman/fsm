<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../FsmTestCase.php')));

/**
 * public function test_Sleep_Before_MethodIsAllowed()
 * public function test_Sleep_After_MethodIsBlocked()
 * public function test_Sleep_Default_AppendsSleepItemToLog()()
 */
/**
 * public function test_Sleep_CallsIsSleep()
 * public function test_Sleep_SetsSleep()
 * public function test_Sleep_AppendsSleepItemToLog()
 **/
class Fsm_SleepTest extends FsmTestCase
{
    public function setUp()
    {
        parent::setUp();
        $methods = array(
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
     * @group issue1_sleep_protected
     * @expectedException Exception
     * @expectedExceptionMessage Sleep mode
     * @expectedExceptionCode 112
     */
    public function test_Sleep_CallsIsSleep()
    {
        $this->_fsm->expects($this->once())->method('isSleep')->with()->will($this->returnValue(true));
        $this->_fsm->sleep();
    }

    /**
     * @group issue1
     * @group issue1_sleep_protected
     */
    public function test_Sleep_SetsSleep()
    {
        $this->_fsm->expects($this->once())->method('isSleep')->with()->will($this->returnValue(false));
        $sleepBefore = $this->getSleep();
        $this->_fsm->sleep();
        $sleepAfter = $this->getSleep();
        $this->assertFalse($sleepBefore);
        $this->assertTrue($sleepAfter);
    }

    public function provideMethods()
    {
        return array(
            array(
                'method' => 'action',
                'arguments' => array('checkout'),
            ),
            array(
                'method' => 'reset',
                'arguments' => array(),
            ),
            array(
                'method' => 'sleep',
                'arguments' => array(),
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_log_sleep
     * @dataProvider provideMethods
     * @expectedException Exception
     * @expectedExceptionMessage 47fcddc8193f1bed347ae752d8b30bbe
     */
    public function test_Sleep_Before_MethodIsAllowed($method, $arguments)
    {
        call_user_func_array(array($this->_fsm, $method), $arguments);
        throw new Exception('47fcddc8193f1bed347ae752d8b30bbe');
    }

    /**
     * @group issue1
     * @group issue1_log_sleep
     * @dataProvider provideMethods
     * @expectedException RuntimeException
     * @expectedExceptionCode 112
     * @expectedExceptionMessage Could not call method over the sleep mode
     */
    public function test_Sleep_After_MethodIsBlocked($method, $arguments)
    {
        $this->_fsm->sleep();
        call_user_func_array(array($this->_fsm, $method), $arguments);
    }

    /**
     * @group issue1
     * @group issue1_log_sleep
     */
    public function test_Sleep_Default_AppendsSleepItemToLog()
    {
        $expectedTimestamp = md5(uniqid());
        $expectedLog = array(
            md5(uniqid()),
            array(
                'state' => null,
                'reason' => 'sleep',
                'symbol' => null,
                'timestamp' => $expectedTimestamp,
            ),
        );
        $log = array_slice($expectedLog, 0, -1);
        $className = get_class($this->_fsm);
        $this->_fsm = $this->getMockBuilder($className)->setMethods(array('getTimestamp'))->getMock();
        $this->_fsm->expects($this->once())->method('getTimestamp')->will($this->returnValue($expectedTimestamp));
        $this->setLog($log);
        $log = $this->_fsm->sleep();
        $this->assertSame($expectedLog, $log);
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
        $this->setState($state);
        $this->setLog($log);
        $this->_fsm->sleep();
        $log = $this->getLog();
        $this->assertSame($expectedLog, $log);
    }
}
