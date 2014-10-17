<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../VerifyLogTestCase.php')));

/**
 * public function test_VerifyLog_Timestamp_InvalidType_ThrowsException
 * public function test_VerifyLog_Timestamp_InvalidValue_ThrowsException
 * public function test_VerifyLog_Timestamp_InvalidSequence_ThrowsException
 */
class Fsm_VerifyLog_TimestampTest extends Fsm_VerifyLogTestCase
{
    protected function _testLogType($stateSet, $log, $logRecordIndex, $sequence)
    {
        $sequence = $sequence ? 'in sequence ' : '';
        try {
            $this->_fsm->verifyLog($stateSet, $log);
        } catch (InvalidArgumentException $e) {
            $this->assertInvalidTypeArgumentExceptionMessage($e, 'log');
            $this->assertStringEndsWith("invalid type timestamp {$sequence}at index $logRecordIndex", $e->getMessage());
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
            $this->assertStringEndsWith("invalid value timestamp {$sequence}at index $logRecordIndex", $e->getMessage());
            throw $e;
        }
    }

    public function provideLogsWithInvalidTypeTimestamp()
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
                        'timestamp' => 1,
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000009',
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
                        'timestamp' => '1.000007',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => 1.1,
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
                        'timestamp' => array(),
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000009',
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
                        'timestamp' => '1.000007',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => false,
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
                        'timestamp' => new stdClass(),
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000009',
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
                        'timestamp' => '1.000007',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => null,
                    ),
                ),
                'logRecordIndex' => 1,
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_timestamp
     * @group issue1_type_and_value
     * @dataProvider provideLogsWithInvalidTypeTimestamp
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 811
     */
    public function test_VerifyLog_Timestamp_InvalidType_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_testLogType($stateSet, $log, $logRecordIndex, false);
    }

    public function provideLogsWithInvalidValueTimestamp()
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
                        'timestamp' => '',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000009',
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
                        'timestamp' => '1',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000009',
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
                        'timestamp' => '1.000009',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '01.000001',
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
                        'timestamp' => '1.000009',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.1',
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
                        'timestamp' => '-1.000009',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.1',
                    ),
                ),
                'logRecordIndex' => 0,
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_timestamp
     * @group issue1_type_and_value
     * @dataProvider provideLogsWithInvalidValueTimestamp
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 812
     */
    public function test_VerifyLog_Timestamp_InvalidValue_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_testLogValue($stateSet, $log, $logRecordIndex, false);
    }

    public function provideLogsWithInvalidSequenceTimestamp()
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
                        'timestamp' => '2.000009',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000009',
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
                        'timestamp' => '1.000009',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000009',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'wakeup',
                        'symbol' => null,
                        'timestamp' => '1.000009',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000008',
                    ),
                ),
                'logRecordIndex' => 3,
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_timestamp
     * @dataProvider provideLogsWithInvalidSequenceTimestamp
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 813
     */
    public function test_VerifyLog_Timestamp_InvalidSequence_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_testLogValue($stateSet, $log, $logRecordIndex, true);
    }
}
