<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../VerifyLogTestCase.php')));

/**
 * public function test_VerifyLog_InitReasonWithNotInitState_ThrowsException
 * public function test_VerifyLog_ResetReasonWithNotInitState_ThrowsException
 * public function test_VerifyLog_ActionReasonWithMismatchedState_ThrowsException
 * public function test_VerifyLog_ActionSleepWithStateFromNoPreviousRecord_ThrowsException
 * public function test_VerifyLog_ActionWakeupWithStateFromNoPreviousRecord_ThrowsException
 */
class Fsm_VerifyLog_StateTest extends Fsm_VerifyLogTestCase
{
    protected function _testLogType($stateSet, $log, $logRecordIndex = null, $variable = null)
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

    public function provideLogsWithInitReasonWithNotInitState()
    {
        $stateSet = array_shift(array_shift($this->provideValidStateSets()));
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
        $this->_testLogType($stateSet, $log, $logRecordIndex, 'state');
    }
}
