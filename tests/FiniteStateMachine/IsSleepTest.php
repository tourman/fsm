<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../FsmTestCase.php')));

/**
 * public function test_IsSleep_ReturnsSleep()
 */
class Fsm_IsInitializedTest extends FsmTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function provideSleeps()
    {
        return array(
            array(
                'sleep' => true,
                'expectedSleep' => true,
            ),
            array(
                'sleep' => false,
                'expectedSleep' => false,
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_sleep_protected
     * @dataProvider provideSleeps
     */
    public function test_IsSleep_ReturnsSleep($expectedSleep)
    {
        $this->setSleep($expectedSleep);
        $sleep = $this->_fsm->isSleep();
        $this->assertSame($expectedSleep, $sleep);
    }
}
