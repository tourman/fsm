<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../FsmTestCase.php')));

/**
 * public function test_Action_InvalidTypeArguments_ThrowsException()
 * public function test_Action_ValidArguments_CallsIsInitialized()
 * public function test_Action_ValidArguments_CallsIsSleep()
 * public function test_Action_ValidArguments_CallsVerifySymbol()
 * public function test_Action_ValidArguments_CallsAppropriateMethodWithTheArguments()
 * public function test_Action_ValidArguments_SetsState()
 * public function test_Action_ValidArguments_PushesLog()
 * public function test_Action_ValidArguments_CallsAppropriateMethodBeforeStateIsSet()
 * public function test_Action_ValidArguments_CallsAppropriateMethodBeforeLogIsPushed()
 * public function test_Action_NoArguments_UsesDefaultValueOfArguments()
 * public function test_Action_ValidArguments_ReturnsMethodReturnResult()
 */
class Fsm_ActionTest extends FsmTestCase
{
    public function setUp()
    {
        $this->_fsm = $this->getMockBuilder(self::FSM_CLASS_NAME)->
            disableOriginalConstructor();
    }

    //It's essential to call this method every time we use test method
    public function setMethods($methods = array())
    {
        $methods = array_merge(
            array(
                'isInitialized',
                'isSleep',
                'verifySymbol',
                'getTimestamp',
            ),
            $methods
        );
        $this->_fsm = $this->_fsm->setMethods($methods)->getMock();
    }

    public function provideInvalidTypeArguments()
    {
        return array(
            array(
                'symbol' => '*',
                'arguments' => true,
            ),
            array(
                'symbol' => '*',
                'arguments' => 1,
            ),
            array(
                'symbol' => '*',
                'arguments' => 1.1,
            ),
            array(
                'symbol' => '*',
                'arguments' => 'true',
            ),
            array(
                'symbol' => '*',
                'arguments' => new stdClass(),
            ),
            array(
                'symbol' => '*',
                'arguments' => null,
            ),
        );
    }

    /**
     * @dataProvider provideInvalidTypeArguments
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 101
     */
    public function test_Action_InvalidTypeArguments_ThrowsException($symbol, $arguments)
    {
        $this->setMethods();
        try {
            $this->_fsm->action($symbol, $arguments);
        } catch (InvalidArgumentException $e) {
            $this->assertInvalidTypeArgumentExceptionMessage($e, 'arguments');
            throw $e;
        }
    }

    public function provideValidArguments()
    {
        $stateSet = array_shift(array_shift($this->provideValidStateSets()));
        return array(
            array(
                'stateSet' => $stateSet,
                'symbol' => '*',
                'arguments' => array(),
            ),
        );
    }

    protected function _getArguments()
    {
        $arguments = array();
        $numArguments = rand(0, 3);
        for ($i = 0; $i < $numArguments; $i++) {
            $arguments[] = md5(uniqid());
        }
        return $arguments;
    }

    public function provideSymbols()
    {
        return array(
            array(
                'symbol' => md5(uniqid()),
                'arguments' => $this->_getArguments(),
            ),
        );
    }

    /**
     * @dataProvider provideSymbols
     * @expectedException RuntimeException
     * @expectedExceptionCode 111
     * @expectedExceptionMessage States are not set
     */
    public function test_Action_ValidArguments_CallsIsInitialized($symbol, $arguments)
    {
        $this->setMethods();
        $this->_fsm->expects($this->once())->method('isInitialized')->will($this->returnValue(false));
        $this->_fsm->action($symbol, $arguments);
    }

    /**
     * @group issue1
     * @group issue1_sleep_protected
     * @dataProvider provideSymbols
     * @expectedException Exception
     * @expectedExceptionCode 112
     * @expectedExceptionMessage Sleep mode
     */
    public function test_Action_ValidArguments_CallsIsSleep($symbol, $arguments)
    {
        $this->setMethods();
        $this->_fsm->expects($this->once())->method('isInitialized')->with()->will($this->returnValue(true));
        $this->_fsm->expects($this->once())->method('isSleep')->with()->will($this->returnValue(true));
        $this->_fsm->action($symbol, $arguments);
    }

    /**
     * @dataProvider provideSymbols
     */
    public function test_Action_ValidArguments_CallsVerifySymbol($symbol, $arguments)
    {
        $this->setMethods();
        $this->_fsm->expects($this->once())->method('isInitialized')->will($this->returnValue(true));
        $this->_fsm->expects($this->once())->method('isSleep')->will($this->returnValue(false));
        $this->_fsm->expects($this->once())->method('verifySymbol')->with($this->identicalTo($symbol));
        $this->_fsm->action($symbol, $arguments);
    }

    public function provideMethods()
    {
        $argumentSets = array();
        $stateSets = array_map('array_shift', $this->provideValidStateSets());
        foreach ($stateSets as $stateSet) {
            foreach ($stateSet as $state => $symbolSet) {
                foreach ($symbolSet as $symbol => $destination) {
                    if (!isset($destination['action'])) {
                        continue;
                    }
                    $argumentSets[] = array(
                        'stateSet' => $stateSet,
                        'state' => $state,
                        'symbol' => $symbol,
                        'arguments' => $this->_getArguments(),
                        'method' => $destination['action'],
                        'newState' => $destination['state'],
                        'logRecord' => array(
                            'state' => $destination['state'],
                            'reason' => 'action',
                            'symbol' => $symbol,
                            'timestamp' => $this->generateTimestamp(),
                        ),
                    );
                }
            }
        }
        return $argumentSets;
    }

    /**
     * @dataProvider provideMethods
     */
    public function test_Action_ValidArguments_CallsAppropriateMethodWithTheArguments($stateSet, $state, $symbol, $arguments, $method)
    {
        $this->setMethods(array($method));
        $this->setStateSet($stateSet);
        $this->setState($state);
        $this->_fsm->expects($this->once())->method('isInitialized')->will($this->returnValue(true));
        $this->_fsm->expects($this->once())->method('isSleep')->will($this->returnValue(false));
        $expectation = $this->_fsm->expects($this->once())->method($method);
        $expectedArguments = array_map(array($this, 'identicalTo'), $arguments);
        call_user_func_array(array($expectation, 'with'), $expectedArguments);
        $this->_fsm->action($symbol, $arguments);
    }

    /**
     * @dataProvider provideMethods
     */
    public function test_Action_ValidArguments_SetsState($stateSet, $state, $symbol, $arguments, $method, $newState)
    {
        $this->setMethods(array($method));
        $this->setStateSet($stateSet);
        $this->setState($state);
        $this->_fsm->expects($this->once())->method('isInitialized')->will($this->returnValue(true));
        $this->_fsm->expects($this->once())->method('isSleep')->will($this->returnValue(false));
        $this->_fsm->action($symbol, $arguments);
        $state = $this->getState();
        $this->assertEquals($newState, $state);
    }

    /**
     * @dataProvider provideMethods
     */
    public function test_Action_ValidArguments_PushesLog($stateSet, $state, $symbol, $arguments, $method, $newState, $expectedLogRecord)
    {
        $this->setMethods(array($method));
        $this->setStateSet($stateSet);
        $this->setState($state);
        $this->_fsm->expects($this->once())->method('getTimestamp')->will($this->returnValue($expectedLogRecord['timestamp']));
        $this->_fsm->expects($this->once())->method('isInitialized')->will($this->returnValue(true));
        $this->_fsm->expects($this->once())->method('isSleep')->will($this->returnValue(false));
        $this->_fsm->action($symbol, $arguments);
        $log = $this->getLog();
        $logRecord = array_pop($log);
        $this->assertSame($expectedLogRecord, $logRecord);
    }

    /**
     * @dataProvider provideMethods
     */
    public function test_Action_ValidArguments_CallsAppropriateMethodBeforeStateIsSet($stateSet, $expectedState, $symbol, $arguments, $method)
    {
        $this->setMethods(array($method));
        $this->setStateSet($stateSet);
        $this->setState($expectedState);
        $this->_fsm->expects($this->once())->method('isInitialized')->will($this->returnValue(true));
        $this->_fsm->expects($this->once())->method('isSleep')->will($this->returnValue(false));
        $exceptionHash = md5(uniqid());
        $this->_fsm->expects($this->once())->method($method)->will($this->throwException(new RuntimeException($exceptionHash)));
        try {
            $this->_fsm->action($symbol, $arguments);
        } catch (RuntimeException $e) {
            if ($e->getMessage() != $exceptionHash) {
                throw $e;
            }
        }
        $state = $this->getState();
        $this->assertSame($expectedState, $state);
    }

    /**
     * @dataProvider provideMethods
     */
    public function test_Action_ValidArguments_CallsAppropriateMethodBeforeLogIsPushed($stateSet, $expectedState, $symbol, $arguments, $method, $newState, $expectedLogRecord)
    {
        $this->setMethods(array($method));
        $this->setStateSet($stateSet);
        $this->setState($expectedState);
        //It should be checked for call time() any times because it should not be call actually
        $this->_fsm->expects($this->any())->method('getTimestamp')->will($this->returnValue($expectedLogRecord['timestamp']));
        $this->_fsm->expects($this->once())->method('isInitialized')->will($this->returnValue(true));
        $this->_fsm->expects($this->once())->method('isSleep')->will($this->returnValue(false));
        $exceptionHash = md5(uniqid());
        $this->_fsm->expects($this->once())->method($method)->will($this->throwException(new RuntimeException($exceptionHash)));
        try {
            $this->_fsm->action($symbol, $arguments);
        } catch (RuntimeException $e) {
            if ($e->getMessage() != $exceptionHash) {
                throw $e;
            }
        }
        $log = $this->getLog();
        $logRecord = array_pop($log);
        $this->assertNotSame($expectedLogRecord, $logRecord);
    }

    /**
     * @dataProvider provideMethods
     */
    public function test_Action_NoArguments_UsesDefaultValueOfArguments($stateSet, $state, $symbol, $arguments, $method)
    {
        $this->setMethods(array($method));
        $this->setStateSet($stateSet);
        $this->setState($state);
        $this->_fsm->expects($this->once())->method('isInitialized')->will($this->returnValue(true));
        $this->_fsm->expects($this->once())->method('isSleep')->will($this->returnValue(false));
        $this->_fsm->expects($this->once())->method($method)->with();
        $this->_fsm->action($symbol);
    }

    /**
     * @dataProvider provideMethods
     */
    public function test_Action_ValidArguments_ReturnsMethodReturnResult($stateSet, $state, $symbol, $arguments, $method)
    {
        $expectedResult = md5(uniqid());
        $this->setMethods(array($method));
        $this->setStateSet($stateSet);
        $this->setState($state);
        $this->_fsm->expects($this->once())->method('isInitialized')->will($this->returnValue(true));
        $this->_fsm->expects($this->once())->method('isSleep')->will($this->returnValue(false));
        $this->_fsm->expects($this->once())->method($method)->will($this->returnValue($expectedResult));
        $result = $this->_fsm->action($symbol, $arguments);
        $this->assertSame($expectedResult, $result);
    }
}
