<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../VerifyLogTestCase.php')));

/**
 * public function test_VerifyLog_Reason_TheFirstPosition_NotInit_ThrowsException
 * public function test_VerifyLog_Reason_TheLastPosition_NotSleep_ThrowsException
 * public function test_VerifyLog_Reason_Init_NotAtTheFirstPosition_ThrowsException
 * public function test_VerifyLog_Reason_NotWakeup_AfterSleep_ThrowsException
 */
class Fsm_VerifyLog_ReasonTest extends Fsm_VerifyLogTestCase
{
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

    public function provideLogsWithNotInitFirstPosition()
    {
        $stateSet = array_shift(array_shift($this->provideValidStates()));
        return array(
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array(
                        'state' => 'INIT',
                        'reason' => 'action',
                        'symbol' => null,
                        'timestamp' => '1.000001',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'action',
                        'symbol' => 'checkout',
                        'timestamp' => '1.000002',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000003',
                    ),
                ),
                'logRecordIndex' => 0,
                'extra' => 'action',
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array(
                        'state' => 'INIT',
                        'reason' => 'reset',
                        'symbol' => null,
                        'timestamp' => '1.000001',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'action',
                        'symbol' => 'checkout',
                        'timestamp' => '1.000002',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000003',
                    ),
                ),
                'logRecordIndex' => 0,
                'extra' => 'action',
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array(
                        'state' => 'INIT',
                        'reason' => 'wakeup',
                        'symbol' => null,
                        'timestamp' => '1.000001',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'action',
                        'symbol' => 'checkout',
                        'timestamp' => '1.000002',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000003',
                    ),
                ),
                'logRecordIndex' => 0,
                'extra' => 'action',
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000001',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'action',
                        'symbol' => 'checkout',
                        'timestamp' => '1.000002',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000003',
                    ),
                ),
                'logRecordIndex' => 0,
                'extra' => 'action',
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_reason
     * @dataProvider provideLogsWithNotInitFirstPosition
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 501
     */
    public function test_VerifyLog_Reason_TheFirstPosition_NotInit_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_testLogType($stateSet, $log, $logRecordIndex, 'reason');
    }

    public function provideLogsWithNotSleepLastPosition()
    {
        $stateSet = array_shift(array_shift($this->provideValidStates()));
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
                        'state' => 'CHECKOUT',
                        'reason' => 'action',
                        'symbol' => 'checkout',
                        'timestamp' => '1.000002',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'init',
                        'symbol' => null,
                        'timestamp' => '1.000003',
                    ),
                ),
                'logRecordIndex' => 2,
                'extra' => 'init',
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
                        'state' => 'CHECKOUT',
                        'reason' => 'action',
                        'symbol' => 'checkout',
                        'timestamp' => '1.000002',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'action',
                        'symbol' => null,
                        'timestamp' => '1.000003',
                    ),
                ),
                'logRecordIndex' => 2,
                'extra' => 'action',
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
                        'state' => 'CHECKOUT',
                        'reason' => 'action',
                        'symbol' => 'checkout',
                        'timestamp' => '1.000002',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'reset',
                        'symbol' => null,
                        'timestamp' => '1.000003',
                    ),
                ),
                'logRecordIndex' => 2,
                'extra' => 'reset',
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
                        'state' => 'CHECKOUT',
                        'reason' => 'action',
                        'symbol' => 'checkout',
                        'timestamp' => '1.000002',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'wakeup',
                        'symbol' => null,
                        'timestamp' => '1.000003',
                    ),
                ),
                'logRecordIndex' => 2,
                'extra' => 'wakeup',
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_reason
     * @dataProvider provideLogsWithNotSleepLastPosition
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 502
     */
    public function test_VerifyLog_Reason_TheLastPosition_NotSleep_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_testLogType($stateSet, $log, $logRecordIndex, 'reason');
    }

    public function provideLogsWithInitNotAtFirstPosition()
    {
        $stateSet = array_shift(array_shift($this->provideValidStates()));
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
                        'state' => 'CHECKOUT',
                        'reason' => 'action',
                        'symbol' => 'checkout',
                        'timestamp' => '1.000002',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'init',
                        'symbol' => null,
                        'timestamp' => '1.000003',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000004',
                    ),
                ),
                'logRecordIndex' => 2,
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
                        'state' => 'CHECKOUT',
                        'reason' => 'init',
                        'symbol' => 'checkout',
                        'timestamp' => '1.000002',
                    ),
                    array(
                        'state' => 'PROCESSING',
                        'reason' => 'action',
                        'symbol' => 'processing',
                        'timestamp' => '1.000003',
                    ),
                    array(
                        'state' => 'PROCESSING',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000004',
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
                        'state' => 'CHECKOUT',
                        'reason' => 'init',
                        'symbol' => 'checkout',
                        'timestamp' => '1.000002',
                    ),
                    array(
                        'state' => 'PROCESSING',
                        'reason' => 'init',
                        'symbol' => 'processing',
                        'timestamp' => '1.000003',
                    ),
                    array(
                        'state' => 'PROCESSING',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000004',
                    ),
                ),
                'logRecordIndex' => 1,
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_reason
     * @dataProvider provideLogsWithInitNotAtFirstPosition
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 503
     */
    public function test_VerifyLog_Reason_Init_NotAtTheFirstPosition_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_testLogType($stateSet, $log, $logRecordIndex, 'reason');
    }

    public function provideLogsWithNotWakepAfterSleep()
    {
        $stateSet = array_shift(array_shift($this->provideValidStates()));
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
                        'state' => 'CHECKOUT',
                        'reason' => 'action',
                        'symbol' => 'checkout',
                        'timestamp' => '1.000002',
                    ),
                    array(
                        'state' => 'PROCESSING',
                        'reason' => 'action',
                        'symbol' => 'processing',
                        'timestamp' => '1.000003',
                    ),
                    array(
                        'state' => 'PROCESSING',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000004',
                    ),
                    array(
                        'state' => 'PENDING',
                        'reason' => 'action',
                        'symbol' => 'pending',
                        'timestamp' => '1.000005',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'wakeup',
                        'symbol' => null,
                        'timestamp' => '1.000006',
                    ),
                    array(
                        'state' => 'PENDING',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000007',
                    ),
                ),
                'logRecordIndex' => 4,
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_reason
     * @dataProvider provideLogsWithNotWakepAfterSleep
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 504
     */
    public function test_VerifyLog_Reason_NotWakeup_AfterSleep_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_testLogType($stateSet, $log, $logRecordIndex, 'reason');
    }
}
