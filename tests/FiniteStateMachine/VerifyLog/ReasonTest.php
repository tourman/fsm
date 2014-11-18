<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../VerifyLogTestCase.php')));

/**
 * public function test_VerifyLog_Reason_InvalidType_ThrowsException
 * public function test_VerifyLog_Reason_InvalidType_ThrowsException_CertainKeys
 * public function test_VerifyLog_Reason_InvalidValue_ThrowsException
 * public function test_VerifyLog_Reason_InvalidValue_ThrowsException_CertainKeys
 * public function test_VerifyLog_Reason_TheFirstPosition_NotInit_ThrowsException
 * public function test_VerifyLog_Reason_TheFirstPosition_NotInit_ThrowsException_CertainKeys
 * public function test_VerifyLog_Reason_TheLastPosition_NotSleep_ThrowsException
 * public function test_VerifyLog_Reason_Init_NotAtTheFirstPosition_ThrowsException
 * public function test_VerifyLog_Reason_NotWakeup_AfterSleep_ThrowsException
 */
class Fsm_VerifyLog_ReasonTest extends Fsm_VerifyLogTestCase
{
    protected $_exceptionMessage;

    public function setUp()
    {
        parent::setUp();
        $this->_exceptionMessage = null;
    }

    public function assertExceptionMessage($stateSet, $log, $key, $value)
    {
        if (is_null($this->_exceptionMessage)) {
            try {
                $this->_fsm->verifyLog($stateSet, $log);
                $this->_exceptionMessage = '';
            } catch (Exception $e) {
                $this->_exceptionMessage = $e->getMessage();
            }
        }
        $regExp = preg_quote("$key $value", '/');
        $this->assertRegExp("/$regExp/", $this->_exceptionMessage);
    }

    public function provideLogsWithInvalidTypeReason()
    {
        $stateSet = $this->_getBillingStateSet();
        return array(
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array(
                        'state' => 'INIT',
                        'reason' => 1,
                        'symbol' => null,
                        'timestamp' => '1.999887',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.999887',
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
                        'timestamp' => '1.999887',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 1.1,
                        'symbol' => null,
                        'timestamp' => '1.999887',
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
                        'timestamp' => '1.999887',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => array(),
                        'symbol' => null,
                        'timestamp' => '1.999887',
                    ),
                ),
                'logRecordIndex' => 1,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array(
                        'state' => 'INIT',
                        'reason' => new stdClass(),
                        'symbol' => null,
                        'timestamp' => '1.999887',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => new stdClass(),
                        'symbol' => null,
                        'timestamp' => '1.999887',
                    ),
                ),
                'logRecordIndex' => 0,
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_reason
     * @group issue1_type_and_value
     * @group issue22
     * @dataProvider provideLogsWithInvalidTypeReason
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 511
     * @expectedExceptionMessageRegExp /^Argument \$log has invalid type: invalid type reason at index \d+$/
     */
    public function test_VerifyLog_Reason_InvalidType_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_fsm->verifyLog($stateSet, $log);
    }

    /**
     * @group issue22
     * @dataProvider provideLogsWithInvalidTypeReason
     */
    public function test_VerifyLog_Reason_InvalidType_ThrowsException_CertainKeys($stateSet, $log, $logRecordIndex)
    {
        $this->assertExceptionMessage($stateSet, $log, 'index', $logRecordIndex);
    }

    public function provideLogsWithInvalidValueReason()
    {
        $stateSet = $this->_getBillingStateSet();
        return array(
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array(
                        'state' => 'INIT',
                        'reason' => 'someRandomString',
                        'symbol' => null,
                        'timestamp' => '1.999887',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.999887',
                    ),
                ),
                'logRecordIndex' => 0,
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_reason
     * @group issue1_type_and_value
     * @group issue22
     * @dataProvider provideLogsWithInvalidValueReason
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 512
     * @expectedExceptionMessageRegExp /^Argument \$log has invalid value: invalid value reason at index \d+$/
     */
    public function test_VerifyLog_Reason_InvalidValue_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_fsm->verifyLog($stateSet, $log);
    }

    /**
     * @group issue22
     * @dataProvider provideLogsWithInvalidValueReason
     */
    public function test_VerifyLog_Reason_InvalidValue_ThrowsException_CertainKeys($stateSet, $log, $logRecordIndex)
    {
        $this->assertExceptionMessage($stateSet, $log, 'index', $logRecordIndex);
    }

    public function provideLogsWithNotInitFirstPosition()
    {
        $stateSet = $this->_getBillingStateSet();
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
     * @group issue22
     * @dataProvider provideLogsWithNotInitFirstPosition
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 501
     * @expectedExceptionMessageRegExp /^Argument \$log has invalid value: invalid value reason in sequence at index \d+$/
     */
    public function test_VerifyLog_Reason_TheFirstPosition_NotInit_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_fsm->verifyLog($stateSet, $log);
    }

    /**
     * @group issue22
     * @dataProvider provideLogsWithNotInitFirstPosition
     */
    public function test_VerifyLog_Reason_TheFirstPosition_NotInit_ThrowsException_CertainKeys($stateSet, $log, $logRecordIndex)
    {
        $this->assertExceptionMessage($stateSet, $log, 'index', $logRecordIndex);
    }

    public function provideLogsWithNotSleepLastPosition()
    {
        $stateSet = $this->_getBillingStateSet();
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
        $this->_testLogValue($stateSet, $log, $logRecordIndex, true);
    }

    public function provideLogsWithInitNotAtFirstPosition()
    {
        $stateSet = $this->_getBillingStateSet();
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
        $this->_testLogValue($stateSet, $log, $logRecordIndex, true);
    }

    public function provideLogsWithNotWakepAfterSleep()
    {
        $stateSet = $this->_getBillingStateSet();
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
        $this->_testLogValue($stateSet, $log, $logRecordIndex, true);
    }
}
