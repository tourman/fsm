<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../VerifyLogTestCase.php')));

class Fsm_VerifyLog_ReasonTestCase extends Fsm_VerifyLogTestCase
{
    protected function _getStateSet()
    {
        return array_shift(array_shift($this->provideValidStateSets()));
    }
}
