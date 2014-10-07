<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../ReasonTestCase.php')));

/**
 * public function test_VerifyLog_Reason_Init_MiddlePosition_ThrowsException
 * public function test_VerifyLog_Reason_Init_LastPosition_ThrowsException
 */
class Fsm_VerifyLog_Reason_InitTest extends Fsm_VerifyLog_ReasonTestCase
{
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

    public function provideLogsWithInitReasonInTheMiddlePosition()
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
                        'timestamp' => '12.000000',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'init',
                        'symbol' => null,
                        'timestamp' => '12.000001',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '12.000002',
                    ),
                ),
                'logRecordIndex' => 1,
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_reason
     * @dataProvider provideLogsWithInitReasonInTheMiddlePosition
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 501
     */
    public function test_VerifyLog_Reason_Init_MiddlePosition_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_testLogSequence($stateSet, $log, $logRecordIndex, 'reason');
    }

    public function provideLogsWithInitReasonInTheLastPosition()
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
                        'timestamp' => '12.000000',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'action',
                        'symbol' => 'checkout',
                        'timestamp' => '12.000001',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'init',
                        'symbol' => null,
                        'timestamp' => '12.000002',
                    ),
                ),
                'logRecordIndex' => 2,
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_reason
     * @dataProvider provideLogsWithInitReasonInTheLastPosition
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 502
     */
    public function test_VerifyLog_Reason_Init_LastPosition_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_testLogSequence($stateSet, $log, $logRecordIndex, 'reason');
    }
}
