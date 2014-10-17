<?php

class FiniteStateMachine
{
    const EXCEPTION_INVALID_TYPE = 101;
    const EXCEPTION_INVALID_VALUE = 102;
    const EXCEPTION_LOG_WITH_INVALID_KEYS = 103;

    const EXCEPTION_STATES_ARE_SET = 110;
    const EXCEPTION_STATES_ARE_NOT_SET = 111;
    const EXCEPTION_SLEEP = 112;

    const EXCEPTION_NO_DEFAULT_SYMBOL = 120;
    const EXCEPTION_ABSENT_STATE = 121;
    const EXCEPTION_ABSENT_METHOD = 122;
    const EXCEPTION_NONPUBLIC_METHOD = 123;
    const EXCEPTION_NO_STATE = 124;
    const EXCEPTION_UNLINKED_STATE = 125;

    const EXCEPTION_INVALID_TYPE_SYMBOL = 131;
    const EXCEPTION_SYMBOL_IS_OUT_OF_ALPHABET = 132;
    const EXCEPTION_SYMBOL_IS_OUT_OF_STATE = 133;

    const EXCEPTION_INVALID_LENGTH_LOG = 301;

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
    protected $_sleep = false;

    public function verifyStateSet($stateSet)
    {
        if (!is_array($stateSet)) {
            throw new InvalidArgumentException('Argument $stateSet has invalid type', self::EXCEPTION_INVALID_TYPE);
        }
        if (!$stateSet) {
            throw new InvalidArgumentException("Argument \$stateSet has invalid value: empty array", self::EXCEPTION_INVALID_VALUE);
        }
        foreach ($stateSet as $state => $symbolSet) {
            if (!is_array($symbolSet)) {
                throw new InvalidArgumentException("Argument \$stateSet has invalid value: invalid symbol set for state \"$state\"", self::EXCEPTION_INVALID_VALUE);
            }
            if (!$symbolSet) {
                throw new InvalidArgumentException("Argument \$stateSet has invalid value: empty symbol set for state \"$state\"", self::EXCEPTION_INVALID_VALUE);
            }
            foreach ($symbolSet as $symbol => $destination) {
                if (!is_array($destination)) {
                    throw new InvalidArgumentException("Argument \$stateSet has invalid value: invalid destination for symbol \"$symbol\"", self::EXCEPTION_INVALID_VALUE);
                }
                if (!$destination) {
                    throw new InvalidArgumentException("Argument \$stateSet has invalid value: empty destination for symbol \"$symbol\"", self::EXCEPTION_INVALID_VALUE);
                }
            }
        }
        $reflection = new ReflectionClass($this);
        $linkedStates = array();
        foreach ($stateSet as $state => $symbolSet) {
            foreach ($symbolSet as $symbol => $destination) {
                if (!array_key_exists('state', $destination)) {
                    throw new InvalidArgumentException("Argument \$stateSet has invalid value: there is no state for symbol \"$symbol\"", self::EXCEPTION_NO_STATE);
                }
                if (!array_key_exists($destination['state'], $stateSet)) {
                    throw new InvalidArgumentException("Argument \$stateSet has invalid value: symbol \"$symbol\" refers to the absent state", self::EXCEPTION_ABSENT_STATE);
                }
                if (isset($destination['action'])) {
                    if (!$reflection->hasMethod($destination['action'])) {
                        throw new InvalidArgumentException("Argument \$stateSet has invalid value: symbol \"$symbol\" refers to the absent method", self::EXCEPTION_ABSENT_METHOD);
                    }
                    $method = $reflection->getMethod($destination['action']);
                    if (!$method->isPublic()) {
                        throw new InvalidArgumentException("Argument \$stateSet has invalid value: symbol \"$symbol\" refers to the nonpublic method", self::EXCEPTION_NONPUBLIC_METHOD);
                    }
                }
                $linkedStates[] = $destination['state'];
            }
        }
        $states = array_keys($stateSet);
        $unlinkedStates = array_diff($states, $linkedStates);
        if ($unlinkedStates) {
            $unlinkedStates = implode(',', $unlinkedStates);
            throw new InvalidArgumentException("Argument \$stateSet has invalid value: there are states that are not linked ($unlinkedStates)", self::EXCEPTION_UNLINKED_STATE);
        }
        return true;
    }

    public function isInitialized()
    {
        return (bool)$this->_stateSet;
    }

    public function sleep()
    {
        if ($this->_sleep) {
            throw new RuntimeException('Could not call method over the sleep mode', self::EXCEPTION_SLEEP);
        }
        $this->_sleep = true;
        $this->_log[] = array(
            'state' => null,
            'reason' => 'sleep',
            'symbol' => null,
            'timestamp' => $this->getTimestamp(),
        );
        return $this->_log;
    }

    public function reset()
    {
        if ($this->_sleep) {
            throw new RuntimeException('Could not call method over the sleep mode', self::EXCEPTION_SLEEP);
        }
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
            $this->_log[] = array(
                'state' => null,
                'reason' => 'wakeup',
                'symbol' => null,
                'timestamp' => $this->getTimestamp(),
            );
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
        if ($this->_sleep) {
            throw new RuntimeException('Could not call method over the sleep mode', self::EXCEPTION_SLEEP);
        }
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
        $this->_verifyLogReasonType($log);
        $this->_verifyLogReasonValue($log);
        $this->_verifyLogReasonFirstPosition($log);
        $this->_verifyLogReasonLastPosition($log);
        $this->_verifyInitReason($log);
        $this->_verifyPositionAfterSleep($log);
        return;
        $reasons = array();
        foreach ($log as $logRecord) {
            $reasons[] = $logRecord['reason'];
        }
        $this->_verifyLogInitReasonPosition($reasons);
        return;
        foreach ($log as $logRecordIndex => $logRecord) {
            $reason = $logRecord['reason'];
            if (!is_string($reason)) {
                throw new InvalidArgumentException("Argument \$log has invalid type: invalid type reason at index $logRecordIndex", self::EXCEPTION_LOG_WITH_INVALID_TYPE_REASON);
            }
            if (!in_array($reason, array('init', 'action', 'reset', 'sleep', 'wakeup'))) {
                throw new InvalidArgumentException("Argument \$log has invalid value: invalid value reason at index $logRecordIndex", self::EXCEPTION_LOG_WITH_INVALID_VALUE_REASON);
            }
        }
        if ($log[0]['reason'] != 'init') {
            throw new InvalidArgumentException("Argument \$log has invalid value: invalid value reason in sequence at index 0, required values: (init)", 124);
        }
        $lastLogRecordIndex = sizeof($log) - 1;
        if ($log[$lastLogRecordIndex]['reason'] != 'sleep') {
            throw new InvalidArgumentException("Argument \$log has invalid value: invalid value reason in sequence at index $lastLogRecordIndex, required values: (sleep)", 125);
        }
        foreach ($log as $logRecordIndex => $logRecord) {
            $reason = $logRecord['reason'];
            $firstLogRecord = !(bool)$logRecordIndex;
            $initReason = $reason == 'init';
            if ($firstLogRecord xor $initReason) {
                throw new InvalidArgumentException("Argument \$log has invalid value: invalid value reason in sequence at index $logRecordIndex", self::EXCEPTION_LOG_WITH_INVALID_REASON_SEQUENCE);
            }
        }
    }

    protected function _verifyLogReasonType(array $log)
    {
        foreach ($log as $logRecordIndex => $logRecord) {
            if (!in_array(gettype($logRecord['reason']), array('string', 'NULL'))) {
                throw new InvalidArgumentException("Argument \$log has invalid type: invalid type reason at index $logRecordIndex", 511);
            }
        }
    }

    protected function _verifyLogReasonValue(array $log)
    {
        foreach ($log as $logRecordIndex => $logRecord) {
            if (!in_array($logRecord['reason'], array('init', 'reset', 'action', 'wakeup', 'sleep'))) {
                throw new InvalidArgumentException("Argument \$log has invalid value: invalid value reason at index $logRecordIndex", 512);
            }
        }
    }

    protected function _verifyLogReasonFirstPosition(array $log)
    {
        $logRecord = array_shift($log);
        if ($logRecord['reason'] != 'init') {
            throw new InvalidArgumentException("Argument \$log has invalid value: invalid value reason in sequence at index 0", 501);
        }
    }

    protected function _verifyLogReasonLastPosition(array $log)
    {
        $logRecordIndex = sizeof($log) - 1;
        $logRecord = array_pop($log);
        if ($logRecord['reason'] != 'sleep') {
            throw new InvalidArgumentException("Argument \$log has invalid value: invalid value reason in sequence at index $logRecordIndex", 502);
        }
    }

    protected function _verifyInitReason(array $log)
    {
        foreach ($log as $logRecordIndex => $logRecord) {
            if ($logRecordIndex && $logRecord['reason'] == 'init') {
                throw new InvalidArgumentException("Argument \$log has invalid value: invalid value reason in sequence at index $logRecordIndex", 503);
            }
        }
    }

    protected function _verifyPositionAfterSleep(array $log)
    {
        $lastLogRecordIndex = sizeof($log) - 1;
        foreach ($log as $logRecordIndex => $logRecord) {
            if (!$logRecordIndex) {
                continue;
            }
            if ($log[ $logRecordIndex - 1 ]['reason'] == 'sleep' && $logRecord['reason'] != 'wakeup') {
                throw new InvalidArgumentException("Argument \$log has invalid value: invalid value reason in sequence at index $logRecordIndex", 504);
            }
        }
    }

    protected function _verifyLogInitReasonPosition(array $reasons)
    {
        $numReasons = sizeof($reasons);
        foreach ($reasons as $reasonIndex => $reason) {
            if (!$reasonIndex) {
                continue;
            }
            if ($reason == 'init') {
                if ($reasonIndex < $numReasons - 1) {
                    throw new InvalidArgumentException("Argument \$log has invalid value: invalid value reason in sequence at index $reasonIndex", 501);
                } else {
                    throw new InvalidArgumentException("Argument \$log has invalid value: invalid value reason in sequence at index $reasonIndex", 502);
                }
            }
        }
    }

    protected function _verifyLogState($stateSet, $log)
    {
        $this->_verifyLogStateType($log);
        $this->_verifyLogStateValue($stateSet, $log);
        $this->_verifyLogStateWithInitReason($stateSet, $log);
        $this->_verifyLogStateWithResetReason($stateSet, $log);
        $this->_verifyLogStateWithActionReason($stateSet, $log);
        $this->_verifyLogStateWithSleepReason($stateSet, $log);
        $this->_verifyLogStateWithWakeupReason($stateSet, $log);
        return;
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

    protected function _verifyLogStateType($log)
    {
        foreach ($log as $logRecordIndex => $logRecord) {
            if (!is_string($logRecord['state'])) {
                throw new InvalidArgumentException("Argument \$log has invalid type: invalid type state at index $logRecordIndex", 611);
            }
        }
    }

    protected function _verifyLogStateValue($stateSet, $log)
    {
        $states = array_keys($stateSet);
        foreach ($log as $logRecordIndex => $logRecord) {
            if (!in_array($logRecord['state'], $states)) {
                throw new InvalidArgumentException("Argument \$log has invalid value: invalid value state at index $logRecordIndex", 612);
            }
        }
    }

    protected function _verifyLogStateWithInitReason($stateSet, $log)
    {
        $initState = array_shift(array_keys($stateSet));
        foreach ($log as $logRecordIndex => $logRecord) {
            if ($logRecord['reason'] != 'init') {
                continue;
            }
            if ($logRecord['state'] != $initState) {
                throw new InvalidArgumentException("Argument \$log has invalid value: invalid value state in sequence at index $logRecordIndex", 601);
            }
        }
    }

    protected function _verifyLogStateWithResetReason($stateSet, $log)
    {
        $initState = array_shift(array_keys($stateSet));
        foreach ($log as $logRecordIndex => $logRecord) {
            if ($logRecord['reason'] != 'reset') {
                continue;
            }
            if ($logRecord['state'] != $initState) {
                throw new InvalidArgumentException("Argument \$log has invalid value: invalid value state in sequence at index $logRecordIndex", 602);
            }
        }
    }

    protected function _verifyLogStateWithActionReason($stateSet, $log)
    {
        foreach ($log as $logRecordIndex => $logRecord) {
            if ($logRecord['reason'] != 'action') {
                continue;
            }
            $prevState = $log[ $logRecordIndex - 1 ]['state'];
            $states = array();
            foreach ($stateSet[$prevState] as $symbol => $destination) {
                $states[] = $destination['state'];
            }
            if (!in_array($logRecord['state'], $states)) {
                throw new InvalidArgumentException("Argument \$log has invalid value: invalid value state in sequence at index $logRecordIndex", 603);
            }
        }
    }

    protected function _verifyLogStateWithSleepReason($stateSet, $log)
    {
        foreach ($log as $logRecordIndex => $logRecord) {
            if ($logRecord['reason'] != 'sleep') {
                continue;
            }
            $prevState = $log[ $logRecordIndex - 1 ]['state'];
            if ($prevState != $logRecord['state']) {
                throw new InvalidArgumentException("Argument \$log has invalid value: invalid value state in sequence at index $logRecordIndex", 604);
            }
        }
    }

    protected function _verifyLogStateWithWakeupReason($stateSet, $log)
    {
        foreach ($log as $logRecordIndex => $logRecord) {
            if ($logRecord['reason'] != 'wakeup') {
                continue;
            }
            $prevState = $log[ $logRecordIndex - 1 ]['state'];
            if ($prevState != $logRecord['state']) {
                throw new InvalidArgumentException("Argument \$log has invalid value: invalid value state in sequence at index $logRecordIndex", 605);
            }
        }
    }

    protected function _verifyLogSymbol($stateSet, $log)
    {
        $this->_verifyLogSymbolType($log);
        $this->_verifyLogSymbolValue($stateSet, $log);
        $this->_verifyLogSymbolWithInitReason($stateSet, $log);
        $this->_verifyLogSymbolWithResetReason($stateSet, $log);
        $this->_verifyLogSymbolWithActionReason($stateSet, $log);
        $this->_verifyLogSymbolWithSleepReason($stateSet, $log);
        $this->_verifyLogSymbolWithWakeupReason($stateSet, $log);
        return;
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

    protected function _verifyLogSymbolType($log)
    {
        foreach ($log as $logRecordIndex => $logRecord) {
            if (!in_array(gettype($logRecord['symbol']), array('string', 'NULL'))) {
                throw new InvalidArgumentException("Argument \$log has invalid type: invalid type symbol at index $logRecordIndex", 711);
            }
        }
    }

    protected function _verifyLogSymbolValue($stateSet, $log)
    {
        $symbols = array(null);
        foreach ($stateSet as $state => $symbolSet) {
            $symbols = array_merge($symbols, array_keys($symbolSet));
        }
        $symbols = array_unique($symbols);
        foreach ($log as $logRecordIndex => $logRecord) {
            if (!in_array($logRecord['symbol'], $symbols)) {
                throw new InvalidArgumentException("Argument \$log has invalid value: invalid value symbol at index $logRecordIndex", 712);
            }
        }
    }

    protected function _verifyLogSymbolWithInitReason($stateSet, $log)
    {
        foreach ($log as $logRecordIndex => $logRecord) {
            if ($logRecord['reason'] != 'init') {
                continue;
            }
            if (!is_null($logRecord['symbol'])) {
                throw new InvalidArgumentException("Argument \$log has invalid value: invalid value symbol in sequence at index $logRecordIndex", 701);
            }
        }
    }

    protected function _verifyLogSymbolWithResetReason($stateSet, $log)
    {
        foreach ($log as $logRecordIndex => $logRecord) {
            if ($logRecord['reason'] != 'reset') {
                continue;
            }
            if (!is_null($logRecord['symbol'])) {
                throw new InvalidArgumentException("Argument \$log has invalid value: invalid value symbol in sequence at index $logRecordIndex", 702);
            }
        }
    }

    protected function _verifyLogSymbolWithActionReason($stateSet, $log)
    {
        foreach ($log as $logRecordIndex => $logRecord) {
            if ($logRecord['reason'] != 'action') {
                continue;
            }
            if (!is_string($logRecord['symbol'])) {
                throw new InvalidArgumentException("Argument \$log has invalid value: invalid value symbol in sequence at index $logRecordIndex", 703);
            }
        }
        foreach ($log as $logRecordIndex => $logRecord) {
            if ($logRecord['reason'] != 'action') {
                continue;
            }
            $prevState = $log[ $logRecordIndex - 1 ]['state'];
            $symbol = $logRecord['symbol'];
            if (empty($stateSet[$prevState][$symbol])) {
                throw new InvalidArgumentException("Argument \$log has invalid value: invalid value symbol in sequence at index $logRecordIndex", 704);
            }
        }
        foreach ($log as $logRecordIndex => $logRecord) {
            if ($logRecord['reason'] != 'action') {
                continue;
            }
            $prevState = $log[ $logRecordIndex - 1 ]['state'];
            $state = $logRecord['state'];
            $symbol = $logRecord['symbol'];
            if ($stateSet[$prevState][$symbol]['state'] != $state) {
                throw new InvalidArgumentException("Argument \$log has invalid value: invalid value symbol in sequence at index $logRecordIndex", 705);
            }
        }
    }

    protected function _verifyLogSymbolWithSleepReason($stateSet, $log)
    {
        foreach ($log as $logRecordIndex => $logRecord) {
            if ($logRecord['reason'] != 'sleep') {
                continue;
            }
            if (!is_null($logRecord['symbol'])) {
                throw new InvalidArgumentException("Argument \$log has invalid value: invalid value symbol in sequence at index $logRecordIndex", 706);
            }
        }
    }

    protected function _verifyLogSymbolWithWakeupReason($stateSet, $log)
    {
        foreach ($log as $logRecordIndex => $logRecord) {
            if ($logRecord['reason'] != 'wakeup') {
                continue;
            }
            if (!is_null($logRecord['symbol'])) {
                throw new InvalidArgumentException("Argument \$log has invalid value: invalid value symbol in sequence at index $logRecordIndex", 707);
            }
        }
    }

    protected function _verifyLogTimestamp($stateSet, $log)
    {
        $this->_verifyLogTimestampType($log);
        $this->_verifyLogTimestampValue($log);
        $this->_verifyLogTimestampSequence($log);
        return;
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

    protected function _verifyLogTimestampType($log)
    {
        foreach ($log as $logRecordIndex => $logRecord) {
            if (!is_string($logRecord['timestamp'])) {
                throw new InvalidArgumentException("Argument \$log has invalid type: invalid type timestamp at index $logRecordIndex", 811);
            }
        }
    }

    protected function _verifyLogTimestampValue($log)
    {
        foreach ($log as $logRecordIndex => $logRecord) {
            if (!preg_match('/^(0|[1-9]\d*)\.\d{6}$/', $logRecord['timestamp'])) {
                throw new InvalidArgumentException("Argument \$log has invalid value: invalid value timestamp at index $logRecordIndex", 812);
            }
        }
    }

    protected function _verifyLogTimestampSequence($log)
    {
        foreach ($log as $logRecordIndex => $logRecord) {
            if (!$logRecordIndex) {
                continue;
            }
            $prevTimestamp = $log[ $logRecordIndex - 1]['timestamp'];
            if ($logRecord['timestamp'] < $prevTimestamp) {
                throw new InvalidArgumentException("Argument \$log has invalid value: invalid value timestamp in sequence at index $logRecordIndex", 813);
            }
        }
    }

    public function verifyLog($stateSet, $log)
    {
        $this->verifyStateSet($stateSet);
        if (!is_array($log)) {
            throw new InvalidArgumentException('Argument $log has invalid type', self::EXCEPTION_INVALID_TYPE);
        }
        $length = sizeof($log);
        if (!$length) {
            return true;
        }
        foreach ($log as $logRecordIndex => $logRecord) {
            if (!is_array($logRecord)) {
                throw new InvalidArgumentException("Argument \$log has invalid type: invalid type at index $logRecordIndex", 102);
            }
        }
        if ($length < 2) {
            throw new InvalidArgumentException("Argument \$log has invalid value: invalid log length: $length", self::EXCEPTION_INVALID_LENGTH_LOG);
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
