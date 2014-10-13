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
}
