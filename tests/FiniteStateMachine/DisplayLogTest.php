<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../FsmTestCase.php')));

/**
 * public function test_DisplayLog_ValidLog_DisplaysProperFormat()
 */
class Fsm_DisplayLogTest extends FsmTestCase
{
    public function provideValidLogs()
    {
        $expectedContent = <<<EOC
state                             reason  symbol  timestamp          diff      
-------------------------------------------------------------------------------
INIT                              init            1409297603.783227            
b2118a9a6b91e1b40770231419b07f60  action  *       1409297603.918776  0.135549  
INIT                              reset           1409297603.918778  0.000002  

EOC;
        return array(
            array(
                'log' => array(
                    array(
                        'state' => 'INIT',
                        'reason' => 'init',
                        'symbol' => null,
                        'timestamp' => '1409297603.783227',
                    ),
                    array(
                        'state' => 'b2118a9a6b91e1b40770231419b07f60',
                        'reason' => 'action',
                        'symbol' => '*',
                        'timestamp' => '1409297603.918776',
                    ),
                    array(
                        'state' => 'INIT',
                        'reason' => 'reset',
                        'symbol' => null,
                        'timestamp' => '1409297603.918778',
                    ),
                ),
                'expectedContent' => $expectedContent,
            ),
        );
    }

    /**
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
