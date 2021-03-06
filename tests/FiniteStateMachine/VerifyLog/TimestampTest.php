<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../VerifyLogTestCase.php')));

/**
 * public function test_VerifyLog_Timestamp_InvalidType_ThrowsException
 * public function test_VerifyLog_Timestamp_InvalidType_ThrowsException_CertainKeys
 * public function test_VerifyLog_Timestamp_InvalidValue_ThrowsException
 * public function test_VerifyLog_Timestamp_InvalidValue_ThrowsException_CertainKeys
 * public function test_VerifyLog_Timestamp_InvalidSequence_ThrowsException
 * public function test_VerifyLog_Timestamp_InvalidSequence_ThrowsException_CertainKeys
 */
class Fsm_VerifyLog_TimestampTest extends Fsm_VerifyLogTestCase
{
    public function provideLogsWithInvalidTypeTimestamp()
    {
        $stateSet = $this->_getBillingStateSet();
        return array(
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array('state' => 'INIT', 'reason' => 'init',  'symbol' => null, 'timestamp' => 1),
                    array('state' => 'INIT', 'reason' => 'sleep', 'symbol' => null, 'timestamp' => '1.000009'),
                ),
                'logRecordIndex' => 0,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array('state' => 'INIT', 'reason' => 'init',  'symbol' => null, 'timestamp' => '1.000007'),
                    array('state' => 'INIT', 'reason' => 'sleep', 'symbol' => null, 'timestamp' => 1.1),
                ),
                'logRecordIndex' => 1,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array('state' => 'INIT', 'reason' => 'init',  'symbol' => null, 'timestamp' => array()),
                    array('state' => 'INIT', 'reason' => 'sleep', 'symbol' => null, 'timestamp' => '1.000009'),
                ),
                'logRecordIndex' => 0,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array('state' => 'INIT', 'reason' => 'init',  'symbol' => null, 'timestamp' => '1.000007'),
                    array('state' => 'INIT', 'reason' => 'sleep', 'symbol' => null, 'timestamp' => false),
                ),
                'logRecordIndex' => 1,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array('state' => 'INIT', 'reason' => 'init',  'symbol' => null, 'timestamp' => new stdClass()),
                    array('state' => 'INIT', 'reason' => 'sleep', 'symbol' => null, 'timestamp' => '1.000009'),
                ),
                'logRecordIndex' => 0,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array('state' => 'INIT', 'reason' => 'init',  'symbol' => null, 'timestamp' => '1.000007'),
                    array('state' => 'INIT', 'reason' => 'sleep', 'symbol' => null, 'timestamp' => null),
                ),
                'logRecordIndex' => 1,
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_timestamp
     * @group issue1_type_and_value
     * @group issue22
     * @dataProvider provideLogsWithInvalidTypeTimestamp
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 811
     * @expectedExceptionMessageRegExp /^Argument \$log has invalid type: invalid type timestamp at index \d+$/
     */
    public function test_VerifyLog_Timestamp_InvalidType_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_fsm->verifyLog($stateSet, $log);
    }

    /**
     * @group issue22
     * @dataProvider provideLogsWithInvalidTypeTimestamp
     */
    public function test_VerifyLog_Timestamp_InvalidType_ThrowsException_CertainKeys($stateSet, $log, $logRecordIndex)
    {
        $this->assertExceptionMessage($stateSet, $log, 'index', $logRecordIndex);
    }

    public function provideLogsWithInvalidValueTimestamp()
    {
        $stateSet = $this->_getBillingStateSet();
        return array(
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array('state' => 'INIT', 'reason' => 'init',  'symbol' => null, 'timestamp' => ''),
                    array('state' => 'INIT', 'reason' => 'sleep', 'symbol' => null, 'timestamp' => '1.000009'),
                ),
                'logRecordIndex' => 0,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array('state' => 'INIT', 'reason' => 'init',  'symbol' => null, 'timestamp' => '1'),
                    array('state' => 'INIT', 'reason' => 'sleep', 'symbol' => null, 'timestamp' => '1.000009'),
                ),
                'logRecordIndex' => 0,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array('state' => 'INIT', 'reason' => 'init',  'symbol' => null, 'timestamp' => '1.000009'),
                    array('state' => 'INIT', 'reason' => 'sleep', 'symbol' => null, 'timestamp' => '01.000001'),
                ),
                'logRecordIndex' => 1,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array('state' => 'INIT', 'reason' => 'init',  'symbol' => null, 'timestamp' => '1.000009'),
                    array('state' => 'INIT', 'reason' => 'sleep', 'symbol' => null, 'timestamp' => '1.1'),
                ),
                'logRecordIndex' => 1,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array('state' => 'INIT', 'reason' => 'init',  'symbol' => null, 'timestamp' => '-1.000009'),
                    array('state' => 'INIT', 'reason' => 'sleep', 'symbol' => null, 'timestamp' => '1.1'),
                ),
                'logRecordIndex' => 0,
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_timestamp
     * @group issue1_type_and_value
     * @group issue22
     * @dataProvider provideLogsWithInvalidValueTimestamp
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 812
     * @expectedExceptionMessageRegExp /^Argument \$log has invalid value: invalid value timestamp at index \d+$/
     */
    public function test_VerifyLog_Timestamp_InvalidValue_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_fsm->verifyLog($stateSet, $log);
    }

    /**
     * @group issue22
     * @dataProvider provideLogsWithInvalidValueTimestamp
     */
    public function test_VerifyLog_Timestamp_InvalidValue_ThrowsException_CertainKeys($stateSet, $log, $logRecordIndex)
    {
        $this->assertExceptionMessage($stateSet, $log, 'index', $logRecordIndex);
    }

    public function provideLogsWithInvalidSequenceTimestamp()
    {
        $stateSet = $this->_getBillingStateSet();
        return array(
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array('state' => 'INIT', 'reason' => 'init',   'symbol' => null, 'timestamp' => '2.000009'),
                    array('state' => 'INIT', 'reason' => 'sleep',  'symbol' => null, 'timestamp' => '1.000009'),
                ),
                'logRecordIndex' => 1,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array('state' => 'INIT', 'reason' => 'init',   'symbol' => null, 'timestamp' => '1.000009'),
                    array('state' => 'INIT', 'reason' => 'sleep',  'symbol' => null, 'timestamp' => '1.000009'),
                    array('state' => 'INIT', 'reason' => 'wakeup', 'symbol' => null, 'timestamp' => '1.000009'),
                    array('state' => 'INIT', 'reason' => 'sleep',  'symbol' => null, 'timestamp' => '1.000008'),
                ),
                'logRecordIndex' => 3,
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_timestamp
     * @group issue22
     * @dataProvider provideLogsWithInvalidSequenceTimestamp
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 813
     * @expectedExceptionMessageRegExp /^Argument \$log has invalid value: invalid value timestamp in sequence at index \d+$/
     */
    public function test_VerifyLog_Timestamp_InvalidSequence_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_fsm->verifyLog($stateSet, $log);
    }

    /**
     * @group issue22
     * @dataProvider provideLogsWithInvalidSequenceTimestamp
     */
    public function test_VerifyLog_Timestamp_InvalidSequence_ThrowsException_CertainKeys($stateSet, $log, $logRecordIndex)
    {
        $this->assertExceptionMessage($stateSet, $log, 'index', $logRecordIndex);
    }
}
