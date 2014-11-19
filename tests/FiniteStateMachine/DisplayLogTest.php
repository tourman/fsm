<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../FsmTestCase.php')));

/**
 * public function test_DisplayLog_ValidLog_DisplaysProperFormat()
 */
class Fsm_DisplayLogTest extends FsmTestCase
{
    public function provideValidLogs()
    {
        $expectedContent[0] = <<<EOC
state                             reason  symbol  timestamp          diff         
----------------------------------------------------------------------------------
INIT                              init            1409296102.783227               
b2118a9a6b91e1b40770231419b07f60  action  *       1409297503.918776  1401.135549  
INIT                              reset           1409297603.918778   100.000002  
INIT                              sleep           1409297603.918779     0.000001  
INIT                              wakeup          1409297603.918779     0.000000  

EOC;
        $expectedContent[1] = <<<EOC
state  reason  symbol          timestamp          diff      
------------------------------------------------------------
INIT   init                    1409297603.783227            
S      action  someLongSymbol  1409297603.918776  0.135549  

EOC;
        return array(
            array(
                'log' => array(
                    array(
                        'state' => 'INIT',
                        'reason' => 'init',
                        'symbol' => null,
                        'timestamp' => '1409296102.783227',
                    ),
                    array(
                        'state' => 'b2118a9a6b91e1b40770231419b07f60',
                        'reason' => 'action',
                        'symbol' => '*',
                        'timestamp' => '1409297503.918776',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'reset',
                        'symbol' => null,
                        'timestamp' => '1409297603.918778',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'sleep',
                        'symbol' => null,
                        'timestamp' => '1409297603.918779',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'wakeup',
                        'symbol' => null,
                        'timestamp' => '1409297603.918779',
                    ),
                ),
                'expectedContent' => $expectedContent[0],
            ),
            array(
                'log' => array(
                    array(
                        'state' => 'INIT',
                        'reason' => 'init',
                        'symbol' => null,
                        'timestamp' => '1409297603.783227',
                    ),
                    array(
                        'state' => 'S',
                        'reason' => 'action',
                        'symbol' => 'someLongSymbol',
                        'timestamp' => '1409297603.918776',
                    ),
                ),
                'expectedContent' => $expectedContent[1],
            ),
        );
    }

    /**
     * @group issue1
     * @group issue1_log_display
     * @dataProvider provideValidLogs
     */
    public function test_DisplayLog_Default_DisplaysProperFormat($log, $expectedContent)
    {
        $this->setLog($log);
        ob_start();
        $this->_fsm->displayLog();
        $content = ob_get_clean();
        $this->assertSame($expectedContent, $content);
    }
}
