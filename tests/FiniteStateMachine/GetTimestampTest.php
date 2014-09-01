<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../FsmTestCase.php')));

/**
 * public function test_GetTimestamp_CallsMicrotime()
 * public function test_GetTimestamp_ReturnsFormattedValue()
 */
class Fsm_GetTimestampTest extends FsmTestCase
{
    public function test_GetTimestamp_CallsMicrotime()
    {
        $microtime = new PHPUnit_Extensions_MockFunction('microtime', $this->_fsm);
        $microtime->expects($this->once())->with($this->identicalTo(true));
        $this->_fsm->getTimestamp();
    }

    public function provideTimestamps()
    {
        return array(
            array(
                'timestamp' => 1409323005.784599,
                'expectedTimestamp' => '1409323005.784599',
            ),
            array(
                'timestamp' => 1409323005.7845991,
                'expectedTimestamp' => '1409323005.784599',
            ),
            array(
                'timestamp' => 1409323005.78459,
                'expectedTimestamp' => '1409323005.784590',
            ),
            array(
                'timestamp' => 1409323005.1,
                'expectedTimestamp' => '1409323005.100000',
            ),
            array(
                'timestamp' => 1409323005.0000001,
                'expectedTimestamp' => '1409323005.000000',
            ),
        );
    }

    /**
     * @dataProvider provideTimestamps
     */
    public function test_GetTimestamp_ReturnsFormattedValue($timestamp, $expectedTimestamp)
    {
        $microtime = new PHPUnit_Extensions_MockFunction('microtime', $this->_fsm);
        $microtime->expects($this->once())->will($this->returnValue($timestamp));
        $timestamp = $this->_fsm->getTimestamp();
        $this->assertSame($expectedTimestamp, $timestamp);
    }
}
