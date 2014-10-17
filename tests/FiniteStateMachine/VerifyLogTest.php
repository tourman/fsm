<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../FsmTestCase.php')));

/**
 * public function test_VerifyLog_Default_CallsVerifyStateSet()
 * public function test_VerifyLog_InvalidTypeLog_ThrowsException()
 * public function test_VerifyLog_InvalidStructureLog_ThrowsException()
 * public function test_VerifyLog_InvalidLengthLog_ThrowsException()
 * public function test_VerifyLog_LogWithInvalidKeys_ThrowsException()
 * public function test_VerifyLog_ValidArguments_ReturnsTrue()
 */
class Fsm_VerifyLogTest extends FsmTestCase
{
    public function setUp()
    {
        $this->_fsm = $this->getMockBuilder(self::FSM_CLASS_NAME)->
            disableOriginalConstructor()->
            setMethods(array('verifyStateSet'))->
            getMock();
    }

    public function test_VerifyLog_Default_CallsVerifyStateSet()
    {
        $stateSet = array_shift(array_shift($this->provideValidStateSets()));
        //Add unique value for the identifying
        $stateSet['INIT'][md5(uniqid())] = $stateSet['INIT']['*'];
        $this->_fsm->expects($this->once())->method('verifyStateSet')->with($this->identicalTo($stateSet));
        $this->_fsm->verifyLog($stateSet, array());
    }

    protected function _testLogType($stateSet, $log, $logRecordIndex = null, $variable = null)
    {
        try {
            $this->_fsm->verifyLog($stateSet, $log);
        } catch (InvalidArgumentException $e) {
            $this->assertInvalidTypeArgumentExceptionMessage($e, 'log');
            if (!is_null($logRecordIndex) && !is_null($variable)) {
                $this->assertStringEndsWith("invalid type $variable at index $logRecordIndex", $e->getMessage());
            }
            throw $e;
        }
    }

    protected function _testLogValue($stateSet, $log, $logRecordIndex = null, $variable = null)
    {
        try {
            $this->_fsm->verifyLog($stateSet, $log);
        } catch (InvalidArgumentException $e) {
            $this->assertInvalidValueArgumentExceptionMessage($e, 'log');
            if (!is_null($logRecordIndex) && !is_null($variable)) {
                $this->assertStringEndsWith("invalid value $variable at index $logRecordIndex", $e->getMessage());
            }
            throw $e;
        }
    }

    protected function _testLogSequence($stateSet, $log, $logRecordIndex = null, $variable = null)
    {
        try {
            $this->_fsm->verifyLog($stateSet, $log);
        } catch (InvalidArgumentException $e) {
            $this->assertInvalidValueArgumentExceptionMessage($e, 'log');
            if (!is_null($logRecordIndex) && !is_null($variable)) {
                $this->assertStringEndsWith("invalid value $variable in sequence at index $logRecordIndex", $e->getMessage());
            }
            throw $e;
        }
    }

    protected function _provideLogs($logs)
    {
        $stateSets = array_map('array_shift', $this->provideValidStateSets());
        $argumentSets = array();
        foreach ($logs as $log) {
            $stateSetIndex = rand(0, sizeof($stateSets) - 1);
            $stateSet = $stateSets[$stateSetIndex];
            $argumentSets[] = array(
                'stateSet' => $stateSet,
                'log' => $log,
            );
        }
        return $argumentSets;
    }

    public function provideInvalidTypeLogs()
    {
        $logs = array(
            false,
            1,
            1.1,
            'false',
            new stdClass(),
            null,
        );
        return $this->_provideLogs($logs);
    }

    /**
     * @dataProvider provideInvalidTypeLogs
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 101
     */
    public function test_VerifyLog_InvalidTypeLog_ThrowsException($stateSet, $log)
    {
        $this->_testLogType($stateSet, $log);
    }

    public function provideInvalidStructureLogs()
    {
        $logs = array(
            array(false),
            array(1),
            array(1.1),
            array('false'),
            array(new stdClass()),
            array(null),
        );
        return $this->_provideLogs($logs);
    }

    public function provideInvalidLengthLogs()
    {
        $stateSet = array_shift(array_shift($this->provideValidStateSets()));
        return array(
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array(
                        'state' => 'INIT',
                        'reason' => 'init',
                        'symbol' => null,
                        'timestamp' => '1.000000',
                    ),
                ),
                'length' => 1,
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_reason
     * @group issue1_log_verification
     * @dataProvider provideInvalidLengthLogs
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 301
     */
    public function test_VerifyLog_InvalidLengthLog_ThrowsException($stateSet, $log, $length)
    {
        try {
            $this->_fsm->verifyLog($stateSet, $log);
        } catch (InvalidArgumentException $e) {
            $this->assertInvalidValueArgumentExceptionMessage($e, 'log');
            $this->assertStringEndsWith("invalid log length: $length", $e->getMessage());
            throw $e;
        }
    }

    /**
     * @dataProvider provideInvalidStructureLogs
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 102
     */
    public function test_VerifyLog_InvalidStructureLog_ThrowsException($stateSet, $log)
    {
        $this->_testLogValue($stateSet, $log);
    }

    public function providLogsWithInvalidKeys()
    {
        $stateSets = array_map('array_shift', $this->provideValidStateSets());
        $argumentSets = array();
        $validKeys = array(
            'state',
            'reason',
            'symbol',
            'timestamp',
        );
        foreach ($validKeys as $validKey) {
            $length = rand(1, 3);
            $logRecordIndex = rand(0, $length - 1);
            $log = array();
            for ($i = 0; $i < $length; $i++) {
                $log[] = array_fill_keys($validKeys, null);
            }
            unset($log[$logRecordIndex][$validKey]);
            $stateSetIndex = rand(0, sizeof($stateSets) - 1);
            $stateSet = $stateSets[$stateSetIndex];
            $argumentSets[] = array(
                'stateSet' => $stateSet,
                'log' => $log,
                'logRecordIndex' => $logRecordIndex,
            );
        }
        return $argumentSets;
    }

    /**
     * @dataProvider providLogsWithInvalidKeys
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 103
     */
    public function test_VerifyLog_LogWithInvalidKeys_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        //$this->_testLogValue($stateSet, $log, $logRecordIndex);
        try {
            $this->_fsm->verifyLog($stateSet, $log);
        } catch (InvalidArgumentException $e) {
            $this->assertInvalidValueArgumentExceptionMessage($e, 'log');
            $this->assertStringEndsWith("invalid keys at index $logRecordIndex", $e->getMessage());
            throw $e;
        }
    }

    protected function _provideLogsWithSpecificValues($key, $values)
    {
        $argumentSets = array();
        $templateArgumentSets = $this->provideValidLogs();
        foreach ($values as $value) {
            $templateArgumentSetIndex = rand(0, sizeof($templateArgumentSets) - 1);
            $argumentSet = $templateArgumentSets[$templateArgumentSetIndex];
            $log = &$argumentSet['log'];
            $logIndex = rand(0, sizeof($log) - 1);
            $log[$logIndex][$key] = $value;
            unset($log);
            $argumentSet['logRecordIndex'] = $logIndex;
            $argumentSets[] = $argumentSet;
        }
        return $argumentSets;
    }

    public function provideValidLogs()
    {
        $stateSet = array_shift(array_shift($this->provideValidStateSets()));
        return array(
            array(
                'stateSet' => $stateSet,
                'log' => array(),
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array(
                        'state' => 'INIT',
                        'reason' => 'init',
                        'symbol' => null,
                        'timestamp' => '1413277575.008993',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'action',
                        'symbol' => 'checkout',
                        'timestamp' => '1413277575.103009',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1413277576.001000',
                    ),
                ),
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array(
                        'state' => 'INIT',
                        'reason' => 'init',
                        'symbol' => null,
                        'timestamp' => '1413277575.008993',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'action',
                        'symbol' => 'checkout',
                        'timestamp' => '1413277575.103009',
                    ),
                    array(
                        'state' => 'PROCESSING',
                        'reason' => 'action',
                        'symbol' => 'processing',
                        'timestamp' => '1413277576.001000',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'reset',
                        'symbol' => null,
                        'timestamp' => '1413277577.002988',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'action',
                        'symbol' => 'checkout',
                        'timestamp' => '1413277577.002988',
                    ),
                    array(
                        'state' => 'PROCESSING',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1413277577.003988',
                    ),
                    array(
                        'state' => 'PROCESSING',
                        'reason' => 'wakeup',
                        'symbol' => null,
                        'timestamp' => '1413277577.003989',
                    ),
                    array(
                        'state' => 'PENDING',
                        'reason' => 'action',
                        'symbol' => 'pending',
                        'timestamp' => '1413277577.004002',
                    ),
                    array(
                        'state' => 'PENDING',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1413277578.980772',
                    ),
                ),
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_log_verification
     * @dataProvider provideValidLogs
     */
    public function test_VerifyLog_ValidArguments_ReturnsTrue($stateSet, $log)
    {
        $result = $this->_fsm->verifyLog($stateSet, $log);
        $this->assertTrue($result);
    }
}
