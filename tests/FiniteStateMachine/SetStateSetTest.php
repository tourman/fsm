<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../FsmTestCase.php')));

/**
 * public function test_SetStateSet_IsInitializedReturnsTrue_ThrowsException()
 * public function test_SetStateSet_ValidArguments_UnsetsSleep()
 * public function test_SetStateSet_ValidArguments_CallsIsInitialized()
 * public function test_SetStateSet_ValidArguments_CallsIsVerifyLog()
 * public function test_SetStateSet_ValidArguments_SetsStateSet()
 * public function test_SetStateSet_ValidArguments_SetsState()
 * public function test_SetStateSet_ValidArguments_SetsLog()
 * public function test_SetStateSet_DefaultLogArgument_MatchesEmptyArray()
 */
class Fsm_SetStateSetTest extends FsmTestCase
{
    public function setUp()
    {
        $this->_fsm = $this->getMockBuilder(self::FSM_CLASS_NAME)->
            disableOriginalConstructor()->
            setMethods(array('verifyLog', 'isInitialized', 'getTimestamp'))->
            getMock();
    }

    public function getStateSet()
    {
        $class = new ReflectionClass($this->_fsm);
        $property = $class->getProperty('_stateSet');
        $property->setAccessible(true);
        $value = $property->getValue($this->_fsm);
        $property->setAccessible(false);
        return $value;
    }

    public function provideValidArguments()
    {
        $argumentSets = array();
        $stateSets = array_map('array_shift', $this->provideValidStateSets());
        foreach ($stateSets as $stateSet) {
            foreach ($stateSet as $state => &$symbolSet) {
                $symbolSet[md5(uniqid())] = array(
                    'state' => $state,
                );
                break;
            }
            unset($symbolSet);
            $log = array(
                array(
                    'state' => $state,
                    'reason' => md5(uniqid()),
                    'symbol' => md5(uniqid()),
                    'timestamp' => $this->generateTimestamp(),
                ),
                array(
                    'state' => $state,
                    'reason' => md5(uniqid()),
                    'symbol' => md5(uniqid()),
                    'timestamp' => $this->generateTimestamp(),
                ),
            );
            $argumentSets[] = array(
                'stateSet' => $stateSet,
                'log' => $log,
                'expectedLog' => array_merge($log, array(
                    array(
                        'state' => $state,
                        'reason' => 'wakeup',
                        'symbol' => null,
                        'timestamp' => $this->generateTimestamp(),
                    )
                )),
            );
            $argumentSets[] = array(
                'stateSet' => $stateSet,
                'log' => array(),
                'expectedLog' => array(
                    array(
                        'state' => $state,
                        'reason' => 'init',
                        'symbol' => null,
                        'timestamp' => $this->generateTimestamp(),
                    ),
                ),
            );
        }
        return $argumentSets;
    }

    /**
     * @dataProvider provideValidArguments
     * @expectedException RuntimeException
     * @expectedExceptionCode 110
     * @expectedExceptionMessage States are set
     */
    public function test_SetStateSet_IsInitializedReturnsTrue_ThrowsException($stateSet, $log)
    {
        $this->_fsm->expects($this->once())->method('isInitialized')->will($this->returnValue(true));
        $this->_fsm->setStateSet($stateSet, $log);
    }

    /**
     * @group issue1
     * @group issue_sleep_protected
     * @dataProvider provideValidArguments
     */
    public function test_SetStateSet_ValidArguments_UnsetsSleep($stateSet, $log)
    {
        $this->_fsm->expects($this->once())->method('isInitialized')->will($this->returnValue(false));
        $this->_fsm->setStateSet($stateSet, $log);
        $sleep = $this->getSleep();
        $this->assertFalse($sleep);
    }

    /**
     * @dataProvider provideValidArguments
     */
    public function test_SetStateSet_ValidArguments_CallsIsInitialized($stateSet, $log)
    {
        $this->_fsm->expects($this->once())->method('isInitialized')->will($this->returnValue(false));
        $this->_fsm->setStateSet($stateSet, $log);
    }

    public function test_SetStateSet_ValidArguments_CallsVerifyLog()
    {
        $stateSet = array(md5(uniqid()));
        $log = array(md5(uniqid()));
        $this->_fsm->expects($this->once())->method('isInitialized')->will($this->returnValue(false));
        $this->_fsm->expects($this->once())->method('verifyLog')->with(
            $this->identicalTo($stateSet),
            $this->identicalTo($log)
        );
        $this->_fsm->setStateSet($stateSet, $log);
    }

    /**
     * @dataProvider provideValidArguments
     */
    public function test_SetStateSet_ValidArguments_SetsStateSet($expectedStateSet, $log)
    {
        $this->_fsm->setStateSet($expectedStateSet, $log);
        $stateSet = $this->getStateSet();
        $this->assertSame($expectedStateSet, $stateSet);
    }

    /**
     * @dataProvider provideValidArguments
     */
    public function test_SetStateSet_ValidArguments_SetsState($stateSet, $log)
    {
        $states = array_keys($stateSet);
        $this->_fsm->setStateSet($stateSet, $log);
        $state = $this->getState();
        $this->assertSame($states[0], $state);
    }

    /**
     * @group issue1
     * @dataProvider provideValidArguments
     */
    public function test_SetStateSet_ValidArguments_SetsLog($stateSet, $log, $expectedLog)
    {
        $expectedLogRecord = array_shift(array_slice($expectedLog, -1));
        $className = get_class($this->_fsm);
        $this->_fsm = $this->getMockBuilder($className)->setMethods(array('getTimestamp'))->getMock();
        $this->_fsm->expects($this->any())->method('getTimestamp')->will($this->returnValue($expectedLogRecord['timestamp']));
        $this->_fsm->setStateSet($stateSet, $log);
        $log = $this->getLog();
        $this->assertSame($expectedLog, $log);
    }

    /**
     * @dataProvider provideValidArguments
     */
    public function test_SetStateSet_DefaultLogArgument_MatchesEmptyArray($stateSet)
    {
        $this->_fsm->expects($this->once())->method('isInitialized')->will($this->returnValue(false));
        $this->_fsm->expects($this->once())->method('verifyLog')->with(
            $this->identicalTo($stateSet),
            $this->identicalTo(array())
        );
        $this->_fsm->setStateSet($stateSet);
    }
}
