<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../FsmTestCase.php')));

/**
 * public function test_SetStatusSet_EmptyLog_SetsInitialLog()
 * public function test_SetStatusSet_NotEmptyLog_SetsTheSameLog()
 * public function test_Sleep_Default_ProvidesValidStates
 * public function test_Sleep_Default_ProvidesValidReasons
 * public function test_Sleep_WakeUp_TheObjectsAreEqual
 * public function test_Cascade_VerifyLog_LogIsValidEveryStep()
 */
class Fsm_IntegrationTest extends FsmTestCase
{
    public function provideEmptyLogs()
    {
        $stateSet = array_shift(array_shift($this->provideValidStateSets()));
        return array(
            array(
                'stateSet' => $stateSet,
                'expectedLog' => array(
                    array(
                        'state' => 'INIT',
                        'reason' => 'init',
                        'symbol' => null,
                        'timestamp' => $this->generateTimestamp(),
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => $this->generateTimestamp(),
                    ),
                ),
            ),
        );
    }

    /**
     * @dataProvider provideEmptyLogs
     */
    public function test_SetStatusSet_EmptyLog_SetsInitialLog($stateSet, $expectedLog)
    {
        $className = get_class($this->_fsm);
        $this->_fsm = $this->getMockBuilder($className)->setMethods(array('getTimestamp'))->getMock();
        $this->_fsm->expects($this->at(0))->method('getTimestamp')->will($this->returnValue($expectedLog[0]['timestamp']));
        $this->_fsm->expects($this->at(1))->method('getTimestamp')->will($this->returnValue($expectedLog[1]['timestamp']));
        $this->_fsm->setStateSet($stateSet);
        $log = $this->_fsm->sleep();
        $this->assertSame($expectedLog, $log);
    }

    public function provideNotEmptyLogs()
    {
        $stateSet = array_shift(array_shift($this->provideValidStateSets()));
        $log = array(
            array(
                'state' => 'INIT',
                'reason' => 'init',
                'symbol' => null,
                'timestamp' => $this->generateTimestamp(),
            ),
            array(
                'state' => 'INIT',
                'reason' => 'action',
                'symbol' => '*',
                'timestamp' => $this->generateTimestamp(),
            ),
            array(
                'state' => 'CHECKOUT',
                'reason' => 'action',
                'symbol' => 'checkout',
                'timestamp' => $this->generateTimestamp(),
            ),
            array(
                'state' => 'CHECKOUT',
                'reason' => 'sleep',
                'symbol' => null,
                'timestamp' => $this->generateTimestamp(),
            ),
        );
        return array(
            array(
                'stateSet' => $stateSet,
                'log' => $log,
                'expectedLog' => array_merge($log, array(
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'wakeup',
                        'symbol' => null,
                        'timestamp' => $this->generateTimestamp(),
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => $this->generateTimestamp(),
                    ),
                )),
            ),
        );
    }

    /**
     * @dataProvider provideNotEmptyLogs
     */
    public function test_SetStatusSet_NotEmptyLog_SetsTheSameLog($stateSet, $log, $expectedLog)
    {
        $reversedLog = array_reverse($expectedLog);
        $className = get_class($this->_fsm);
        $this->_fsm = $this->getMockBuilder($className)->setMethods(array('getTimestamp'))->getMock();
        $this->_fsm->expects($this->at(0))->method('getTimestamp')->with();
        $this->_fsm->expects($this->at(1))->method('getTimestamp')->will($this->returnValue($reversedLog[1]['timestamp']));
        $this->_fsm->expects($this->at(2))->method('getTimestamp')->will($this->returnValue($reversedLog[0]['timestamp']));
        $this->_fsm->setStateSet($stateSet, $log);
        $log = $this->_fsm->sleep();
        $this->assertSame($expectedLog, $log);
    }

    public function provideSteps()
    {
        $stateSet = array_shift(array_shift($this->provideValidStateSets()));
        return array(
            array(
                'stateSet' => $stateSet,
                'steps' => array(),
                'expectedStates' => array(
                    'INIT',
                    'INIT',
                ),
                'expectedReasons' => array(
                    'init',
                    'sleep',
                ),
            ),
            array(
                'stateSet' => $stateSet,
                'steps' => array(
                    array(
                        'symbol' => '*',
                    ),
                    array(
                        'symbol' => 'checkout',
                    ),
                    array(
                        'symbol' => 'processing',
                    ),
                    array(
                        'symbol' => '*',
                    ),
                    array(
                        'symbol' => 'pending',
                    ),
                    array(
                        'symbol' => '*',
                    ),
                    array(
                        'symbol' => 'failed',
                    ),
                    array(
                        'symbol' => '*',
                    ),
                ),
                'expectedStates' => array(
                    'INIT',
                    'INIT',
                    'CHECKOUT',
                    'PROCESSING',
                    'PROCESSING',
                    'PENDING',
                    'PENDING',
                    'FAILED',
                    'FAILED',
                    'FAILED',
                ),
                'expectedReasons' => array(
                    'init',
                    'action',
                    'action',
                    'action',
                    'action',
                    'action',
                    'action',
                    'action',
                    'action',
                    'sleep',
                ),
            ),
        );
    }

    /**
     * @dataProvider provideSteps
     */
    public function test_Sleep_Default_ProvidesValidStates($stateSet, $steps, $expectedStates, $expectedReasons)
    {
        $this->_fsm->setStateSet($stateSet);
        foreach ($steps as $step) {
            $this->_fsm->action($step['symbol']);
        }
        $log = $this->_fsm->sleep();
        $states = array();
        foreach ($log as $logRecord) {
            $states[] = $logRecord['state'];
        }
        $this->assertSame($expectedStates, $states);
    }

    /**
     * @dataProvider provideSteps
     */
    public function test_Sleep_Default_ProvidesValidReasons($stateSet, $steps, $expectedStates, $expectedReasons)
    {
        $this->_fsm->setStateSet($stateSet);
        foreach ($steps as $step) {
            $this->_fsm->action($step['symbol']);
        }
        $log = $this->_fsm->sleep();
        $reasons = array();
        foreach ($log as $logRecord) {
            $reasons[] = $logRecord['reason'];
        }
        $this->assertSame($expectedReasons, $reasons);
    }

    public function jsonEncode(TestFiniteStateMachine $fsm)
    {
        $object = new stdClass();
        $reflection = new ReflectionClass($fsm);
        $properties = $reflection->getProperties();
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $object->{$property->getName()} = $property->getValue($fsm);
        }
        $jsonPretty = new Camspiers\JsonPretty\JsonPretty; //See composer file
        return $jsonPretty->prettify($object);
    }

    /**
     * @dataProvider provideSteps
     */
    public function test_Sleep_WakeUp_TheTwoObjectsAreEqual($stateSet, $steps, $expectedStates, $expectedReasons)
    {
        $first = $this->_createFsm();
        $first->setStateSet($stateSet);
        foreach ($steps as $step) {
            $first->action($step['symbol']);
        }
        $log = $first->sleep();
        $second = $this->_createFsm();
        $second->setStateSet($stateSet, $log);
        $log = $second->sleep();
        $third = $this->_createFsm();
        $third->setStateSet($stateSet, $log);

        $first = $this->jsonEncode($first);
        $second = $this->jsonEncode($second);
        $third = $this->jsonEncode($third);

        $this->assertEquals($first, $second);
        $this->assertEquals($first, $third);
    }
}
