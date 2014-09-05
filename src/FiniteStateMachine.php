<?php

class FiniteStateMachine
{
    const EXCEPTION_INVALID_TYPE = 101;
    const EXCEPTION_INVALID_VALUE = 102;
    const EXCEPTION_LOG_WITH_INVALID_KEYS = 103;

    const EXCEPTION_STATES_ARE_SET = 110;
    const EXCEPTION_STATES_ARE_NOT_SET = 111;

    const EXCEPTION_STATE_SET_IS_EMPTY = 202;
    const EXCEPTION_STATE_SET_WITH_INVALID_TYPE_STATE = 203;
    const EXCEPTION_STATE_SET_WITH_INVALID_TYPE_SYMBOL_SET = 204;
    const EXCEPTION_STATE_SET_WITH_EMPTY_FIRST_STATE = 205;
    const EXCEPTION_STATE_SET_WITH_INVALID_TYPE_SYMBOL = 206;
    const EXCEPTION_STATE_SET_WITH_INVALID_TYPE_DESTINATION = 207;
    const EXCEPTION_STATE_SET_WITH_DESTINATION_HAS_NO_STATE = 208;
    const EXCEPTION_STATE_SET_WITH_DESTINATION_REFERS_TO_ABSENT_STATE = 209;
    const EXCEPTION_STATE_SET_WITH_DESTINATION_HAS_INVALID_TYPE_ACTION = 210;
    const EXCEPTION_STATE_SET_WITH_DESTINATION_REFERS_TO_ABSENT_METHOD = 211;
    const EXCEPTION_STATE_SET_WITH_DESTINATION_REFERS_TO_NON_PUBLIC_METHOD = 212;
    const EXCEPTION_STATE_SET_WITH_DESTINATION_WITH_STATE_WITH_NO_REFERENCE_TO = 213;

    const EXCEPTION_NO_DEFAULT_SYMBOL = 120;
    const EXCEPTION_ABSENT_STATE = 121;
    const EXCEPTION_ABSENT_METHOD = 122;
    const EXCEPTION_NONPUBLIC_METHOD = 123;
    const EXCEPTION_NO_STATE = 124;

    const EXCEPTION_INVALID_TYPE_SYMBOL = 131;
    const EXCEPTION_SYMBOL_IS_OUT_OF_ALPHABET = 132;
    const EXCEPTION_SYMBOL_IS_OUT_OF_STATE = 133;

    const EXCEPTION_LOG_WITH_INVALID_TYPE_STATE = 111;
    const EXCEPTION_LOG_WITH_INVALID_VALUE_STATE = 112;
    const EXCEPTION_LOG_WITH_INVALID_STATE_SEQUENCE = 113;

    const EXCEPTION_LOG_WITH_INVALID_TYPE_REASON = 121;
    const EXCEPTION_LOG_WITH_INVALID_VALUE_REASON = 122;
    const EXCEPTION_LOG_WITH_INVALID_REASON_SEQUENCE = 123;

    const EXCEPTION_LOG_WITH_INVALID_TYPE_SYMBOL = 131;
    const EXCEPTION_LOG_WITH_INVALID_VALUE_SYMBOL = 132;
    const EXCEPTION_LOG_WITH_INVALID_SYMBOL_SEQUENCE = 133;

    const EXCEPTION_LOG_WITH_INVALID_TYPE_TIMESTAMP = 141;
    const EXCEPTION_LOG_WITH_INVALID_VALUE_TIMESTAMP = 142;
    const EXCEPTION_LOG_WITH_INVALID_TIMESTAMP_SEQUENCE = 143;

    protected $_stateSet;
    protected $_state;
    protected $_log = array();

    public function verifyStateSet($stateSet)
    {
        if (!is_array($stateSet)) {
            throw new InvalidArgumentException('Argument $stateSet has invalid type', self::EXCEPTION_INVALID_TYPE);
        }
        if (!$stateSet) {
            throw new InvalidArgumentException("Argument \$stateSet has invalid value: empty array", self::EXCEPTION_STATE_SET_IS_EMPTY);
        }
        foreach ($stateSet as $state => $symbolSet) {
            if (!is_string($state)) {
                throw new InvalidArgumentException("Argument \$stateSet has invalid value: invalid type state", self::EXCEPTION_STATE_SET_WITH_INVALID_TYPE_STATE);
            }
        }
        foreach ($stateSet as $state => $symbolSet) {
            if (!is_array($symbolSet)) {
                throw new InvalidArgumentException("Argument \$stateSet has invalid value: invalid type symbol set for state $state", self::EXCEPTION_STATE_SET_WITH_INVALID_TYPE_SYMBOL_SET);
            }
        }
        $states = array_keys($stateSet);
        if (!$stateSet[$states[0]]) {
            throw new InvalidArgumentException("Argument \$stateSet has invalid value: first state is empty", self::EXCEPTION_STATE_SET_WITH_EMPTY_FIRST_STATE);
        }
        foreach ($stateSet as $state => $symbolSet) {
            foreach ($symbolSet as $symbol => $destination) {
                if (!is_string($symbol)) {
                    throw new InvalidArgumentException("Argument \$stateSet has invalid value: invalid type symbol for state $state", self::EXCEPTION_STATE_SET_WITH_INVALID_TYPE_SYMBOL);
                }
            }
        }
        foreach ($stateSet as $state => $symbolSet) {
            foreach ($symbolSet as $symbol => $destination) {
                if (!is_array($destination)) {
                    throw new InvalidArgumentException("Argument \$stateSet has invalid value: invalid type destination for state $state and symbol $symbol", self::EXCEPTION_STATE_SET_WITH_INVALID_TYPE_DESTINATION);
                }
            }
        }
        foreach ($stateSet as $state => $symbolSet) {
            foreach ($symbolSet as $symbol => $destination) {
                if (!isset($destination['state'])) {
                    throw new InvalidArgumentException("Argument \$stateSet has invalid value: destination has no state for state $state and symbol $symbol", self::EXCEPTION_STATE_SET_WITH_DESTINATION_HAS_NO_STATE);
                }
            }
        }
        foreach ($stateSet as $state => $symbolSet) {
            foreach ($symbolSet as $symbol => $destination) {
                if (!array_key_exists($destination['state'], $stateSet)) {
                    throw new InvalidArgumentException("Argument \$stateSet has invalid value: destination refers to absent state {$destination['state']} for state $state and symbol $symbol", self::EXCEPTION_STATE_SET_WITH_DESTINATION_REFERS_TO_ABSENT_STATE);
                }
            }
        }
        foreach ($stateSet as $state => $symbolSet) {
            foreach ($symbolSet as $symbol => $destination) {
                if (array_key_exists('action', $destination) && !is_string($destination['action'])) {
                    throw new InvalidArgumentException("Argument \$stateSet has invalid value: destination has invalid type action for state $state and symbol $symbol", self::EXCEPTION_STATE_SET_WITH_DESTINATION_HAS_INVALID_TYPE_ACTION);
                }
            }
        }
        foreach ($stateSet as $state => $symbolSet) {
            foreach ($symbolSet as $symbol => $destination) {
                if (!array_key_exists('action', $destination)) {
                    continue;
                }
                $class = new ReflectionClass($this);
                if (!$class->hasMethod($destination['action'])) {
                    throw new InvalidArgumentException("Argument \$stateSet has invalid value: destination refers to absent method {$destination['action']} for state $state and symbol $symbol", self::EXCEPTION_STATE_SET_WITH_DESTINATION_REFERS_TO_ABSENT_METHOD);
                }
            }
        }
        foreach ($stateSet as $state => $symbolSet) {
            foreach ($symbolSet as $symbol => $destination) {
                if (!array_key_exists('action', $destination)) {
                    continue;
                }
                $class = new ReflectionClass($this);
                $method = $class->getMethod($destination['action']);
                if (!$method->isPublic()) {
                    throw new InvalidArgumentException("Argument \$stateSet has invalid value: destination refers to non-public method {$destination['action']} for state $state and symbol $symbol", self::EXCEPTION_STATE_SET_WITH_DESTINATION_REFERS_TO_NON_PUBLIC_METHOD);
                }
            }
        }
        $linkedStates = array();
        foreach ($stateSet as $state => $symbolSet) {
            foreach ($symbolSet as $symbol => $destination) {
                $linkedStates[] = $destination['state'];
            }
        }
        $linkedStates = array_unique($linkedStates);
        $nonLinkedStates = array_diff(array_slice($states, 1), $linkedStates);
        if ($nonLinkedStates) {
            $nonLinkedState = array_shift($nonLinkedStates);
            throw new InvalidArgumentException("Argument \$stateSet has invalid value: there is a state $nonLinkedState with no reference to", self::EXCEPTION_STATE_SET_WITH_DESTINATION_WITH_STATE_WITH_NO_REFERENCE_TO);
        }
        return true;
    }

    public function isInitialized()
    {
        return (bool)$this->_stateSet;
    }

    public function sleep()
    {
        return $this->_log;
    }

    public function reset()
    {
        if (!$this->isInitialized()) {
            throw new RuntimeException('States are not set', self::EXCEPTION_STATES_ARE_NOT_SET);
        }
        $this->_setState(array_shift(array_keys($this->_stateSet)), 'reset');
    }

    public function setStateSet($stateSet, $log = array())
    {
        if ($this->isInitialized()) {
            throw new RuntimeException('States are set', self::EXCEPTION_STATES_ARE_SET);
        }
        $this->verifyLog($stateSet, $log);
        $this->_stateSet = $stateSet;
        if ($log) {
            $this->_setState(array_shift(array_keys($stateSet)), 'init');
            $this->_log = $log;
            $lastLogRecord = array_pop($log);
            $this->_state = $lastLogRecord['state'];
        } else {
            $this->_setState(array_shift(array_keys($stateSet)), 'init');
        }
        return;
    }

    protected function _setState($state, $reason, $symbol = null)
    {
        $this->_state = $state;
        $this->_log[] = array(
            'state' => $state,
            'reason' => $reason,
            'symbol' => $symbol,
            'timestamp' => $this->getTimestamp(),
        );
    }

    public function action($symbol, $arguments = array())
    {
        if (!is_array($arguments)) {
            throw new InvalidArgumentException('Argument $arguments has invalid type', self::EXCEPTION_INVALID_TYPE);
        }
        if (!$this->isInitialized()) {
            throw new RuntimeException('States are not set', self::EXCEPTION_STATES_ARE_NOT_SET);
        }
        $this->verifySymbol($symbol);
        $result = null;
        $action = isset($this->_stateSet[ $this->_state ][$symbol]['action']) ? $this->_stateSet[ $this->_state ][$symbol]['action'] : null;
        $state = $this->_stateSet[ $this->_state ][$symbol]['state'];
        if ($action) {
            $result = call_user_func_array(array($this, $action), $arguments);
        }
        $this->_setState($state, 'action', $symbol);
        return $result;
    }

    public function verifySymbol($symbol)
    {
        if (!is_string($symbol)) {
            throw new InvalidArgumentException('Argument $symbol has invalid type', self::EXCEPTION_INVALID_TYPE_SYMBOL);
        }
        if (!$this->isInitialized()) {
            throw new RuntimeException('States are not set', self::EXCEPTION_STATES_ARE_NOT_SET);
        }
        $alphabet = $this->_getAlphabet($this->_stateSet);
        if (!in_array($symbol, $alphabet)) {
            $alphabet = implode('","', $alphabet);
            $alphabet = "(\"$alphabet\")";
            throw new InvalidArgumentException("Argument \$symbol has invalid value: symbol \"$symbol\" is out of the alphabet $alphabet", self::EXCEPTION_SYMBOL_IS_OUT_OF_ALPHABET);
        }
        if (!array_key_exists($symbol, $this->_stateSet[$this->_state])) {
            throw new InvalidArgumentException("Argument \$symbol has invalid value: symbol \"$symbol\" is out of the state \"{$this->_state}\"", self::EXCEPTION_SYMBOL_IS_OUT_OF_STATE);
        }
        return true;
    }

    protected function _getAlphabet($stateSet)
    {
        $alphabet = array_map('array_keys', $stateSet);
        $alphabet = array_reduce($alphabet, 'array_merge', array());
        $alphabet = array_unique($alphabet);
        sort($alphabet);
        return $alphabet;
    }

    protected function _getStates($stateSet)
    {
        $states = array_keys($stateSet);
        return $states;
    }

    protected function _getAllowedTransitions($stateSet)
    {
        $allowedTransitions = array();
        foreach ($stateSet as $state => $symbolSet) {
            $allowedTransitions[$state] = array();
            foreach ($symbolSet as $symbol => $destination) {
                $allowedTransitions[$state][] = $destination['state'];
            }
        }
        return $allowedTransitions;
    }

    protected function _verifyLogReason($stateSet, $log)
    {
        foreach ($log as $logRecordIndex => $logRecord) {
            $reason = $logRecord['reason'];
            if (!is_string($reason)) {
                throw new InvalidArgumentException("Argument \$log has invalid type: invalid type reason at index $logRecordIndex", self::EXCEPTION_LOG_WITH_INVALID_TYPE_REASON);
            }
            if (!in_array($reason, array('init', 'action', 'reset'))) {
                throw new InvalidArgumentException("Argument \$log has invalid value: invalid value reason at index $logRecordIndex", self::EXCEPTION_LOG_WITH_INVALID_VALUE_REASON);
            }
            $firstLogRecord = !(bool)$logRecordIndex;
            $initReason = $reason == 'init';
            if ($firstLogRecord xor $initReason) {
                throw new InvalidArgumentException("Argument \$log has invalid value: invalid value reason in sequence at index $logRecordIndex", self::EXCEPTION_LOG_WITH_INVALID_REASON_SEQUENCE);
            }
        }
    }

    protected function _verifyLogState($stateSet, $log)
    {
        $states = $this->_getStates($stateSet);
        $allowedTransitions = $this->_getAllowedTransitions($stateSet);
        foreach ($log as $logRecordIndex => $logRecord) {
            $state = $logRecord['state'];
            if (!is_string($state)) {
                throw new InvalidArgumentException("Argument \$log has invalid type: invalid type state at index $logRecordIndex", self::EXCEPTION_LOG_WITH_INVALID_TYPE_STATE);
            }
            if (!in_array($state, $states)) {
                throw new InvalidArgumentException("Argument \$log has invalid value: invalid value state at index $logRecordIndex", self::EXCEPTION_LOG_WITH_INVALID_VALUE_STATE);
            }
            //State sequence
            $prevState = $logRecordIndex ? $log[$logRecordIndex - 1]['state'] : null;
            $firstState = $states[0] == $state;
            $firstLogRecord = !(bool)$logRecordIndex;
            $allowedTransition = $logRecordIndex ? in_array($state, $allowedTransitions[$prevState]) : null;
            $reason = $logRecord['reason'];
            if (
                $firstState && $firstLogRecord && $reason == 'init'
                ||
                $firstState && !$firstLogRecord && $reason == 'reset'
                ||
                !$firstLogRecord && $allowedTransition && $reason == 'action'
            ) {
                //Allowed log record
            } else {
                throw new InvalidArgumentException("Argument \$log has invalid value: invalid value state in sequence at index $logRecordIndex", self::EXCEPTION_LOG_WITH_INVALID_STATE_SEQUENCE);
            }
        }
    }

    protected function _verifyLogSymbol($stateSet, $log)
    {
        $alphabet = $this->_getAlphabet($stateSet);
        foreach ($log as $logRecordIndex => $logRecord) {
            $symbol = $logRecord['symbol'];
            $reason = $logRecord['reason'];
            $state = $logRecord['state'];
            $prevState = $logRecordIndex ? $log[$logRecordIndex - 1]['state'] : null;
            if (!is_string($symbol) && !is_null($symbol)) {
                throw new InvalidArgumentException("Argument \$log has invalid type: invalid type symbol at index $logRecordIndex", self::EXCEPTION_LOG_WITH_INVALID_TYPE_SYMBOL);
            }
            if (is_string($symbol) && !in_array($symbol, $alphabet)) {
                throw new InvalidArgumentException("Argument \$log has invalid value: invalid value symbol at index $logRecordIndex", self::EXCEPTION_LOG_WITH_INVALID_VALUE_SYMBOL);
            }
            $symbolIsString = is_string($symbol);
            $reasonIsAction = $reason == 'action';
            if ($symbolIsString xor $reasonIsAction) {
                throw new InvalidArgumentException("Argument \$log has invalid value: invalid value symbol in sequence at index $logRecordIndex", self::EXCEPTION_LOG_WITH_INVALID_SYMBOL_SEQUENCE);
            }
            if ($reasonIsAction) {
                if (!array_key_exists($symbol, $stateSet[$prevState]) || $stateSet[$prevState][$symbol]['state'] != $state) {
                    throw new InvalidArgumentException("Argument \$log has invalid value: invalid value symbol in sequence at index $logRecordIndex", self::EXCEPTION_LOG_WITH_INVALID_SYMBOL_SEQUENCE);
                }
            }
        }
    }

    protected function _verifyLogTimestamp($stateSet, $log)
    {
        foreach ($log as $logRecordIndex => $logRecord) {
            $timestamp = $logRecord['timestamp'];
            if (!is_string($timestamp)) {
                throw new InvalidArgumentException("Argument \$log has invalid type: invalid type timestamp at index $logRecordIndex", self::EXCEPTION_LOG_WITH_INVALID_TYPE_TIMESTAMP);
            }
            if (!preg_match('/^\d+\.\d{6}$/', $timestamp)) {
                throw new InvalidArgumentException("Argument \$log has invalid value: invalid value timestamp at index $logRecordIndex", self::EXCEPTION_LOG_WITH_INVALID_VALUE_TIMESTAMP);
            }
            if ($logRecordIndex && $timestamp < $log[$logRecordIndex - 1]['timestamp']) {
                throw new InvalidArgumentException("Argument \$log has invalid value: invalid value timestamp in sequence at index $logRecordIndex", self::EXCEPTION_LOG_WITH_INVALID_TIMESTAMP_SEQUENCE);
            }
        }
    }

    public function verifyLog($stateSet, $log)
    {
        $this->verifyStateSet($stateSet);
        if (!is_array($log)) {
            throw new InvalidArgumentException('Argument $log has invalid type', self::EXCEPTION_INVALID_TYPE);
        }
        foreach ($log as $logRecordIndex => $logRecord) {
            if (!is_array($logRecord)) {
                throw new InvalidArgumentException('Argument $log has invalid value', self::EXCEPTION_INVALID_VALUE);
            }
            if (array_diff(array('state', 'reason', 'symbol', 'timestamp'), array_keys($logRecord))) {
                throw new InvalidArgumentException("Argument \$log has invalid value: invalid keys at index $logRecordIndex", self::EXCEPTION_LOG_WITH_INVALID_KEYS);
            }
        }
        $this->_verifyLogReason($stateSet, $log);
        $this->_verifyLogState($stateSet, $log);
        $this->_verifyLogSymbol($stateSet, $log);
        $this->_verifyLogTimestamp($stateSet, $log);
        return true;
    }

    public function displayLog()
    {
        $log = $this->_log;
        foreach ($log as $logRecordIndex => &$logRecord) {
            if (!$logRecordIndex) {
                $logRecord['diff'] = '';
            } else {
                $logRecord['diff'] = sprintf('%.6f', $logRecord['timestamp'] - $log[$logRecordIndex - 1]['timestamp']);
            }
        }
        unset($logRecord);
        array_unshift($log, array(
            'state' => 'state',
            'reason' => 'reason',
            'symbol' => 'symbol',
            'timestamp' => 'timestamp',
            'diff' => 'diff',
        ));
        $colSizes = array(
            'state' => 0,
            'reason' => 0,
            'symbol' => 0,
            'timestamp' => 0,
            'diff' => 0,
        );
        //Define column sizes
        foreach ($log as $logRecord) {
            foreach ($logRecord as $key => $value) {
                $cellSize = strlen($value . '  ');
                $colSizes[$key] = max($colSizes[$key], $cellSize);
            }
        }
        //Define row format
        $rowFormat = '';
        foreach ($colSizes as $colSize) {
            $rowFormat .= "%-{$colSize}s";
        }
        $rowFormat .= "\n";
        foreach ($log as $logRecordIndex => $logRecord) {
            call_user_func_array('printf', array_merge(array($rowFormat), $logRecord));
            if (!$logRecordIndex) {
                printf("%s\n", str_repeat('-', array_sum($colSizes)));
            }
        }
    }

    public function getTimestamp()
    {
        $timestamp = microtime(true);
        $timestamp = sprintf('%.6f', $timestamp);
        return $timestamp;
    }
}
