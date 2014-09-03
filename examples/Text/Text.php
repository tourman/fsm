<?php

class Text extends FiniteStateMachine
{
    public function setStateSet($log = array())
    {
        $stateSet = array(
            'BEFORE' => array(
                'a' => array(
                    'state' => 'INSIDE',
                    'action' => 'show',
                ),
                's' => array(
                    'state' => 'BEFORE',
                ),
                'n' => array(
                    'state' => 'BEFORE',
                    'action' => 'show',
                ),
            ),
            'INSIDE' => array(
                'a' => array(
                    'state' => 'INSIDE',
                    'action' => 'show',
                ),
                's' => array(
                    'state' => 'AFTER',
                ),
                'n' => array(
                    'state' => 'BEFORE',
                    'action' => 'show',
                ),
            ),
            'AFTER' => array(
                'a' => array(
                    'state' => 'AFTER',
                ),
                's' => array(
                    'state' => 'AFTER',
                ),
                'n' => array(
                    'state' => 'BEFORE',
                    'action' => 'show',
                ),
            ),
        );
        parent::setStateSet($stateSet, $log);
    }

    public function show($char)
    {
        echo $char;
    }

    public function process($char)
    {
        if ($char == "\n") {
            $symbol = 'n';
        } elseif (preg_match('/\s/', $char)) {
            $symbol = 's';
        } else {
            $symbol = 'a';
        }
        parent::action($symbol, array($char));
    }
}
