<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../VerifyLogTestCase.php')));

/**
 * public function test_VerifyLog_Symbol_InvalidType_ThrowsException
 * public function test_VerifyLog_Symbol_InvalidValue_ThrowsException
 * public function test_VerifyLog_InitReasonWithNotEmptySymbol_ThrowsException
 * public function test_VerifyLog_ResetReasonWithNotEmptySymbol_ThrowsException
 * public function test_VerifyLog_ActionReasonWithEmptySymbol_ThrowsException
 * public function test_VerifyLog_ActionReasonWithMismatchSymbol_ThrowsException
 * public function test_VerifyLog_SleepReasonWithNotEmptySymbol_ThrowsException
 * public function test_VerifyLog_SleepReasonWithNotEmptySymbol_ThrowsException
 */
class Fsm_VerifyLog_SymbolTest extends Fsm_VerifyLogTestCase
{
    protected function _testLogType($stateSet, $log, $logRecordIndex, $sequence)
    {
        $sequence = $sequence ? 'in sequence ' : '';
        try {
            $this->_fsm->verifyLog($stateSet, $log);
        } catch (InvalidArgumentException $e) {
            $this->assertInvalidTypeArgumentExceptionMessage($e, 'log');
            $this->assertStringEndsWith("invalid type symbol {$sequence}at index $logRecordIndex", $e->getMessage());
            throw $e;
        }
    }

    protected function _testLogValue($stateSet, $log, $logRecordIndex, $sequence)
    {
        $sequence = $sequence ? 'in sequence ' : '';
        try {
            $this->_fsm->verifyLog($stateSet, $log);
        } catch (InvalidArgumentException $e) {
            $this->assertInvalidValueArgumentExceptionMessage($e, 'log');
            $this->assertStringEndsWith("invalid value symbol {$sequence}at index $logRecordIndex", $e->getMessage());
            throw $e;
        }
    }


    public function provideLogsWithInitInvalidTypeSymbol()
    {
        $stateSet = array_shift(array_shift($this->provideValidStateSets()));
        return array(
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array(
                        'state' => 'INIT',
                        'reason' => 'init',
                        'symbol' => 1,
                        'timestamp' => '1.000001',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000002',
                    ),
                ),
                'logRecordIndex' => 0,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array(
                        'state' => 'INIT',
                        'reason' => 'init',
                        'symbol' => null,
                        'timestamp' => '1.000001',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => 1.1,
                        'timestamp' => '1.000002',
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
                        'symbol' => array(),
                        'timestamp' => '1.000001',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000002',
                    ),
                ),
                'logRecordIndex' => 0,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array(
                        'state' => 'INIT',
                        'reason' => 'init',
                        'symbol' => new stdClass(),
                        'timestamp' => '1.000001',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000002',
                    ),
                ),
                'logRecordIndex' => 0,
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_symbol
     * @group issue1_type_and_value
     * @dataProvider provideLogsWithInitInvalidTypeSymbol
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 711
     */
    public function test_VerifyLog_Symbol_InvalidType_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_testLogType($stateSet, $log, $logRecordIndex, false);
    }

    public function provideLogsWithInitReasonWithNotEmptySymbol()
    {
        $stateSet = array_shift(array_shift($this->provideValidStateSets()));
        return array(
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array(
                        'state' => 'INIT',
                        'reason' => 'init',
                        'symbol' => 'init',
                        'timestamp' => '1.000001',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000002',
                    ),
                ),
                'logRecordIndex' => 0,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array(
                        'state' => 'INIT',
                        'reason' => 'init',
                        'symbol' => 1,
                        'timestamp' => '1.000001',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000002',
                    ),
                ),
                'logRecordIndex' => 0,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array(
                        'state' => 'INIT',
                        'reason' => 'init',
                        'symbol' => 1.1,
                        'timestamp' => '1.000001',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000002',
                    ),
                ),
                'logRecordIndex' => 0,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array(
                        'state' => 'INIT',
                        'reason' => 'init',
                        'symbol' => array(),
                        'timestamp' => '1.000001',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000002',
                    ),
                ),
                'logRecordIndex' => 0,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array(
                        'state' => 'INIT',
                        'reason' => 'init',
                        'symbol' => new stdClass(),
                        'timestamp' => '1.000001',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000002',
                    ),
                ),
                'logRecordIndex' => 0,
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_symbol
     * @dataProvider provideLogsWithInitReasonWithNotEmptySymbol
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 701
     */
    public function test_VerifyLog_InitReasonWithNotEmptySymbol_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_testLogType($stateSet, $log, $logRecordIndex, 'symbol');
    }

    public function provideLogsWithResetReasonWithNotEmptySymbol()
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
                        'timestamp' => '1.000001',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'reset',
                        'symbol' => 'reset',
                        'timestamp' => '1.000001',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000002',
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
                        'timestamp' => '1.000001',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'reset',
                        'symbol' => 1,
                        'timestamp' => '1.000001',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000002',
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
                        'timestamp' => '1.000001',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'reset',
                        'symbol' => 1.1,
                        'timestamp' => '1.000001',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000002',
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
                        'timestamp' => '1.000001',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'reset',
                        'symbol' => array(),
                        'timestamp' => '1.000001',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000002',
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
                        'timestamp' => '1.000001',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'reset',
                        'symbol' => new stdClass(),
                        'timestamp' => '1.000001',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000002',
                    ),
                ),
                'logRecordIndex' => 1,
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_symbol
     * @dataProvider provideLogsWithResetReasonWithNotEmptySymbol
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 702
     */
    public function test_VerifyLog_ResetReasonWithNotEmptySymbol_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_testLogType($stateSet, $log, $logRecordIndex, 'symbol');
    }
}
