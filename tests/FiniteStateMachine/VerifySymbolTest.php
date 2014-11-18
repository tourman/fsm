<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../FsmTestCase.php')));

/**
 * public function test_VerifySymbol_InvalidTypeSymbol_ThrowsException()
 * public function test_VerifySymbol_IsInitializedReturnsFalse_ThrowsException()
 * public function test_VerifySymbol_SymbolIsOutOfAlphabet_ThrowsException()
 * public function test_VerifySymbol_SymbolIsOutOfState_ThrowsException()
 * public function test_VerifySymbol_ValidSymbol_ReturnsTrue()
 */
class Fsm_VerifySymbolTest extends FsmTestCase
{
    public function setUp()
    {
        $this->_fsm = $this->getMockBuilder(self::FSM_CLASS_NAME)->
            disableOriginalConstructor()->
            setMethods(array('isInitialized'))->
            getMock();
    }

    public function provideInvalidTypeSymbols()
    {
        return array(
            array(false),
            array(1),
            array(1.1),
            array(array()),
            array(new stdClass()),
            array(null),
        );
    }

    /**
     * @group issue22
     * @dataProvider provideInvalidTypeSymbols
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 131
     * @epxectedExceptionMessage Argument $symbol has invalid type
     */
    public function test_VerifySymbol_InvalidTypeSymbol_ThrowsException($symbol)
    {
        $this->_fsm->verifySymbol($symbol);
    }

    public function provideValidArguments()
    {
        return array(
            array(
                md5(uniqid()),
            ),
        );
    }

    /**
     * @group issue22
     * @dataProvider provideValidArguments
     * @expectedException RuntimeException
     * @expectedExceptionCode 111
     * @expectedExceptionMessage States are not set
     */
    public function test_VerifySymbol_IsInitializedReturnsFalse_ThrowsException($symbol)
    {
        $this->_fsm->expects($this->once())->method('isInitialized')->will($this->returnValue(false));
        $this->_fsm->verifySymbol($symbol);
    }

    protected function _getAlphabet($stateSet)
    {
        $alphabet = array();
        foreach ($stateSet as $state => $symbolSet) {
            foreach ($symbolSet as $symbol => $destination) {
                $alphabet[$symbol] = $symbol;
            }
        }
        $alphabet = array_values($alphabet);
        sort($alphabet);
        return $alphabet;
    }

    public function provideOutOfAlphabetSymbols()
    {
        $argumentSets = $this->provideValidStates();
        foreach ($argumentSets as &$argumentSet) {
            $alphabet = $this->_getAlphabet($argumentSet['stateSet']);
            do {
                $symbol = md5(uniqid());
            } while (in_array($symbol, $alphabet));
            $argumentSet = array(
                'stateSet' => $argumentSet['stateSet'],
                'symbol' => $symbol,
                'alphabet' => $alphabet,
            );
        }
        unset($argumentSet);
        return $argumentSets;
    }

    /**
     * @dataProvider provideOutOfAlphabetSymbols
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 132
     */
    public function test_VerifySymbol_SymbolIsOutOfAlphabet_ThrowsException($stateSet, $symbol, $alphabet)
    {
        $this->setStateSet($stateSet);
        $this->_fsm->expects($this->once())->method('isInitialized')->will($this->returnValue(true));
        try {
            $this->_fsm->verifySymbol($symbol);
        } catch (InvalidArgumentException $e) {
            $this->assertInvalidValueArgumentExceptionMessage($e, 'symbol');
            $alphabet = implode('","', $alphabet);
            $alphabet = "(\"$alphabet\")";
            $this->assertStringEndsWith("symbol \"$symbol\" is out of the alphabet $alphabet", $e->getMessage());
            throw $e;
        }
    }

    public function provideOutOfStateSymbols()
    {
        $stateSet = $this->_getBillingStateSet();
        return array(
            array(
                'stateSet' => $stateSet,
                'state' => 'CHECKOUT',
                'symbol' => 'pending',
            ),
            array(
                'stateSet' => $stateSet,
                'state' => 'INIT',
                'symbol' => 'void',
            ),
        );
    }

    /**
     * @dataProvider provideOutOfStateSymbols
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 133
     */
    public function test_VerifySymbol_SymbolIsOutOfState_ThrowsException($stateSet, $state, $symbol)
    {
        $this->setStateSet($stateSet);
        $this->setState($state);
        $this->_fsm->expects($this->once())->method('isInitialized')->will($this->returnValue(true));
        try {
            $this->_fsm->verifySymbol($symbol);
        } catch (InvalidArgumentException $e) {
            $this->assertInvalidValueArgumentExceptionMessage($e, 'symbol');
            $this->assertStringEndsWith("symbol \"$symbol\" is out of the state \"$state\"", $e->getMessage());
            throw $e;
        }
    }

    public function provideValidSymbols()
    {
        $argumentSets = array();
        $stateSets = array_map('array_shift', $this->provideValidStateSets());
        foreach ($stateSets as $stateSet) {
            foreach ($stateSet as $state => $symbolState) {
                foreach ($symbolState as $symbol => $destination) {
                    $argumentSets[] = array(
                        'stateSet' => $stateSet,
                        'state' => $state,
                        'symbol' => $symbol,
                    );
                }
            }
        }
        return $argumentSets;
    }

    /**
     * @dataProvider provideValidSymbols
     */
    public function test_VerifySymbol_ValidSymbol_ReturnsTrue($stateSet, $state, $symbol)
    {
        $this->setStateSet($stateSet);
        $this->setState($state);
        $this->_fsm->expects($this->once())->method('isInitialized')->will($this->returnValue(true));
        $result = $this->_fsm->verifySymbol($symbol);
        $this->assertTrue($result);
    }
}
