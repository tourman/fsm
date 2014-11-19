<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../VerifyLogTestCase.php')));

/**
 * public function test_VerifyLog_Symbol_InvalidType_ThrowsException
 * public function test_VerifyLog_Symbol_InvalidType_ThrowsException_CertainKeys
 * public function test_VerifyLog_Symbol_InvalidValue_ThrowsException
 * public function test_VerifyLog_Symbol_InvalidValue_ThrowsException_CertainKeys
 * public function test_VerifyLog_InitReasonWithNotEmptySymbol_ThrowsException
 * public function test_VerifyLog_InitReasonWithNotEmptySymbol_ThrowsException_CertainKeys
 * public function test_VerifyLog_ResetReasonWithNotEmptySymbol_ThrowsException
 * public function test_VerifyLog_ResetReasonWithNotEmptySymbol_ThrowsException_CertainKeys
 * public function test_VerifyLog_ActionReasonWithEmptySymbol_ThrowsException
 * public function test_VerifyLog_ActionReasonWithEmptySymbol_ThrowsException_CertainKeys
 * public function test_VerifyLog_ActionReasonWithAbsentSymbol_ThrowsException
 * public function test_VerifyLog_ActionReasonWithAbsentSymbol_ThrowsException_CertainKeys
 * public function test_VerifyLog_ActionReasonWithMismatchSymbol_ThrowsException
 * public function test_VerifyLog_ActionReasonWithMismatchSymbol_ThrowsException_CertainKeys
 * public function test_VerifyLog_SleepReasonWithNotEmptySymbol_ThrowsException
 * public function test_VerifyLog_SleepReasonWithNotEmptySymbol_ThrowsException_CertainKeys
 * public function test_VerifyLog_WakeupReasonWithNotEmptySymbol_ThrowsException
 * public function test_VerifyLog_WakeupReasonWithNotEmptySymbol_ThrowsException_CertainKeys
 */
class Fsm_VerifyLog_SymbolTest extends Fsm_VerifyLogTestCase
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

    public function provideLogsWithInitInvalidTypeSymbol()
    {
        $stateSet = $this->_getBillingStateSet();
        return array(
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array('state' => 'INIT', 'reason' => 'init',  'symbol' => 1,              'timestamp' => '1.000001'),
                    array('state' => 'INIT', 'reason' => 'sleep', 'symbol' => null,           'timestamp' => '1.000002'),
                ),
                'logRecordIndex' => 0,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array('state' => 'INIT', 'reason' => 'init',  'symbol' => null,           'timestamp' => '1.000001'),
                    array('state' => 'INIT', 'reason' => 'sleep', 'symbol' => 1.1,            'timestamp' => '1.000002'),
                ),
                'logRecordIndex' => 1,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array('state' => 'INIT', 'reason' => 'init',  'symbol' => array(),        'timestamp' => '1.000001'),
                    array('state' => 'INIT', 'reason' => 'sleep', 'symbol' => null,           'timestamp' => '1.000002'),
                ),
                'logRecordIndex' => 0,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array('state' => 'INIT', 'reason' => 'init',  'symbol' => new stdClass(), 'timestamp' => '1.000001'),
                    array('state' => 'INIT', 'reason' => 'sleep', 'symbol' => null,           'timestamp' => '1.000002'),
                ),
                'logRecordIndex' => 0,
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_symbol
     * @group issue1_type_and_value
     * @group issue22
     * @dataProvider provideLogsWithInitInvalidTypeSymbol
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 711
     * @expectedExceptionMessageRegExp /^Argument \$log has invalid type: invalid type symbol at index \d+$/
     */
    public function test_VerifyLog_Symbol_InvalidType_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_fsm->verifyLog($stateSet, $log);
    }

    /**
     * @group issue22
     * @dataProvider provideLogsWithInitInvalidTypeSymbol
     */
    public function test_VerifyLog_Symbol_InvalidType_ThrowsException_CertainKeys($stateSet, $log, $logRecordIndex)
    {
        $this->assertExceptionMessage($stateSet, $log, 'index', $logRecordIndex);
    }

    public function provideLogsWithInitInvalidValueSymbol()
    {
        $stateSet = $this->_getBillingStateSet();
        return array(
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array('state' => 'INIT',     'reason' => 'init',   'symbol' => null,               'timestamp' => '1.000001'),
                    array('state' => 'CHECKOUT', 'reason' => 'action', 'symbol' => 'someRandomSymbol', 'timestamp' => '1.000002'),
                    array('state' => 'CHECKOUT', 'reason' => 'sleep',  'symbol' => null,               'timestamp' => '1.000002'),
                ),
                'logRecordIndex' => 1,
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_symbol
     * @group issue1_type_and_value
     * @group issue22
     * @dataProvider provideLogsWithInitInvalidValueSymbol
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 712
     * @expectedExceptionMessageRegExp /^Argument \$log has invalid value: invalid value symbol at index \d+$/
     */
    public function test_VerifyLog_Symbol_InvalidValue_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_fsm->verifyLog($stateSet, $log);
    }

    /**
     * @group issue22
     * @dataProvider provideLogsWithInitInvalidValueSymbol
     */
    public function test_VerifyLog_Symbol_InvalidValue_ThrowsException_CertainKeys($stateSet, $log, $logRecordIndex)
    {
        $this->assertExceptionMessage($stateSet, $log, 'index', $logRecordIndex);
    }

    public function provideLogsWithInitReasonWithNotEmptySymbol()
    {
        $stateSet = $this->_getBillingStateSet();
        return array(
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array('state' => 'INIT', 'reason' => 'init',  'symbol' => '*',  'timestamp' => '1.000001'),
                    array('state' => 'INIT', 'reason' => 'sleep', 'symbol' => null, 'timestamp' => '1.000002'),
                ),
                'logRecordIndex' => 0,
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_symbol
     * @group issue22
     * @dataProvider provideLogsWithInitReasonWithNotEmptySymbol
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 701
     * @expectedExceptionMessageRegExp /^Argument \$log has invalid value: invalid value symbol in sequence at index \d+$/
     */
    public function test_VerifyLog_InitReasonWithNotEmptySymbol_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_fsm->verifyLog($stateSet, $log);
    }

    /**
     * @group issue22
     * @dataProvider provideLogsWithInitReasonWithNotEmptySymbol
     */
    public function test_VerifyLog_InitReasonWithNotEmptySymbol_ThrowsException_CertainKeys($stateSet, $log, $logRecordIndex)
    {
        $this->assertExceptionMessage($stateSet, $log, 'index', $logRecordIndex);
    }

    public function provideLogsWithResetReasonWithNotEmptySymbol()
    {
        $stateSet = $this->_getBillingStateSet();
        return array(
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array('state' => 'INIT', 'reason' => 'init',  'symbol' => null, 'timestamp' => '1.000001'),
                    array('state' => 'INIT', 'reason' => 'reset', 'symbol' => '*',  'timestamp' => '1.000001'),
                    array('state' => 'INIT', 'reason' => 'sleep', 'symbol' => null, 'timestamp' => '1.000002'),
                ),
                'logRecordIndex' => 1,
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_symbol
     * @group issue22
     * @dataProvider provideLogsWithResetReasonWithNotEmptySymbol
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 702
     * @expectedExceptionMessageRegExp /^Argument \$log has invalid value: invalid value symbol in sequence at index \d+$/
     */
    public function test_VerifyLog_ResetReasonWithNotEmptySymbol_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_fsm->verifyLog($stateSet, $log);
    }

    /**
     * @group issue22
     * @dataProvider provideLogsWithResetReasonWithNotEmptySymbol
     */
    public function test_VerifyLog_ResetReasonWithNotEmptySymbol_ThrowsException_CertainKeys($stateSet, $log, $logRecordIndex)
    {
        $this->assertExceptionMessage($stateSet, $log, 'index', $logRecordIndex);
    }

    public function provideLogsWithActionReasonWithEmptySymbol()
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
                        'symbol' => null,
                        'timestamp' => '1.000001',
                    ),
                    array(
                        'state' => 'CHECKOUT',
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
     * @group issue22
     * @dataProvider provideLogsWithActionReasonWithEmptySymbol
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 703
     * @expectedExceptionMessageRegExp /^Argument \$log has invalid value: invalid value symbol in sequence at index \d+$/
     */
    public function test_VerifyLog_ActionReasonWithEmptySymbol_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_fsm->verifyLog($stateSet, $log);
    }

    /**
     * @group issue22
     * @dataProvider provideLogsWithActionReasonWithEmptySymbol
     */
    public function test_VerifyLog_ActionReasonWithEmptySymbol_ThrowsException_CertainKeys($stateSet, $log, $logRecordIndex)
    {
        $this->assertExceptionMessage($stateSet, $log, 'index', $logRecordIndex);
    }

    public function provideLogsWithActionReasonWithAbsentSymbol()
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
                        'symbol' => 'failed',
                        'timestamp' => '1.000001',
                    ),
                    array(
                        'state' => 'CHECKOUT',
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
                        'state' => 'CHECKOUT',
                        'reason' => 'action',
                        'symbol' => 'checkout',
                        'timestamp' => '1.000001',
                    ),
                    array(
                        'state' => 'PROCESSING',
                        'reason' => 'action',
                        'symbol' => 'checkout',
                        'timestamp' => '1.000001',
                    ),
                    array(
                        'state' => 'PROCESSING',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000002',
                    ),
                ),
                'logRecordIndex' => 2,
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_symbol
     * @group issue22
     * @dataProvider provideLogsWithActionReasonWithAbsentSymbol
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 704
     * @expectedExceptionMessageRegExp /^Argument \$log has invalid value: invalid value symbol in sequence at index \d+$/
     */
    public function test_VerifyLog_ActionReasonWithAbsentSymbol_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_fsm->verifyLog($stateSet, $log);
    }

    /**
     * @group issue22
     * @dataProvider provideLogsWithActionReasonWithAbsentSymbol
     */
    public function test_VerifyLog_ActionReasonWithAbsentSymbol_ThrowsException_CertainKeys($stateSet, $log, $logRecordIndex)
    {
        $this->assertExceptionMessage($stateSet, $log, 'index', $logRecordIndex);
    }

    public function provideLogsWithActionReasonWithMismatchSymbol()
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
                        'symbol' => '*',
                        'timestamp' => '1.000001',
                    ),
                    array(
                        'state' => 'CHECKOUT',
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
                        'state' => 'CHECKOUT',
                        'reason' => 'action',
                        'symbol' => 'checkout',
                        'timestamp' => '1.000001',
                    ),
                    array(
                        'state' => 'PROCESSING',
                        'reason' => 'action',
                        'symbol' => 'failed',
                        'timestamp' => '1.000001',
                    ),
                    array(
                        'state' => 'PROCESSING',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000002',
                    ),
                ),
                'logRecordIndex' => 2,
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_symbol
     * @group issue22
     * @dataProvider provideLogsWithActionReasonWithMismatchSymbol
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 705
     * @expectedExceptionMessageRegExp /^Argument \$log has invalid value: invalid value symbol in sequence at index \d+$/
     */
    public function test_VerifyLog_ActionReasonWithMismatchSymbol_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_fsm->verifyLog($stateSet, $log);
    }

    /**
     * @group issue22
     * @dataProvider provideLogsWithActionReasonWithMismatchSymbol
     */
    public function test_VerifyLog_ActionReasonWithMismatchSymbol_ThrowsException_CertainKeys($stateSet, $log, $logRecordIndex)
    {
        $this->assertExceptionMessage($stateSet, $log, 'index', $logRecordIndex);
    }

    public function provideLogsWithSleepReasonWithNotEmptySymbol()
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
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => '*',
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
     * @group issue22
     * @dataProvider provideLogsWithSleepReasonWithNotEmptySymbol
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 706
     * @expectedExceptionMessageRegExp /^Argument \$log has invalid value: invalid value symbol in sequence at index \d+$/
     */
    public function test_VerifyLog_SleepReasonWithNotEmptySymbol_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_fsm->verifyLog($stateSet, $log);
    }

    /**
     * @group issue22
     * @dataProvider provideLogsWithSleepReasonWithNotEmptySymbol
     */
    public function test_VerifyLog_SleepReasonWithNotEmptySymbol_ThrowsException_Certainkeys($stateSet, $log, $logRecordIndex)
    {
        $this->assertExceptionMessage($stateSet, $log, 'index', $logRecordIndex);
    }

    public function provideLogsWithWakeupReasonWithNotEmptySymbol()
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
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000002',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'wakeup',
                        'symbol' => '*',
                        'timestamp' => '1.000002',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1.000002',
                    ),
                ),
                'logRecordIndex' => 2,
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_symbol
     * @group issue22
     * @dataProvider provideLogsWithWakeupReasonWithNotEmptySymbol
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 707
     * @expectedExceptionMessageRegExp /^Argument \$log has invalid value: invalid value symbol in sequence at index \d+$/
     */
    public function test_VerifyLog_WakeupReasonWithNotEmptySymbol_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_fsm->verifyLog($stateSet, $log, $logRecordIndex, true);
    }

    /**
     * @group issue22
     * @dataProvider provideLogsWithWakeupReasonWithNotEmptySymbol
     */
    public function test_VerifyLog_WakeupReasonWithNotEmptySymbol_ThrowsException_CertainKeys($stateSet, $log, $logRecordIndex)
    {
        $this->assertExceptionMessage($stateSet, $log, 'index', $logRecordIndex);
    }
}
