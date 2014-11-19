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
        $methods = array(
            'isInitialized',
            'isSleep',
            'verifySymbol',
            'getTimestamp',

            'close',
            'checkout',
            'process',
            'pend',
            'comlete',
            'error',
            'void',
        );
        $this->_fsm = $this->getMockBuilder(self::FSM_CLASS_NAME)->
            disableOriginalConstructor()->
            setMethods($methods)->
            getMock();
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
     * @expectedExceptionMessage Argument $arguments has invalid type
     */
    public function test_Action_InvalidTypeArguments_ThrowsException($symbol, $arguments)
    {
        $this->_fsm->action($symbol, $arguments);
    }

    public function provideSymbols()
    {
        return array(
            array(
                'symbol' => '5b16c1a61ca98f261e353cc368a8b64f',
                'arguments' => array(
                    '3f7d0ac4b1c7ab0a833377b93c4bb0ee',
                ),
            ),
            array(
                'symbol' => '42fb9bf30fd36ffe0710d7635b685c78',
                'arguments' => array(
                    '4a8060ad67b332f5bacc9e44ca39f251',
                    '91a7dd0a0e2597f3a1f85833de9ba416',
                ),
            ),
        );
    }

    /**
     * @group issue22_symbols
     * @dataProvider provideSymbols
     * @expectedException RuntimeException
     * @expectedExceptionCode 111
     * @expectedExceptionMessage States are not set
     */
    public function test_Action_ValidArguments_CallsIsInitialized($symbol, $arguments)
    {
        $this->_fsm->expects($this->once())->method('isInitialized')->will($this->returnValue(false));
        $this->_fsm->action($symbol, $arguments);
    }

    /**
     * @group issue1
     * @group issue1_sleep_protected
     * @group issue22_symbols
     * @dataProvider provideSymbols
     * @expectedException Exception
     * @expectedExceptionCode 112
     * @expectedExceptionMessage Sleep mode
     */
    public function test_Action_ValidArguments_CallsIsSleep($symbol, $arguments)
    {
        $this->_fsm->expects($this->once())->method('isInitialized')->with()->will($this->returnValue(true));
        $this->_fsm->expects($this->once())->method('isSleep')->with()->will($this->returnValue(true));
        $this->_fsm->action($symbol, $arguments);
    }

    /**
     * @group issue22_symbols
     * @dataProvider provideSymbols
     */
    public function test_Action_ValidArguments_CallsVerifySymbol($symbol, $arguments)
    {
        $this->_fsm->expects($this->once())->method('isInitialized')->will($this->returnValue(true));
        $this->_fsm->expects($this->once())->method('isSleep')->will($this->returnValue(false));
        $this->_fsm->expects($this->once())->method('verifySymbol')->with($this->identicalTo($symbol));
        $this->_fsm->action($symbol, $arguments);
    }

    public function provideMethods()
    {
        $stateSet = $this->_getBillingStateSet();
        return array(
            array(
                'stateSet' => $stateSet,
                'state' =>'INIT',
                'symbol' => 'checkout',
                'arguments' => array(
                    '91a7dd0a0e2597f3a1f85833de9ba416',
                ),
                'method' => 'checkout',
                'newState' => 'CHECKOUT',
                'expectedLogRecord' => array(
                    'state' => 'CHECKOUT',
                    'reason' => 'action',
                    'symbol' => 'checkout',
                    'timestamp' => $this->generateTimestamp(),
                ),
            ),
            array(
                'stateSet' => $stateSet,
                'state' =>'PROCESSING',
                'symbol' => 'pending',
                'arguments' => array(
                    'b026324c6904b2a9cb4b88d6d61c81d1',
                ),
                'method' => 'pend',
                'newState' => 'PENDING',
                'expectedLogRecord' => array(
                    'state' => 'PENDING',
                    'reason' => 'action',
                    'symbol' => 'pending',
                    'timestamp' => $this->generateTimestamp(),
                ),
            ),
            array(
                'stateSet' => $stateSet,
                'state' =>'PENDING',
                'symbol' => 'void',
                'arguments' => array(
                    '26ab0db90d72e28ad0ba1e22ee510510',
                ),
                'method' => 'void',
                'newState' => 'VOID',
                'expectedLogRecord' => array(
                    'state' => 'VOID',
                    'reason' => 'action',
                    'symbol' => 'void',
                    'timestamp' => $this->generateTimestamp(),
                ),
            ),
        );
    }

    /**
     * @dataProvider provideMethods
     */
    public function test_Action_ValidArguments_CallsAppropriateMethodWithTheArguments($stateSet, $state, $symbol, $arguments, $method)
    {
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
        $this->setStateSet($stateSet);
        $this->setState($state);
        $this->_fsm->expects($this->once())->method('isInitialized')->will($this->returnValue(true));
        $this->_fsm->expects($this->once())->method('isSleep')->will($this->returnValue(false));
        $this->_fsm->expects($this->once())->method($method)->will($this->returnValue($expectedResult));
        $result = $this->_fsm->action($symbol, $arguments);
        $this->assertSame($expectedResult, $result);
    }
}
