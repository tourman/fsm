<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../FsmTestCase.php')));

class Fsm_VerifyLogTestCase extends FsmTestCase
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
}
