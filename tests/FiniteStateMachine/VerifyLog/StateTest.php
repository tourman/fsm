<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../VerifyLogTestCase.php')));

/**
 * public function test_VerifyLog_State_InvalidType_ThrowsException
 * public function test_VerifyLog_State_InvalidValue_ThrowsException
 * public function test_VerifyLog_InitReasonWithNotInitState_ThrowsException
 * public function test_VerifyLog_ResetReasonWithNotInitState_ThrowsException
 * public function test_VerifyLog_ActionReasonWithMismatchedState_ThrowsException
 * public function test_VerifyLog_ActionSleepWithStateFromNoPreviousRecord_ThrowsException
 * public function test_VerifyLog_ActionWakeupWithStateFromNoPreviousRecord_ThrowsException
 */
class Fsm_VerifyLog_StateTest extends Fsm_VerifyLogTestCase
{
    protected function _testLogType($stateSet, $log, $logRecordIndex, $sequence)
    {
        $sequence = $sequence ? 'in sequence ' : '';
        try {
            $this->_fsm->verifyLog($stateSet, $log);
        } catch (InvalidArgumentException $e) {
            $this->assertInvalidTypeArgumentExceptionMessage($e, 'log');
            $this->assertStringEndsWith("invalid type state {$sequence}at index $logRecordIndex", $e->getMessage());
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
            $this->assertStringEndsWith("invalid value state {$sequence}at index $logRecordIndex", $e->getMessage());
            throw $e;
        }
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
     * @dataProvider provideLogsWithInvalidTypeState
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 611
     */
    public function test_VerifyLog_State_InvalidType_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_testLogType($stateSet, $log, $logRecordIndex, false);
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
     * @dataProvider provideLogsWithInvalidValueState
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 612
     */
    public function test_VerifyLog_State_InvalidValue_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_testLogValue($stateSet, $log, $logRecordIndex, false);
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
     * @dataProvider provideLogsWithInitReasonWithNotInitState
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 601
     */
    public function test_VerifyLog_InitReasonWithNotInitState_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_testLogValue($stateSet, $log, $logRecordIndex, true);
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
     * @dataProvider provideLogsWithResetReasonWithNotInitState
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 602
     */
    public function test_VerifyLog_ResetReasonWithNotInitState_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_testLogValue($stateSet, $log, $logRecordIndex, true);
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
     * @dataProvider provideLogsWithActionReasonWithMismatchState
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 603
     */
    public function test_VerifyLog_ActionReasonWithMismatchedState_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_testLogValue($stateSet, $log, $logRecordIndex, true);
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
     * @dataProvider provideLogsWithSleepReasonWithStateFromNoPreviousRecord
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 604
     */
    public function test_VerifyLog_ActionSleepWithStateFromNoPreviousRecord_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_testLogValue($stateSet, $log, $logRecordIndex, true);
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
     * @dataProvider provideLogsWithWakeupReasonWithStateFromNoPreviousRecord
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 605
     */
    public function test_VerifyLog_ActionWakeupWithStateFromNoPreviousRecord_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_testLogValue($stateSet, $log, $logRecordIndex, true);
    }
}