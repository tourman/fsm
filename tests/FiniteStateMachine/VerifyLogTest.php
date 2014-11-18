<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../FsmTestCase.php')));

/**
 * public function test_VerifyLog_Default_CallsVerifyStateSet()
 * public function test_VerifyLog_InvalidTypeLog_ThrowsException()
 * public function test_VerifyLog_InvalidLengthLog_ThrowsException()
 * public function test_VerifyLog_InvalidLengthLog_ThrowsException_CertainKeys()
 * public function test_VerifyLog_InvalidStructureLog_ThrowsException()
 * public function test_VerifyLog_InvalidStructureLog_ThrowsException_CertainKeys()
 * public function test_VerifyLog_LogWithInvalidKeys_ThrowsException()
 * public function test_VerifyLog_LogWithInvalidKeys_ThrowsException_CertainKeys()
 * public function test_VerifyLog_ValidArguments_ReturnsTrue()
 */
class Fsm_VerifyLogTest extends FsmTestCase
{
    protected $_exceptionMessage;

    public function setUp()
    {
        $this->_fsm = $this->getMockBuilder(self::FSM_CLASS_NAME)->
            disableOriginalConstructor()->
            setMethods(array('verifyStateSet'))->
            getMock();
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

    public function test_VerifyLog_Default_CallsVerifyStateSet()
    {
        $stateSet = $this->_getBillingStateSet();
        //Add unique value for the identifying
        $stateSet['INIT'][md5(uniqid())] = $stateSet['INIT']['*'];
        $this->_fsm->expects($this->once())->method('verifyStateSet')->with($this->identicalTo($stateSet));
        $this->_fsm->verifyLog($stateSet, array());
    }

    public function provideInvalidTypeLogs()
    {
        $stateSet = $this->_getBillingStateSet();
        return array(
            array(
                'stateSet' => $stateSet,
                'log' => false,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => 1,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => 1.1,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => 'false',
            ),
            array(
                'stateSet' => $stateSet,
                'log' => new stdClass(),
            ),
            array(
                'stateSet' => $stateSet,
                'log' => null,
            ),
        );
    }

    /**
     * @group issue22
     * @dataProvider provideInvalidTypeLogs
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 101
     * @expectedExceptionMessage Argument $log has invalid type
     */
    public function test_VerifyLog_InvalidTypeLog_ThrowsException($stateSet, $log)
    {
        $this->_fsm->verifyLog($stateSet, $log);
    }

    public function provideInvalidStructureLogs()
    {
        $stateSet = $this->_getBillingStateSet();
        return array(
            array(
                'stateSet' => $stateSet,
                'log' => array(false),
                'logRecordIndex' => 0,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(array(), array(), 1),
                'logRecordIndex' => 2,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(1.1),
                'logRecordIndex' => 0,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array('false', array(), 'false'),
                'logRecordIndex' => 0,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(array(), new stdClass()),
                'logRecordIndex' => 1,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(null),
                'logRecordIndex' => 0,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(null, array()),
                'logRecordIndex' => 0,
            ),
        );
    }

    public function provideInvalidLengthLogs()
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
                        'timestamp' => '1.000000',
                    ),
                ),
                'length' => 1,
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_reason
     * @group issue1_log_verification
     * @group issue22
     * @dataProvider provideInvalidLengthLogs
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 301
     * @expectedExceptionMessageRegExp /^Argument \$log has invalid value: invalid log length \d+$/
     */
    public function test_VerifyLog_InvalidLengthLog_ThrowsException($stateSet, $log, $length)
    {
        $this->_fsm->verifyLog($stateSet, $log);
    }

    /**
     * @group issue22
     * @dataProvider provideInvalidLengthLogs
     */
    public function test_VerifyLog_InvalidLengthLog_ThrowsException_CertainKeys($stateSet, $log, $length)
    {
        $this->assertExceptionMessage($stateSet, $log, 'length', $length);
    }

    /**
     * @group issue22
     * @dataProvider provideInvalidStructureLogs
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 102
     * @expectedExceptionMessageRegExp /^Argument \$log has invalid type at index \d+$/
     */
    public function test_VerifyLog_InvalidStructureLog_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_fsm->verifyLog($stateSet, $log);
    }

    /**
     * @group issue22
     * @dataProvider provideInvalidStructureLogs
     */
    public function test_VerifyLog_InvalidStructureLog_ThrowsException_CertainKeys($stateSet, $log, $logRecordIndex)
    {
        $this->assertExceptionMessage($stateSet, $log, 'index', $logRecordIndex);
    }

    public function provideLogsWithInvalidKeys()
    {
        $stateSet = $this->_getBillingStateSet();
        return array(
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    0 => array('state' => 'INIT',     'reason' => 'init',   'symbol' => null,        'timestamp' => '1.000001'),
                    1 => array(                                                                                               ),
                    2 => array('state' => 'CHECKOUT', 'reason' => 'action', 'symbol' => 'checkout',  'timestamp' => '1.000003'),
                    3 => array('state' => 'INIT',     'reason' => 'reset',  'symbol' => null,        'timestamp' => '1.000004'),
                    4 => array('state' => 'INIT',     'reason' => 'sleep',  'symbol' => null,        'timestamp' => '1.000005'),
                ),
                'logRecordIndex' => 1,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    0 => array('state' => 'INIT',     'reason' => 'init',   'symbol' => null,        'timestamp' => '1.000001'),
                    1 => array('state' => 'INIT',     'reason' => 'action', 'symbol' => '*',         'timestamp' => '1.000002'),
                    2 => array('state' => 'CHECKOUT', 'reason' => 'action', 'symbol' => 'checkout',  'timestamp' => '1.000003'),
                    3 => array(                       'reason' => 'reset',  'symbol' => null,        'timestamp' => '1.000004'),
                    4 => array('state' => 'INIT',     'reason' => 'sleep',  'symbol' => null,        'timestamp' => '1.000005'),
                ),
                'logRecordIndex' => 3,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    0 => array('state' => 'INIT',     'reason' => 'init',   'symbol' => null,        'timestamp' => '1.000001'),
                    1 => array('state' => 'INIT',     'reason' => 'action', 'symbol' => '*',         'timestamp' => '1.000002'),
                    2 => array('state' => 'CHECKOUT',                       'symbol' => 'checkout',  'timestamp' => '1.000003'),
                    3 => array('state' => 'INIT',     'reason' => 'reset',  'symbol' => null,        'timestamp' => '1.000004'),
                    4 => array('state' => 'INIT',     'reason' => 'sleep',  'symbol' => null,        'timestamp' => '1.000005'),
                ),
                'logRecordIndex' => 2,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    0 => array('state' => 'INIT',     'reason' => 'init',   'symbol' => null,        'timestamp' => '1.000001'),
                    1 => array('state' => 'INIT',     'reason' => 'action', 'symbol' => '*',         'timestamp' => '1.000002'),
                    2 => array('state' => 'CHECKOUT', 'reason' => 'action', 'symbol' => 'checkout',  'timestamp' => '1.000003'),
                    3 => array('state' => 'INIT',     'reason' => 'reset',  'symbol' => null,        'timestamp' => '1.000004'),
                    4 => array('state' => 'INIT',     'reason' => 'sleep',                           'timestamp' => '1.000005'),
                ),
                'logRecordIndex' => 4,
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    0 => array('state' => 'INIT',     'reason' => 'init',   'symbol' => null,                                 ),
                    1 => array('state' => 'INIT',     'reason' => 'action', 'symbol' => '*',         'timestamp' => '1.000002'),
                    2 => array('state' => 'CHECKOUT', 'reason' => 'action', 'symbol' => 'checkout',  'timestamp' => '1.000003'),
                    3 => array('state' => 'INIT',     'reason' => 'reset',  'symbol' => null,        'timestamp' => '1.000004'),
                    4 => array('state' => 'INIT',     'reason' => 'sleep',  'symbol' => null,        'timestamp' => '1.000005'),
                ),
                'logRecordIndex' => 0,
            ),
        );
    }

    /**
     * @group issue22
     * @dataProvider provideLogsWithInvalidKeys
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 103
     * @expectedExceptionMessageRegExp /^Argument \$log has invalid value: invalid keys at index \d+$/
     */
    public function test_VerifyLog_LogWithInvalidKeys_ThrowsException($stateSet, $log, $logRecordIndex)
    {
        $this->_fsm->verifyLog($stateSet, $log);
    }

    /**
     * @group issue22
     * @dataProvider provideLogsWithInvalidKeys
     */
    public function test_VerifyLog_LogWithInvalidKeys_ThrowsException_CertainKeys($stateSet, $log, $logRecordIndex)
    {
        $this->assertExceptionMessage($stateSet, $log, 'index', $logRecordIndex);
    }

    public function provideValidLogs()
    {
        $stateSet = $this->_getBillingStateSet();
        return array(
            array(
                'stateSet' => $stateSet,
                'log' => array(),
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array(
                        'state' => 'INIT',
                        'reason' => 'init',
                        'symbol' => null,
                        'timestamp' => '1413277575.008993',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'action',
                        'symbol' => 'checkout',
                        'timestamp' => '1413277575.103009',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1413277576.001000',
                    ),
                ),
            ),
            array(
                'stateSet' => $stateSet,
                'log' => array(
                    array(
                        'state' => 'INIT',
                        'reason' => 'init',
                        'symbol' => null,
                        'timestamp' => '1413277575.008993',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'action',
                        'symbol' => 'checkout',
                        'timestamp' => '1413277575.103009',
                    ),
                    array(
                        'state' => 'PROCESSING',
                        'reason' => 'action',
                        'symbol' => 'processing',
                        'timestamp' => '1413277576.001000',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'reset',
                        'symbol' => null,
                        'timestamp' => '1413277577.002988',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'action',
                        'symbol' => 'checkout',
                        'timestamp' => '1413277577.002988',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1413277577.003988',
                    ),
                    array(
                        'state' => 'CHECKOUT',
                        'reason' => 'wakeup',
                        'symbol' => null,
                        'timestamp' => '1413277577.003989',
                    ),
                    array(
                        'state' => 'PROCESSING',
                        'reason' => 'action',
                        'symbol' => 'processing',
                        'timestamp' => '1413277577.004002',
                    ),
                    array(
                        'state' => 'PROCESSING',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1413277578.980772',
                    ),
                ),
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_log_verification
     * @dataProvider provideValidLogs
     */
    public function test_VerifyLog_ValidArguments_ReturnsTrue($stateSet, $log)
    {
        $result = $this->_fsm->verifyLog($stateSet, $log);
        $this->assertTrue($result);
    }
}
