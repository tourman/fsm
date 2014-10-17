<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../FsmTestCase.php')));

/**
 * public function test_VerifyLog_Default_CallsVerifyStateSet()
 * public function test_VerifyLog_InvalidTypeLog_ThrowsException()
 * public function test_VerifyLog_InvalidStructureLog_ThrowsException()
 * public function test_VerifyLog_InvalidLengthLog_ThrowsException()
 * public function test_VerifyLog_LogWithInvalidKeys_ThrowsException()
 * public function test_VerifyLog_LogWithInvalidTypeReason_ThrowsException()
 * public function test_VerifyLog_LogWithInvalidValueReason_ThrowsException()
 * public function test_VerifyLog_LogWithInvalidReasonSequence_ThrowsException()
 * public function test_VerifyLog_LogWithInvalidTypeSymbol_ThrowsException()
 * public function test_VerifyLog_LogWithInvalidValueSymbol_ThrowsException()
 * public function test_VerifyLog_LogWithInvalidSymbolSequence_ThrowsException()
 * public function test_VerifyLog_LogWithInvalidTypeTimestamp_ThrowsException()
 * public function test_VerifyLog_LogWithInvalidValueTimestamp_ThrowsException()
 * public function test_VerifyLog_LogWithInvalidTimestampSequence_ThrowsException()
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

    public function providLogsWithInvalidTypeReasons()
    {
        $reasons = array(
            false,
            1,
            1.1,
            array(),
            new stdClass(),
            null,
        );
        return $this->_provideLogsWithSpecificValues('reason', $reasons);
    }

    /**
     * @dataProvider providLogsWithInvalidTypeReasons
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 121
     */
    public function test_VerifyLog_LogWithInvalidTypeReason_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_testLogType($stateSet, $log, $logRecordIndex, 'reason');
    }

    public function providLogsWithInvalidValueReasons()
    {
        $reasons = array(
            '',
            'invalidReason',
        );
        return $this->_provideLogsWithSpecificValues('reason', $reasons);
    }

    /**
     * @dataProvider providLogsWithInvalidValueReasons
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 122
     */
    public function test_VerifyLog_LogWithInvalidValueReason_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_testLogValue($stateSet, $log, $logRecordIndex, 'reason');
    }

    public function providLogsWithInvalidReasonSequence()
    {
        $argumentSet = array();
        $stateSet = array_shift(array_shift($this->provideValidStateSets()));
        $logTemplate = $this->_generateLog($stateSet);
        $invalidReasonSequences = array(
//          array(array('init'),    null),
            array(array('action'),  0),
            array(array('reset'),   0),

            array(array('init',     'init'),        1),
//          array(array('init',     'action'),      null),
//          array(array('init',     'reset'),       null),
            array(array('action',   'init'),        0),
            array(array('action',   'action'),      0),
            array(array('action',   'reset'),       0),
            array(array('reset',    'init'),        0),
            array(array('reset',    'action'),      0),
            array(array('reset',    'reset'),       0),

            array(array('init',     'init',         'init'),        1),
            array(array('init',     'action',       'init'),        2),
            array(array('init',     'reset',        'init'),        2),
            array(array('action',   'init',         'init'),        0),
            array(array('action',   'action',       'init'),        0),
            array(array('action',   'reset',        'init'),        0),
            array(array('reset',    'init',         'init'),        0),
            array(array('reset',    'action',       'init'),        0),
            array(array('reset',    'reset',        'init'),        0),

            array(array('init',     'init',         'action'),      1),
//          array(array('init',     'action',       'action'),      null),
//          array(array('init',     'reset',        'action'),      null),
            array(array('action',   'init',         'action'),      0),
            array(array('action',   'action',       'action'),      0),
            array(array('action',   'reset',        'action'),      0),
            array(array('reset',    'init',         'action'),      0),
            array(array('reset',    'action',       'action'),      0),
            array(array('reset',    'reset',        'action'),      0),

            array(array('init',     'init',         'reset'),       1),
//          array(array('init',     'action',       'reset'),       null),
//          array(array('init',     'reset',        'reset'),       null),
            array(array('action',   'init',         'reset'),       0),
            array(array('action',   'action',       'reset'),       0),
            array(array('action',   'reset',        'reset'),       0),
            array(array('reset',    'init',         'reset'),       0),
            array(array('reset',    'action',       'reset'),       0),
            array(array('reset',    'reset',        'reset'),       0),
        );
        foreach ($invalidReasonSequences as $invalidReasonSequence) {
            $reasons = $invalidReasonSequence[0];
            $logRecordIndex = $invalidReasonSequence[1];
            $log = $logTemplate;
            foreach ($reasons as $i => $reason) {
                $log[$i]['reason'] = $reason;
            }
            $argumentSet[] = array(
                'stateSet' => $stateSet,
                'log' => $log,
                'logRecordIndex' => $logRecordIndex,
            );
        }
        return $argumentSet;
    }

    /**
     * @dataProvider providLogsWithInvalidReasonSequence
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 123
     */
    public function test_VerifyLog_LogWithInvalidReasonSequence_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_testLogSequence($stateSet, $log, $logRecordIndex, 'reason');
    }

    public function providLogsWithInvalidTypeSymbols()
    {
        $symbols = array(
            false,
            1,
            1.1,
            array(),
            new stdClass(),
        );
        return $this->_provideLogsWithSpecificValues('symbol', $symbols);
    }

    /**
     * @dataProvider providLogsWithInvalidTypeSymbols
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 131
     */
    public function test_VerifyLog_LogWithInvalidTypeSymbol_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_testLogType($stateSet, $log, $logRecordIndex, 'symbol');
    }

    public function providLogsWithInvalidValueSymbols()
    {
        $symbols = array(
            '',
            'invalidSymbol',
        );
        return $this->_provideLogsWithSpecificValues('symbol', $symbols);
    }

    /**
     * @dataProvider providLogsWithInvalidValueSymbols
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 132
     */
    public function test_VerifyLog_LogWithInvalidValueSymbol_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_testLogValue($stateSet, $log, $logRecordIndex, 'symbol');
    }

    public function providLogsWithInvalidSymbolSequence()
    {
        $argumentSet = array();
        $stateSet = array_shift(array_shift($this->provideValidStateSets()));
        //First key: null, absent, exists, allows (the last three are the strig type)
        $invalidLogTemplates = array();
        /*
        $validLogTemplates = array();
        */
        /*
        $validLogTemplates['null']['init'] = array(0,
            array(
                array('INIT',           'init',             null,           '17000.000001'),
            ),
        );
        */
        $invalidLogTemplates['null']['action'] = array(2,
            array(
                array('INIT',           'init',             null,           '17000.000001'),
                array('CHECKOUT',       'action',           'checkout',     '17001.000001'),
                array('PROCESSING',     'action',           null,           '17002.000001'),
            ),
        );
        /*
        $validLogTemplates['null']['reset'] = array(2,
            array(
                array('INIT',           'init',             null,           '17000.000001'),
                array('CHECKOUT',       'action',           'checkout',     '17001.000001'),
                array('INIT',           'reset',            null,           '17002.000001'),
            ),
        );
        */
        $invalidLogTemplates['absent']['init'] = array(0,
            array(
                array('INIT',           'init',             'failed',       '17000.000001'),
            ),
        );
        $invalidLogTemplates['absent']['action'] = array(1,
            array(
                array('INIT',           'init',             null,           '17000.000001'),
                array('CHECKOUT',       'action',           'failed',       '17001.000001'),
            ),
        );
        $invalidLogTemplates['absent']['reset'] = array(1,
            array(
                array('INIT',           'init',             null,           '17000.000001'),
                array('INIT',           'reset',            'failed',       '17001.000001'),
            ),
        );
        //$invalidLogTemplates['exists']['init'] //senseless
        $invalidLogTemplates['exists']['action'] = array(3,
            array(
                array('INIT',           'init',             null,           '17000.000001'),
                array('CHECKOUT',       'action',           'checkout',     '17001.000001'),
                array('PROCESSING',     'action',           'processing',   '17002.000001'),
                array('FAILED',         'action',           'void',         '17003.000001'),
            ),
        );
        $invalidLogTemplates['exists']['reset'] = array(3,
            array(
                array('INIT',           'init',             null,           '17000.000001'),
                array('CHECKOUT',       'action',           'checkout',     '17001.000001'),
                array('INIT',           'reset',            null,           '17002.000001'),
                array('INIT',           'reset',            'checkout',     '17003.000001'),
            ),
        );
        //$invalidLogTemplates['allows']['init'] //senseless
        /*
        $validLogTemplates['allows']['action'] = array(1,
            array(
                array('INIT',           'init',             null,           '17000.000001'),
                array('CHECKOUT',       'action',           'checkout',     '17001.000001'),
            ),
        );
        */
        $invalidLogTemplates['allows']['reset'] = array(3,
            array(
                array('INIT',           'init',             null,           '17000.000001'),
                array('CHECKOUT',       'action',           'checkout',     '17001.000001'),
                array('INIT',           'reset',            null,           '17002.000001'),
                array('INIT',           'reset',            '*',            '17003.000001'),
            ),
        );
        //Null: symbol is null. Absent: symbol is absent for the current symbol set. Exists: symbol exists in the current set but not allows transition. Allows: symbol exists in the set and allows transition.
        $symbolModeSet = array('null', 'absent', 'exists', 'allows');
        $reasonSet = array('init', 'action', 'reset');
        foreach ($symbolModeSet as $symbolMode) {
            foreach ($reasonSet as $reason) {
                if (!isset($invalidLogTemplates[$symbolMode][$reason])) {
                    continue;
                }
                $log = $invalidLogTemplates[$symbolMode][$reason][1];
                foreach ($log as &$logRecord) {
                    $logRecord = array(
                        'state'     => $logRecord[0],
                        'reason'    => $logRecord[1],
                        'symbol'    => $logRecord[2],
                        'timestamp' => $logRecord[3],
                    );
                }
                unset($logRecord);
                $argumentSet[] = array(
                    'stateSet'          => $stateSet,
                    'log'               => $log,
                    'logRecordIndex'    => $invalidLogTemplates[$symbolMode][$reason][0],
                    'stateConditions'   => array(
                        'symbolMode'        => $symbolMode,
                        'reason'            => $reason,
                    ),
                );
            }
        }
        return $argumentSet;
    }

    /**
     * @dataProvider providLogsWithInvalidSymbolSequence
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 133
     */
    public function test_VerifyLog_LogWithInvalidSymbolSequence_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_testLogSequence($stateSet, $log, $logRecordIndex, 'symbol');
    }

    public function providLogsWithInvalidTypeTimestamps()
    {
        $timestamps = array(
            false,
            1,
            1.1,
            array(),
            new stdClass(),
            null,
        );
        return $this->_provideLogsWithSpecificValues('timestamp', $timestamps);
    }

    /**
     * @dataProvider providLogsWithInvalidTypeTimestamps
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 141
     */
    public function test_VerifyLog_LogWithInvalidTypeTimestamp_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_testLogType($stateSet, $log, $logRecordIndex, 'timestamp');
    }

    public function providLogsWithInvalidValueTimestamps()
    {
        $timestamps = array(
            '',
            'invalidTimestamp',
            '.1',
            '0.1',
            '1.1',
            '1.12312',
            '1.1231231',
        );
        return $this->_provideLogsWithSpecificValues('timestamp', $timestamps);
    }

    /**
     * @dataProvider providLogsWithInvalidValueTimestamps
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 142
     */
    public function test_VerifyLog_LogWithInvalidValueTimestamp_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_testLogValue($stateSet, $log, $logRecordIndex, 'timestamp');
    }

    public function providLogsWithInvalidTimestampSequence()
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
                        'timestamp' => '17000.000001',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'action',
                        'symbol' => 'checkout',
                        'timestamp' => '16999.000000',
                    ),
                    array(
                        'state' => 'FAILED',
                        'reason' => 'action',
                        'symbol' => 'failed',
                        'timestamp' => '17002.000001',
                    ),
                ),
                'logRecordIndex' => 1,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array(
                        'state' => 'INIT',
                        'reason' => 'init',
                        'symbol' => null,
                        'timestamp' => '17000.000001',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'action',
                        'symbol' => 'checkout',
                        'timestamp' => '17000.000001',
                    ),
                    array(
                        'state' => 'FAILED',
                        'reason' => 'action',
                        'symbol' => 'failed',
                        'timestamp' => '17000.000000',
                    ),
                ),
                'logRecordIndex' => 2,
            ),
        );
    }

    /**
     * @dataProvider providLogsWithInvalidTimestampSequence
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 143
     */
    public function test_VerifyLog_LogWithInvalidTimestampSequence_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_testLogSequence($stateSet, $log, $logRecordIndex, 'timestamp');
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
