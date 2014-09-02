<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../FsmTestCase.php')));

/**
 * public function test_Sleep_Before_MethodIsAllowed()
 * public function test_Sleep_After_MethodIsBlocked()
 * public function test_Sleep_Default_ReturnsLog()
 * public function test_Sleep_Default_AppendsSleepItemToLog()()
 */
class Fsm_SleepTest extends FsmTestCase
{
    public function setUp()
    {
        parent::setUp();
        $stateSet = array_shift(array_shift($this->provideValidStateSets()));
        $this->_fsm->setStateSet($stateSet);
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

    public function test_Sleep_Default_ReturnsLog()
    {
        $expectedLog = md5(uniqid());
        $this->setLog($expectedLog);
        $log = $this->_fsm->sleep();
        $this->assertSame($expectedLog, $log);
    }
}
