<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../VerifyLogTestCase.php')));

/**
 * public function test_VerifyLog_State_InvalidType_ThrowsException
 * public function test_VerifyLog_State_InvalidType_ThrowsException_CertainKeys
 * public function test_VerifyLog_State_InvalidValue_ThrowsException
 * public function test_VerifyLog_State_InvalidValue_ThrowsException_CertainKeys
 * public function test_VerifyLog_InitReasonWithNotInitState_ThrowsException
 * public function test_VerifyLog_InitReasonWithNotInitState_ThrowsException_CertainKeys
 * public function test_VerifyLog_ResetReasonWithNotInitState_ThrowsException
 * public function test_VerifyLog_ResetReasonWithNotInitState_ThrowsException_CertainKeys
 * public function test_VerifyLog_ActionReasonWithMismatchedState_ThrowsException
 * public function test_VerifyLog_ActionReasonWithMismatchedState_ThrowsException_CertainKeys
 * public function test_VerifyLog_ActionSleepWithStateFromNoPreviousRecord_ThrowsException
 * public function test_VerifyLog_ActionSleepWithStateFromNoPreviousRecord_ThrowsException_CertainKeys
 * public function test_VerifyLog_ActionWakeupWithStateFromNoPreviousRecord_ThrowsException
 * public function test_VerifyLog_ActionWakeupWithStateFromNoPreviousRecord_ThrowsException_CertainKeys
 */
class Fsm_VerifyLog_StateTest extends Fsm_VerifyLogTestCase
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

    public function provideLogsWithInvalidTypeState()
    {
        $stateSet = $this->_getBillingStateSet();
        return array(
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array(
                        'state' => 1,
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
                        'state' => 1.1,
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
                        'state' => array(),
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
                        'state' => new stdClass(),
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
                        'state' => null,
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
                ),
                'logRecordIndex' => 0,
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_state
     * @group issue1_type_and_value
     * @group issue22
     * @dataProvider provideLogsWithInvalidTypeState
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 611
     * @expectedExceptionMessageRegExp /^Argument \$log has invalid type: invalid type state at index \d+$/
     */
    public function test_VerifyLog_State_InvalidType_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_fsm->verifyLog($stateSet, $log);
    }

    /**
     * @group issue22
     * @dataProvider provideLogsWithInvalidTypeState
     */
    public function test_VerifyLog_State_InvalidType_ThrowsException_CertainKeys($stateSet, $log, $logRecordIndex)
    {
        $this->assertExceptionMessage($stateSet, $log, 'index', $logRecordIndex);
    }

    public function provideLogsWithInvalidValueState()
    {
        $stateSet = $this->_getBillingStateSet();
        return array(
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array(
                        'state' => 'SOME_RANDOM_STRING',
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
                        'state' => 'SOME_RANDOM_STRING',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000009',
                    ),
                ),
                'logRecordIndex' => 1,
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_state
     * @group issue1_type_and_value
     * @group issue22
     * @dataProvider provideLogsWithInvalidValueState
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 612
     * @expectedExceptionMessageRegExp /^Argument \$log has invalid value: invalid value state at index \d+$/
     */
    public function test_VerifyLog_State_InvalidValue_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_fsm->verifyLog($stateSet, $log);
    }

    /**
     * @group issue22
     * @dataProvider provideLogsWithInvalidValueState
     */
    public function test_VerifyLog_State_InvalidValue_ThrowsException_CertainKeys($stateSet, $log, $logRecordIndex)
    {
        $this->assertExceptionMessage($stateSet, $log, 'index', $logRecordIndex);
    }

    public function provideLogsWithInitReasonWithNotInitState()
    {
        $stateSet = $this->_getBillingStateSet();
        return array(
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'init',
                        'symbol' => null,
                        'timestamp' => '147.800800',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '147.800800',
                    ),
                ),
                'logRecordIndex' => 0,
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_state
     * @group issue22
     * @dataProvider provideLogsWithInitReasonWithNotInitState
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 601
     * @expectedExceptionMessageRegExp /^Argument \$log has invalid value: invalid value state in sequence at index \d+$/
     */
    public function test_VerifyLog_InitReasonWithNotInitState_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_fsm->verifyLog($stateSet, $log);
    }

    /**
     * @group issue22
     * @dataProvider provideLogsWithInitReasonWithNotInitState
     */
    public function test_VerifyLog_InitReasonWithNotInitState_ThrowsException_CertainKeys($stateSet, $log, $logRecordIndex)
    {
        $this->assertExceptionMessage($stateSet, $log, 'index', $logRecordIndex);
    }

    public function provideLogsWithResetReasonWithNotInitState()
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
                        'timestamp' => '147.800800',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'action',
                        'symbol' => 'checkout',
                        'timestamp' => '147.800801',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'reset',
                        'symbol' => null,
                        'timestamp' => '147.800802',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '148.800800',
                    ),
                ),
                'logRecordIndex' => 2,
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_state
     * @group issue22
     * @dataProvider provideLogsWithResetReasonWithNotInitState
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 602
     * @expectedExceptionMessageRegExp /^Argument \$log has invalid value: invalid value state in sequence at index \d+$/
     */
    public function test_VerifyLog_ResetReasonWithNotInitState_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_fsm->verifyLog($stateSet, $log);
    }

    /**
     * @group issue22
     * @dataProvider provideLogsWithResetReasonWithNotInitState
     */
    public function test_VerifyLog_ResetReasonWithNotInitState_ThrowsException_CertainKeys($stateSet, $log, $logRecordIndex)
    {
        $this->assertExceptionMessage($stateSet, $log, 'index', $logRecordIndex);
    }

    public function provideLogsWithActionReasonWithMismatchState()
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
                        'timestamp' => '147.800800',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'action',
                        'symbol' => 'checkout',
                        'timestamp' => '147.800801',
                    ),
                    array(
                        'state' => 'PENDING',
                        'reason' => 'action',
                        'symbol' => 'processing',
                        'timestamp' => '147.800802',
                    ),
                    array(
                        'state' => 'PENDING',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '148.800800',
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
                        'timestamp' => '147.800800',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'action',
                        'symbol' => 'checkout',
                        'timestamp' => '147.800801',
                    ),
                    array(
                        'state' => 'PROCESSING',
                        'reason' => 'action',
                        'symbol' => 'processing',
                        'timestamp' => '147.800802',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'action',
                        'symbol' => 'pending',
                        'timestamp' => '147.800802',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '148.800800',
                    ),
                ),
                'logRecordIndex' => 3,
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_state
     * @group issue22
     * @dataProvider provideLogsWithActionReasonWithMismatchState
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 603
     * @expectedExceptionMessageRegExp /^Argument \$log has invalid value: invalid value state in sequence at index \d+$/
     */
    public function test_VerifyLog_ActionReasonWithMismatchedState_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_fsm->verifyLog($stateSet, $log);
    }

    /**
     * @group issue22
     * @dataProvider provideLogsWithActionReasonWithMismatchState
     */
    public function test_VerifyLog_ActionReasonWithMismatchedState_ThrowsException_CertainKeys($stateSet, $log, $logRecordIndex)
    {
        $this->assertExceptionMessage($stateSet, $log, 'index', $logRecordIndex);
    }

    public function provideLogsWithSleepReasonWithStateFromNoPreviousRecord()
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
                        'timestamp' => '147.800800',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'action',
                        'symbol' => 'checkout',
                        'timestamp' => '147.800801',
                    ),
                    array(
                        'state' => 'PROCESSING',
                        'reason' => 'action',
                        'symbol' => 'processing',
                        'timestamp' => '147.800802',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '148.800800',
                    ),
                ),
                'logRecordIndex' => 3,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array(
                        'state' => 'INIT',
                        'reason' => 'init',
                        'symbol' => null,
                        'timestamp' => '147.800800',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'action',
                        'symbol' => 'checkout',
                        'timestamp' => '147.800801',
                    ),
                    array(
                        'state' => 'PROCESSING',
                        'reason' => 'action',
                        'symbol' => 'processing',
                        'timestamp' => '147.800802',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '147.800802',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'wakeup',
                        'symbol' => null,
                        'timestamp' => '147.800802',
                    ),
                    array(
                        'state' => 'PROCESSING',
                        'reason' => 'action',
                        'symbol' => 'processing',
                        'timestamp' => '147.800802',
                    ),
                    array(
                        'state' => 'PROCESSING',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '148.800800',
                    ),
                ),
                'logRecordIndex' => 3,
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_state
     * @group issue22
     * @dataProvider provideLogsWithSleepReasonWithStateFromNoPreviousRecord
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 604
     * @expectedExceptionMessageRegExp /^Argument \$log has invalid value: invalid value state in sequence at index \d+$/
     */
    public function test_VerifyLog_ActionSleepWithStateFromNoPreviousRecord_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_fsm->verifyLog($stateSet, $log);
    }

    /**
     * @group issue22
     * @dataProvider provideLogsWithSleepReasonWithStateFromNoPreviousRecord
     */
    public function test_VerifyLog_ActionSleepWithStateFromNoPreviousRecord_ThrowsException_CertainKeys($stateSet, $log, $logRecordIndex)
    {
        $this->assertExceptionMessage($stateSet, $log, 'index', $logRecordIndex);
    }

    public function provideLogsWithWakeupReasonWithStateFromNoPreviousRecord()
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
                        'timestamp' => '147.800800',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'action',
                        'symbol' => 'checkout',
                        'timestamp' => '147.800801',
                    ),
                    array(
                        'state' => 'PROCESSING',
                        'reason' => 'action',
                        'symbol' => 'processing',
                        'timestamp' => '147.800802',
                    ),
                    array(
                        'state' => 'PROCESSING',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '148.800800',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'wakeup',
                        'symbol' => null,
                        'timestamp' => '148.800801',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '148.800801',
                    ),
                ),
                'logRecordIndex' => 4,
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_state
     * @group issue22
     * @dataProvider provideLogsWithWakeupReasonWithStateFromNoPreviousRecord
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 605
     * @expectedMessageRegExp /^Argument \$log has invalid value: invalid value state in sequence at index \d+$/
     */
    public function test_VerifyLog_ActionWakeupWithStateFromNoPreviousRecord_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_fsm->verifyLog($stateSet, $log);
    }

    /**
     * @group issue22
     * @dataProvider provideLogsWithWakeupReasonWithStateFromNoPreviousRecord
     */
    public function test_VerifyLog_ActionWakeupWithStateFromNoPreviousRecord_ThrowsException_CertainKeys($stateSet, $log, $logRecordIndex)
    {
        $this->assertExceptionMessage($stateSet, $log, 'index', $logRecordIndex);
    }
}
