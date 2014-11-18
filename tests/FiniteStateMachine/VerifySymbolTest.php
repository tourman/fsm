<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../FsmTestCase.php')));

/**
 * public function test_VerifySymbol_InvalidTypeSymbol_ThrowsException()
 * public function test_VerifySymbol_IsInitializedReturnsFalse_ThrowsException()
 * public function test_VerifySymbol_SymbolIsOutOfAlphabet_ThrowsException()
 * public function test_VerifySymbol_SymbolIsOutOfAlphabet_ThrowsException_CertainKeys()
 * public function test_VerifySymbol_SymbolIsOutOfState_ThrowsException()
 * public function test_VerifySymbol_SymbolIsOutOfState_ThrowsException_CertainKeys()
 * public function test_VerifySymbol_ValidSymbol_ReturnsTrue()
 */
class Fsm_VerifySymbolTest extends FsmTestCase
{
    protected $_exceptionMessage;

    public function setUp()
    {
        $this->_fsm = $this->getMockBuilder(self::FSM_CLASS_NAME)->
            disableOriginalConstructor()->
            setMethods(array('isInitialized'))->
            getMock();
        $this->_exceptionMessage = null;
    }

    public function assertExceptionMessage($symbol, $key, $value)
    {
        if (is_null($this->_exceptionMessage)) {
            try {
                $this->_fsm->verifySymbol($symbol);
                $this->_exceptionMessage = '';
            } catch (Exception $e) {
                $this->_exceptionMessage = $e->getMessage();
            }
        }
        $regExp = preg_quote("$key $value", '/');
        $this->assertRegExp("/$regExp/", $this->_exceptionMessage);
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
        $stateSet = $this->_getBillingStateSet();
        $alphabet = '["*","checkout","completed","failed","pending","processing","void"]';
        return array(
            array(
                'stateSet' => $stateSet,
                'symbol' => '8d7e35631f830f2c5b9685450a2b8568',
                'alphabet' => $alphabet,
            ),
            array(
                'stateSet' => $stateSet,
                'symbol' => '',
                'alphabet' => $alphabet,
            ),
            array(
                'stateSet' => $stateSet,
                'symbol' => ' ',
                'alphabet' => $alphabet,
            ),
        );
    }

    /**
     * @group issue22
     * @dataProvider provideOutOfAlphabetSymbols
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 132
     * @expectedExceptionMessageRegExp /^Argument \$symbol has invalid value: symbol "[^"]*" is out of the alphabet \[("[^"]*",)*"[^"]*"\]$/
     */
    public function test_VerifySymbol_SymbolIsOutOfAlphabet_ThrowsException($stateSet, $symbol, $alphabet)
    {
        $this->setStateSet($stateSet);
        $this->_fsm->expects($this->once())->method('isInitialized')->will($this->returnValue(true));
        $this->_fsm->verifySymbol($symbol);
    }

    /**
     * @group issue22
     * @dataProvider provideOutOfAlphabetSymbols
     */
    public function test_VerifySymbol_SymbolIsOutOfAlphabet_ThrowsException_CertainKeys($stateSet, $symbol, $alphabet)
    {
        $this->setStateSet($stateSet);
        $this->_fsm->expects($this->once())->method('isInitialized')->will($this->returnValue(true));
        $this->assertExceptionMessage($symbol, 'symbol', "\"$symbol\"");
        $this->assertExceptionMessage($symbol, 'alphabet', $alphabet);
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
     * @group issue22
     * @dataProvider provideOutOfStateSymbols
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 133
     * @expectedExceptionMessageRegExp /^Argument \$symbol has invalid value: symbol "[^"]*" is out of the state "[^"]*"$/
     */
    public function test_VerifySymbol_SymbolIsOutOfState_ThrowsException($stateSet, $state, $symbol)
    {
        $this->setStateSet($stateSet);
        $this->setState($state);
        $this->_fsm->expects($this->once())->method('isInitialized')->will($this->returnValue(true));
        $this->_fsm->verifySymbol($symbol);
    }

    /**
     * @group issue22
     * @dataProvider provideOutOfStateSymbols
     */
    public function test_VerifySymbol_SymbolIsOutOfState_ThrowsException_CertainKeys($stateSet, $state, $symbol)
    {
        $this->setStateSet($stateSet);
        $this->setState($state);
        $this->_fsm->expects($this->once())->method('isInitialized')->will($this->returnValue(true));
        $this->assertExceptionMessage($symbol, 'symbol', "\"$symbol\"");
        $this->assertExceptionMessage($symbol, 'state', "\"$state\"");
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
