<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../src/FiniteStateMachine.php')));
require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/TestFiniteStateMachine.php')));

class FsmTestCase extends PHPUnit_Framework_TestCase
{
    const FSM_CLASS_NAME = 'TestFiniteStateMachine';

    protected $_fsm;

    public function setUp()
    {
        $this->_fsm = $this->_createFsm();
    }

    protected function _createFsm()
    {
        return new TestFiniteStateMachine();
    }

    protected function _getBillingStateSet()
    {
        /*
data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAPAAAAFAAQMAAAC4JmMQAAAABlBMVEXw+f
8AAAAr1JQLAAABzklEQVRo3u3Yv0sCcRQA8A5PuKnuKjcJE4JTpEV3D7qQDBe5/oHAoSWwhoSWwxzkhj
I0XJrCpaV/IYcgA8m/wtm1wTDvh5719fskvJND31t83Ifv4/zel/fgu4aB4b1IDozoUzhk/rDIyCbHXm
FugBydiyOz2LO7huwcw43LDoYq7rNfAtmnzig+z2pWApkLucjsrP+9jJ8EZyjyP3j9+POo1aeykBayYo
XK/HXlFOI0rzNcHHy1d96Tu4aMM9TbM5QZjOIrOU4neMpqZhqzEsg+FWA7c5D9EpDZL0RkQHHiAJLZr+
NLZsgryDvhu4Ke7naCl+1ctaQ/Y7bqqsV8uCwbLD5pmw812eBtRbI4mL0d8U35UDGLN8bMX1irO+Eiu6
e8/WXNYk4vnpANlie4VzD5cfhqcXP1QV31wK4hO85w44LbHtnF7Wdke2WIZ0Bxco2DswReDc8xeAquwA
x1/5PYB5DCo+OLjKwz08l1qyqdxVygJtF5/ySgAMVbQ27SmcsEEkDxjUw33vTkriE7y3DjsrNlbNgLnK
F4l4t3uciOc759nvruUVmLnckCV6JxMfqcEloAv0CrtYgIcf5DvLrveXLXkF28y8XAcCF+AHQzIWtrUf
fdAAAAAElFTkSuQmCC
         */
        $stateSet = array(
            'INIT' => array(
                '*' => array(
                    'state' => 'INIT',
                ),
                'checkout' => array(
                    'state' => 'CHECKOUT',
                    'action' => 'checkout',
                ),
            ),
            'CHECKOUT' => array(
                'processing' => array(
                    'state' => 'PROCESSING',
                    'action' => 'process',
                ),
                'failed' => array(
                    'state' => 'FAILED',
                    'action' => 'error',
                ),
                'void' => array(
                    'state' => 'VOID',
                    'action' => 'void',
                ),
                '*' => array(
                    'state' => 'CHECKOUT',
                ),
            ),
            'PROCESSING' => array(
                'pending' => array(
                    'state' => 'PENDING',
                    'action' => 'pend',
                ),
                'failed' => array(
                    'state' => 'FAILED',
                    'action' => 'error',
                ),
                'void' => array(
                    'state' => 'VOID',
                    'action' => 'void',
                ),
                '*' => array(
                    'state' => 'PROCESSING',
                ),
            ),
            'PENDING' => array(
                'completed' => array(
                    'state' => 'COMPLETED',
                    'action' => 'complete',
                ),
                'failed' => array(
                    'state' => 'FAILED',
                    'action' => 'error',
                ),
                'void' => array(
                    'state' => 'VOID',
                    'action' => 'void',
                ),
                '*' => array(
                    'state' => 'PENDING',
                ),
            ),
            'COMPLETED' => array(
                '*' => array(
                    'state' => 'COMPLETED',
                ),
            ),
            'FAILED' => array(
                '*' => array(
                    'state' => 'FAILED',
                ),
            ),
            'VOID' => array(
                '*' => array(
                    'state' => 'VOID',
                ),
            ),
        );
        return $stateSet;
    }

    public function provideValidStateSets()
    {
        return array(
            array(
                'stateSet' => $this->_getBillingStateSet(),
            ),
        );
    }

    public function provideValidStates()
    {
        $argumentSets = array();
        $stateSet = $this->_getBillingStateSet();
        $states = array_keys($stateSet);
        foreach ($states as $state) {
            $argumentSets[] = array(
                'stateSet' => $stateSet,
                'state' => $state,
            );
        }
        return $argumentSets;
    }

    protected function _generateLog($stateSet)
    {
        $log = array();
        $logLength = rand(3, 5);
        $state = array_shift(array_keys($stateSet));
        $reason = 'init';
        $symbol = null;
        $timestamp = sprintf('%.6f', microtime(true) - $logLength);
        for ($logRecordIndex = 0; $logRecordIndex < $logLength; $logRecordIndex++) {
            $log[] = array(
                'state' => $state,
                'reason' => $reason,
                'symbol' => $symbol,
                'timestamp' => $timestamp,
            );
            $symbolIndex = rand(0, sizeof($stateSet[$state]) - 1);
            $symbols = array_keys($stateSet[$state]);
            $symbol = $symbols[$symbolIndex];
            $state = $stateSet[$state][$symbol]['state'];
            $reason = 'action';
            $timestamp = sprintf('%.6f', $timestamp + 1 + rand(0, 999999) / 1000000);
        }
        return $log;
    }

    public function provideValidLogs()
    {
        $argumentSets = array();
        $stateSets = array_map('array_shift', $this->provideValidStateSets());
        foreach ($stateSets as $stateSet) {
            $numLogs = rand(2, 4);
            for ($logIndex = 0; $logIndex < $numLogs; $logIndex++) {
                $argumentSets[] = array(
                    'stateSet' => $stateSet,
                    'log' => $this->_generateLog($stateSet),
                );
            }
        }
        return $argumentSets;
    }

    public function assertInvalidTypeArgumentExceptionMessage(InvalidArgumentException $e, $argumentName)
    {
        $messageRegExp = preg_quote("Argument \$$argumentName has invalid type", '/');
        $this->assertRegExp("/^$messageRegExp/", $e->getMessage());
    }

    public function assertInvalidValueArgumentExceptionMessage(InvalidArgumentException $e, $argumentName)
    {
        $messageRegExp = preg_quote("Argument \$$argumentName has invalid value", '/');
        $this->assertRegExp("/^$messageRegExp/", $e->getMessage());
    }

    public function setStateSet($stateSet)
    {
        $class = new ReflectionClass($this->_fsm);
        $property = $class->getProperty('_stateSet');
        $property->setAccessible(true);
        $property->setValue($this->_fsm, $stateSet);
        $property->setAccessible(false);
    }

    public function setState($state)
    {
        $class = new ReflectionClass($this->_fsm);
        $property = $class->getProperty('_state');
        $property->setAccessible(true);
        $property->setValue($this->_fsm, $state);
        $property->setAccessible(false);
    }

    public function getState()
    {
        $class = new ReflectionClass($this->_fsm);
        $property = $class->getProperty('_state');
        $property->setAccessible(true);
        $value = $property->getValue($this->_fsm);
        $property->setAccessible(false);
        return $value;
    }

    public function setLog($log, FiniteStateMachine $fsm = null)
    {
        $fsm = is_null($fsm) ? $this->_fsm : $fsm;
        $class = new ReflectionClass($fsm);
        $property = $class->getProperty('_log');
        $property->setAccessible(true);
        $property->setValue($fsm, $log);
        $property->setAccessible(false);
    }

    public function getLog(FiniteStateMachine $fsm = null)
    {
        $fsm = is_null($fsm) ? $this->_fsm : $fsm;
        $class = new ReflectionClass($fsm);
        $property = $class->getProperty('_log');
        $property->setAccessible(true);
        $value = $property->getValue($fsm);
        $property->setAccessible(false);
        return $value;
    }

    public function setSleep($sleep, FiniteStateMachine $fsm = null)
    {
        $fsm = is_null($fsm) ? $this->_fsm : $fsm;
        $class = new ReflectionClass($fsm);
        $property = $class->getProperty('_sleep');
        $property->setAccessible(true);
        $property->setValue($fsm, $sleep);
        $property->setAccessible(false);
    }

    public function getSleep(FiniteStateMachine $fsm = null)
    {
        $fsm = is_null($fsm) ? $this->_fsm : $fsm;
        $class = new ReflectionClass($fsm);
        $property = $class->getProperty('_sleep');
        $property->setAccessible(true);
        $value = $property->getValue($fsm);
        $property->setAccessible(false);
        return $value;
    }

    public function generateTimestamp()
    {
        return sprintf('%.6f', microtime(true));
    }
}
