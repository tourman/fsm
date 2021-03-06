<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../FsmTestCase.php')));

/**
 * public function test_VerifyStateSet_InvalidTypeStateSet_ThrowsException
 * public function test_VerifyStateSet_EmptyStateSet_ThrowsException
 * public function test_VerifyStateSet_InvalidTypeState_ThrowsException
 * public function test_VerifyStateSet_InvalidTypeSymbolSet_ThrowsException
 * public function test_VerifyStateSet_InvalidTypeSymbolSet_ThrowsException_CertainKeys
 * public function test_VerifyStateSet_FirstStateIsEmpty_ThrowsException
 * public function test_VerifyStateSet_NonFirstStateIsEmpty_DoesNotThrowException
 * public function test_VerifyStateSet_InvalidTypeSymbol_ThrowsException
 * public function test_VerifyStateSet_InvalidTypeSymbol_ThrowsException_CertainKeys
 * public function test_VerifyStateSet_StateHasNoDefaultSymbol_DoesNotThrowException
 * public function test_VerifyStateSet_InvalidTypeDestination_ThrowsException
 * public function test_VerifyStateSet_InvalidTypeDestination_ThrowsException_CertainKeys
 * public function test_VerifyStateSet_DestinationHasNoState_ThrowsException
 * public function test_VerifyStateSet_DestinationHasNoState_ThrowsException_CertainKeys
 * public function test_VerifyStateSet_DestinationRefersToAbsentState_ThrowsException
 * public function test_VerifyStateSet_DestinationRefersToAbsentState_ThrowsException_CertainKeys
 * public function test_VerifyStateSet_DestinationHasInvalidTypeAction_ThrowsException
 * public function test_VerifyStateSet_DestinationHasInvalidTypeAction_ThrowsException_CertainKeys
 * public function test_VerifyStateSet_DestinationRefersToAbsentMethod_ThrowsException
 * public function test_VerifyStateSet_DestinationRefersToAbsentMethod_ThrowsException_CertainKeys
 * public function test_VerifyStateSet_DestinationRefersToNonPublicMethod_ThrowsException
 * public function test_VerifyStateSet_DestinationRefersToNonPublicMethod_ThrowsException_CertainKeys
 * public function test_VerifyStateSet_StateWithNoReferenceTo_ThrowsException
 * public function test_VerifyStateSet_StateWithNoReferenceTo_ThrowsException_CertainKeys
 * public function test_VerifyStateSet_ValidArguments_ReturnsTrue
 */
class Fsm_VerifyStateSetTest extends FsmTestCase
{
    protected $_exceptionMessage;

    public function setUp()
    {
        parent::setUp();
        $this->_exceptionMessage = null;
    }

    public function assertExceptionMessage($stateSet, $key, $value)
    {
        if (is_null($this->_exceptionMessage)) {
            try {
                $this->_fsm->verifyStateSet($stateSet);
                $this->_exceptionMessage = '';
            } catch (Exception $e) {
                $this->_exceptionMessage = $e->getMessage();
            }
        }
        $regExp = preg_quote("$key $value", '/');
        $this->assertRegExp("/$regExp/", $this->_exceptionMessage);
    }

    public function provideInvalidTypeStateSets()
    {
        return array(
            array(false),
            array(1),
            array(1.1),
            array('false'),
            array(new stdClass()),
            array(null),
        );
    }

    /**
     * @group issue2
     * @group issue22
     * @group issue22_exception_message
     * @dataProvider provideInvalidTypeStateSets
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 101
     * @expectedExceptionMessage Argument $stateSet has invalid type
     */
    public function test_VerifyStateSet_InvalidTypeStateSet_ThrowsException($stateSet)
    {
        $this->_fsm->verifyStateSet($stateSet);
    }

    public function provideEmptyStateSets()
    {
        return array(
            array(
                'stateSet' => array(),
            ),
        );
    }

    /**
     * @group issue2
     * @group issue22
     * @group issue22_exception_message
     * @dataProvider provideEmptyStateSets
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 202
     * @expectedExceptionMessage Argument $stateSet has invalid value
     */
    public function test_VerifyStateSet_EmptyStateSet_ThrowsException($stateSet)
    {
        $this->_fsm->verifyStateSet($stateSet);
    }

    public function provideStateSetsWithInvalidTypeState()
    {
        return array(
            array(
                'stateSet' => array(
                    0 => array(),
                ),
            ),
            array(
                'stateSet' => array(
                    false => array(),
                ),
            ),
        );
    }

    /**
     * @group issue2
     * @group issue22
     * @group issue22_exception_message
     * @dataProvider provideStateSetsWithInvalidTypeState
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 203
     * @expectedExceptionMessage Argument $stateSet has invalid value: invalid type state
     */
    public function test_VerifyStateSet_InvalidTypeState_ThrowsException($stateSet)
    {
        $this->_fsm->verifyStateSet($stateSet);
    }

    public function provideStateSetsWithInvalidTypeSymbolSet()
    {
        return array(
            array(
                'stateSet' => array(
                    'BOOL' => false,
                    'CLOSE' => array(
                    ),
                ),
                'state' => 'BOOL',
            ),
            array(
                'stateSet' => array(
                    'INTEGER' => 1,
                    'CLOSE' => array(
                    ),
                ),
                'state' => 'INTEGER',
            ),
            array(
                'stateSet' => array(
                    'DOUBLE' => 1.1,
                    'CLOSE' => array(
                    ),
                ),
                'state' => 'DOUBLE',
            ),
            array(
                'stateSet' => array(
                    'STRING' => 'false',
                    'CLOSE' => array(
                    ),
                ),
                'state' => 'STRING',
            ),
            array(
                'stateSet' => array(
                    'CLASS' => new stdClass(),
                    'CLOSE' => array(
                    ),
                ),
                'state' => 'CLASS',
            ),
            array(
                'stateSet' => array(
                    'NULL' => null,
                    'CLOSE' => array(
                    ),
                ),
                'state' => 'NULL',
            ),
        );
    }

    /**
     * @group issue2
     * @group issue22
     * @group issue22_exception_message
     * @dataProvider provideStateSetsWithInvalidTypeSymbolSet
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 204
     * @expectedExceptionMessageRegExp /^Argument \$stateSet has invalid value: invalid type symbol set for state "[^"]*"$/
     */
    public function test_VerifyStateSet_InvalidTypeSymbolSet_ThrowsException($stateSet, $state)
    {
        $this->_fsm->verifyStateSet($stateSet);
    }

    /**
     * @group issue22
     * @group issue22_exception_message
     * @dataProvider provideStateSetsWithInvalidTypeSymbolSet
     */
    public function test_VerifyStateSet_InvalidTypeSymbolSet_ThrowsException_CertainKeys($stateSet, $state)
    {
        $this->assertExceptionMessage($stateSet, 'state', "\"$state\"");
    }

    public function provideStateSetsWithEmptyFirstState()
    {
        return array(
            array(
                'stateSet' => array(
                    'INIT' => array(
                    ),
                ),
            ),
            array(
                'stateSet' => array(
                    'INIT' => array(
                    ),
                    'CLOSE' => array(
                        '*' => array(
                            'state' => 'CLOSE',
                        ),
                    ),
                ),
            ),
            array(
                'stateSet' => array(
                    'INIT' => array(
                    ),
                    'CLOSE' => array(
                        '*' => array(
                            'state' => 'CLOSE',
                            'action' => 'repeat',
                        ),
                        'retry' => array(
                            'state' => 'INIT',
                            'action' => 'retry',
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * @group issue2
     * @group issue22
     * @group issue22_exception_message
     * @dataProvider provideStateSetsWithEmptyFirstState
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 205
     * @expectedExceptionMessage Argument $stateSet has invalid value: first state is empty
     */
    public function test_VerifyStateSet_FirstStateIsEmpty_ThrowsException($stateSet)
    {
        $this->_fsm->verifyStateSet($stateSet);
    }

    public function provideStateSetsWithEmptyNonFirstState()
    {
        return array(
            array(
                'stateSet' => array(
                    'INIT' => array(
                        'close' => array(
                            'state' => 'CLOSE',
                            'action' => 'close',
                        ),
                    ),
                    'CLOSE' => array(
                    ),
                ),
            ),
            array(
                'stateSet' => array(
                    'INIT' => array(
                        'close' => array(
                            'state' => 'CLOSE',
                            'action' => 'close',
                        ),
                        'error' => array(
                            'state' => 'FAIL',
                        ),
                    ),
                    'FAIL' => array(
                        'continue' => array(
                            'state' => 'INIT',
                        ),
                    ),
                    'CLOSE' => array(
                    ),
                ),
            ),
        );
    }

    /**
     * @group issue2
     * @dataProvider provideStateSetsWithEmptyNonFirstState
     * @expectedException Exception
     * @expectedExceptionMessage 5a14dd7e909f307a5ce6009fb9a8c505
     */
    public function test_VerifyStateSet_NonFirstStateIsEmpty_DoesNotThrowException($stateSet)
    {
        $this->_fsm->verifyStateSet($stateSet);
        throw new Exception('5a14dd7e909f307a5ce6009fb9a8c505');
    }

    public function provideStateSetWithInvalidTypeSymbol()
    {
        return array(
            array(
                'stateSet' => array(
                    'INIT' => array(
                        '*' => array(
                            'state' => 'INIT',
                            'action' => 'retry',
                        ),
                    ),
                    'INTEGER' => array(
                        0 => array(
                            'state' => 'INIT',
                        ),
                    ),
                ),
                'state' => 'INTEGER',
            ),
            array(
                'stateSet' => array(
                    'INIT' => array(
                        '*' => array(
                            'state' => 'INIT',
                            'action' => 'retry',
                        ),
                    ),
                    'BOOL' => array(
                        false => array(
                            'state' => 'INIT',
                        ),
                    ),
                ),
                'state' => 'BOOL',
            ),
        );
    }

    /**
     * @group issue2
     * @group issue22
     * @group issue22_exception_message
     * @dataProvider provideStateSetWithInvalidTypeSymbol
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 206
     * @expectedExceptionMessageRegExp /^Argument \$stateSet has invalid value: invalid type symbol for state "[^"]*"$/
     */
    public function test_VerifyStateSet_InvalidTypeSymbol_ThrowsException($stateSet, $state)
    {
        $this->_fsm->verifyStateSet($stateSet);
    }

    /**
     * @group issue22
     * @group issue22_exception_message
     * @dataProvider provideStateSetWithInvalidTypeSymbol
     */
    public function test_VerifyStateSet_InvalidTypeSymbol_ThrowsException_CertainKeys($stateSet, $state)
    {
        $this->assertExceptionMessage($stateSet, 'state', "\"$state\"");
    }

    public function provideStateSetsWithoutDefaultSymbol()
    {
        return array(
            array(
                'stateSet' => array(
                    'INIT' => array(
                        'reinit' => array(
                            'state' => 'INIT',
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * @group issue2
     * @dataProvider provideStateSetsWithoutDefaultSymbol
     * @expectedException Exception
     * @expectedExceptionMessage 5a14dd7e909f307a5ce6009fb9a8c506
     */
    public function test_VerifyStateSet_StateHasNoDefaultSymbol_DoesNotThrowException($stateSet)
    {
        $this->_fsm->verifyStateSet($stateSet);
        throw new Exception('5a14dd7e909f307a5ce6009fb9a8c506');
    }

    public function provideStateSetsWithInvalidTypeDestination()
    {
        return array(
            array(
                'stateSet' => array(
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
                        'close' => false,
                    ),
                ),
                'state' => 'CHECKOUT',
                'symbol' => 'close',
            ),
            array(
                'stateSet' => array(
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
                        'close' => array(
                            'state' => 'CLOSE',
                            'action' => 'close',
                        ),
                    ),
                    'CLOSE' => array(
                        '*' => 1,
                    ),
                ),
                'state' => 'CLOSE',
                'symbol' => '*',
            ),
            array(
                'stateSet' => array(
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
                        'close' => array(
                            'state' => 'CLOSE',
                            'action' => 'close',
                        ),
                        'error' => array(
                            'state' => 'FAIL',
                            'action' => 'error',
                        ),
                    ),
                    'FAIL' => array(
                        '*' => 1.1,
                    ),
                    'CLOSE' => array(
                        'error' => array(
                            'state' => 'FAIL',
                            'action' => 'error',
                        ),
                    ),
                ),
                'state' => 'FAIL',
                'symbol' => '*',
            ),
            array(
                'stateSet' => array(
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
                        'close' => array(
                            'state' => 'CLOSE',
                            'action' => 'close',
                        ),
                        'error' => 'error',
                    ),
                    'FAIL' => array(
                        '*' => array(
                            'state' => 'FAIL',
                        ),
                    ),
                    'CLOSE' => array(
                        'error' => array(
                            'state' => 'FAIL',
                            'action' => 'error',
                        ),
                    ),
                ),
                'state' => 'CHECKOUT',
                'symbol' => 'error',
            ),
            array(
                'stateSet' => array(
                    'INIT' => array(
                        '*' => array(
                            'state' => 'INIT',
                        ),
                        'checkout' => new stdClass(),
                    ),
                    'CHECKOUT' => array(
                        'close' => array(
                            'state' => 'CLOSE',
                            'action' => 'close',
                        ),
                        'error' => array(
                            'state' => 'ERROR',
                            'action' => 'error',
                        )
                    ),
                    'FAIL' => array(
                        '*' => array(
                            'state' => 'FAIL',
                        ),
                    ),
                    'CLOSE' => array(
                        'error' => array(
                            'state' => 'FAIL',
                            'action' => 'error',
                        ),
                    ),
                ),
                'state' => 'INIT',
                'symbol' => 'checkout',
            ),
            array(
                'stateSet' => array(
                    'INIT' => array(
                        '*' => array(
                            'state' => 'INIT',
                        ),
                        'checkout' => null,
                    ),
                    'CHECKOUT' => array(
                        'close' => array(
                            'state' => 'CLOSE',
                            'action' => 'close',
                        ),
                        'error' => array(
                            'state' => 'ERROR',
                            'action' => 'error',
                        )
                    ),
                    'FAIL' => array(
                        '*' => array(
                            'state' => 'FAIL',
                        ),
                    ),
                    'CLOSE' => array(
                        'error' => array(
                            'state' => 'FAIL',
                            'action' => 'error',
                        ),
                    ),
                ),
                'state' => 'INIT',
                'symbol' => 'checkout',
            ),
        );
    }

    /**
     * @group issue2
     * @group issue22
     * @group issue22_exception_message
     * @dataProvider provideStateSetsWithInvalidTypeDestination
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 207
     * @expectedExceptionMessageRegExp /^Argument \$stateSet has invalid value: invalid type destination for state "[^"]*" and symbol "[^"]*"$/
     */
    public function test_VerifyStateSet_InvalidTypeDestination_ThrowsException($stateSet, $state, $symbol)
    {
        $this->_fsm->verifyStateSet($stateSet);
    }

    /**
     * @group issue22
     * @group issue22_exception_message
     * @dataProvider provideStateSetsWithInvalidTypeDestination
     */
    public function test_VerifyStateSet_InvalidTypeDestination_ThrowsException_CertainKeys($stateSet, $state, $symbol)
    {
        $this->assertExceptionMessage($stateSet, 'state', "\"$state\"");
        $this->assertExceptionMessage($stateSet, 'symbol', "\"$symbol\"");
    }

    public function provideStateSetsWithDestinationHasNoState()
    {
        return array(
            array(
                'stateSet' => array(
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
                        'close' => array(
                            'action' => 'close',
                        ),
                        'error' => array(
                            'state' => 'ERROR',
                            'action' => 'error',
                        )
                    ),
                    'FAIL' => array(
                        '*' => array(
                            'state' => 'FAIL',
                        ),
                    ),
                    'CLOSE' => array(
                        'error' => array(
                            'state' => 'FAIL',
                            'action' => 'error',
                        ),
                    ),
                ),
                'state' => 'CHECKOUT',
                'symbol' => 'close',
            ),
        );
    }

    /**
     * @group issue2
     * @group issue22
     * @group issue22_exception_message
     * @dataProvider provideStateSetsWithDestinationHasNoState
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 208
     * @expectedExceptionMessageRegExp /^Argument \$stateSet has invalid value: destination has no state for state "[^"]*" and symbol "[^"]*"$/
     */
    public function test_VerifyStateSet_DestinationHasNoState_ThrowsException($stateSet, $state, $symbol)
    {
        $this->_fsm->verifyStateSet($stateSet);
    }

    /**
     * @group issue22
     * @group issue22_exception_message
     * @dataProvider provideStateSetsWithDestinationHasNoState
     */
    public function test_VerifyStateSet_DestinationHasNoState_ThrowsException_CertainKeys($stateSet, $state, $symbol)
    {
        $this->assertExceptionMessage($stateSet, 'state', "\"$state\"");
        $this->assertExceptionMessage($stateSet, 'symbol', "\"$symbol\"");
    }

    public function provideStateSetsWithDestinationRefersToAbsentState()
    {
        return array(
            array(
                'stateSet' => array(
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
                        'close' => array(
                            'state' => 'SOMETHING',
                            'action' => 'close',
                        ),
                        'error' => array(
                            'state' => 'ERROR',
                            'action' => 'error',
                        )
                    ),
                    'FAIL' => array(
                        '*' => array(
                            'state' => 'FAIL',
                        ),
                    ),
                    'CLOSE' => array(
                        'error' => array(
                            'state' => 'FAIL',
                            'action' => 'error',
                        ),
                    ),
                ),
                'state' => 'CHECKOUT',
                'symbol' => 'close',
                'absentState' => 'SOMETHING',
            ),
        );
    }

    /**
     * @group issue2
     * @group issue22
     * @group issue22_exception_message
     * @dataProvider provideStateSetsWithDestinationRefersToAbsentState
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 209
     * @expectedExceptionMessageRegExp /^Argument \$stateSet has invalid value: destination refers to absent state "[^"]*" for state "[^"]*" and symbol "[^"]*"$/
     */
    public function test_VerifyStateSet_DestinationRefersToAbsentState_ThrowsException($stateSet, $state, $symbol, $absentState)
    {
        $this->_fsm->verifyStateSet($stateSet);
    }

    /**
     * @group issue22
     * @group issue22_exception_message
     * @dataProvider provideStateSetsWithDestinationRefersToAbsentState
     */
    public function test_VerifyStateSet_DestinationRefersToAbsentState_ThrowsException_CertainKeys($stateSet, $state, $symbol, $absentState)
    {
        $this->assertExceptionMessage($stateSet, 'state', "\"$state\"");
        $this->assertExceptionMessage($stateSet, 'symbol', "\"$symbol\"");
        $this->assertExceptionMessage($stateSet, 'absent state', "\"$absentState\"");
    }

    public function provideStateSetsWithDestinationHasInvalidTypeAction()
    {
        return array(
            array(
                'stateSet' => array(
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
                        'close' => array(
                            'state' => 'CHECKOUT',
                            'action' => 'close',
                        ),
                        'error' => array(
                            'state' => 'FAIL',
                            'action' => false,
                        )
                    ),
                    'FAIL' => array(
                        '*' => array(
                            'state' => 'FAIL',
                        ),
                    ),
                    'CLOSE' => array(
                        'error' => array(
                            'state' => 'FAIL',
                            'action' => 'error',
                        ),
                    ),
                ),
                'state' => 'CHECKOUT',
                'symbol' => 'error',
            ),
            array(
                'stateSet' => array(
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
                        'close' => array(
                            'state' => 'CHECKOUT',
                            'action' => 1,
                        ),
                        'error' => array(
                            'state' => 'FAIL',
                            'action' => 'error',
                        )
                    ),
                    'FAIL' => array(
                        '*' => array(
                            'state' => 'FAIL',
                        ),
                    ),
                    'CLOSE' => array(
                        'error' => array(
                            'state' => 'FAIL',
                            'action' => 'error',
                        ),
                    ),
                ),
                'state' => 'CHECKOUT',
                'symbol' => 'close',
            ),
            array(
                'stateSet' => array(
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
                        'close' => array(
                            'state' => 'CHECKOUT',
                            'action' => 'close',
                        ),
                        'error' => array(
                            'state' => 'FAIL',
                            'action' => 'error',
                        )
                    ),
                    'FAIL' => array(
                        '*' => array(
                            'state' => 'FAIL',
                            'action' => 1.1,
                        ),
                    ),
                    'CLOSE' => array(
                        'error' => array(
                            'state' => 'FAIL',
                            'action' => 'error',
                        ),
                    ),
                ),
                'state' => 'FAIL',
                'symbol' => '*',
            ),
            array(
                'stateSet' => array(
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
                        'close' => array(
                            'state' => 'CHECKOUT',
                            'action' => 'close',
                        ),
                        'error' => array(
                            'state' => 'FAIL',
                            'action' => 'error',
                        )
                    ),
                    'FAIL' => array(
                        '*' => array(
                            'state' => 'FAIL',
                        ),
                    ),
                    'CLOSE' => array(
                        'error' => array(
                            'state' => 'FAIL',
                            'action' => array(),
                        ),
                    ),
                ),
                'state' => 'CLOSE',
                'symbol' => 'error',
            ),
            array(
                'stateSet' => array(
                    'INIT' => array(
                        '*' => array(
                            'state' => 'INIT',
                            'action' => new stdClass(),
                        ),
                        'checkout' => array(
                            'state' => 'CHECKOUT',
                            'action' => 'checkout',
                        ),
                    ),
                    'CHECKOUT' => array(
                        'close' => array(
                            'state' => 'CHECKOUT',
                            'action' => 'close',
                        ),
                        'error' => array(
                            'state' => 'FAIL',
                            'action' => 'error',
                        )
                    ),
                    'FAIL' => array(
                        '*' => array(
                            'state' => 'FAIL',
                        ),
                    ),
                    'CLOSE' => array(
                        'error' => array(
                            'state' => 'FAIL',
                            'action' => 'error',
                        ),
                    ),
                ),
                'state' => 'INIT',
                'symbol' => '*',
            ),
            array(
                'stateSet' => array(
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
                        'close' => array(
                            'state' => 'CHECKOUT',
                            'action' => 'close',
                        ),
                        'error' => array(
                            'state' => 'FAIL',
                            'action' => 'error',
                        )
                    ),
                    'FAIL' => array(
                        '*' => array(
                            'state' => 'FAIL',
                        ),
                    ),
                    'CLOSE' => array(
                        'error' => array(
                            'state' => 'FAIL',
                            'action' => null,
                        ),
                    ),
                ),
                'state' => 'CLOSE',
                'symbol' => 'error',
            ),
        );
    }

    /**
     * @group issue2
     * @group issue22
     * @group issue22_exception_message
     * @dataProvider provideStateSetsWithDestinationHasInvalidTypeAction
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 210
     * @expectedExceptionMessageRegExp /^Argument \$stateSet has invalid value: destination has invalid type action for state "[^"]*" and symbol "[^"]*"$/
     */
    public function test_VerifyStateSet_DestinationHasInvalidTypeAction_ThrowsException($stateSet, $state, $symbol)
    {
        $this->_fsm->verifyStateSet($stateSet);
    }

    /**
     * @group issue22
     * @group issue22_exception_message
     * @dataProvider provideStateSetsWithDestinationHasInvalidTypeAction
     */
    public function test_VerifyStateSet_DestinationHasInvalidTypeAction_ThrowsException_CertainKeys($stateSet, $state, $symbol)
    {
        $this->assertExceptionMessage($stateSet, 'state', "\"$state\"");
        $this->assertExceptionMessage($stateSet, 'symbol', "\"$symbol\"");
    }

    public function provideStateSetsWithDestinationRefersToAbsentMethod()
    {
        return array(
            array(
                'stateSet' => array(
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
                        'close' => array(
                            'state' => 'CHECKOUT',
                            'action' => 'ba7c8008a3b9386afe9f558227f104b1',
                        ),
                        'error' => array(
                            'state' => 'FAIL',
                            'action' => 'error',
                        )
                    ),
                    'FAIL' => array(
                        '*' => array(
                            'state' => 'FAIL',
                        ),
                    ),
                    'CLOSE' => array(
                        'error' => array(
                            'state' => 'FAIL',
                        ),
                    ),
                ),
                'state' => 'CHECKOUT',
                'symbol' => 'close',
                'absentMethod' => 'ba7c8008a3b9386afe9f558227f104b1',
            ),
        );
    }

    /**
     * @group issue2
     * @group issue22
     * @group issue22_exception_message
     * @dataProvider provideStateSetsWithDestinationRefersToAbsentMethod
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 211
     * @expectedExceptionMessageRegExp /^Argument \$stateSet has invalid value: destination refers to absent method "[^"]*" for state "[^"]*" and symbol "[^"]*"$/
     */
    public function test_VerifyStateSet_DestinationRefersToAbsentMethod_ThrowsException($stateSet, $state, $symbol, $absentMethod)
    {
        $this->_fsm->verifyStateSet($stateSet);
    }

    /**
     * @group issue22
     * @group issue22_exception_message
     * @dataProvider provideStateSetsWithDestinationRefersToAbsentMethod
     */
    public function test_VerifyStateSet_DestinationRefersToAbsentMethod_ThrowsException_CertainKeys($stateSet, $state, $symbol, $absentMethod)
    {
        $this->assertExceptionMessage($stateSet, 'state', "\"$state\"");
        $this->assertExceptionMessage($stateSet, 'symbol', "\"$symbol\"");
        $this->assertExceptionMessage($stateSet, 'absent method', "\"$absentMethod\"");
    }

    public function provideStateSetsWithDestinationRefersToNonPublicMethod()
    {
        return array(
            array(
                'stateSet' => array(
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
                        'close' => array(
                            'state' => 'CLOSE',
                            'action' => '_close',
                        ),
                        'error' => array(
                            'state' => 'FAIL',
                            'action' => 'error',
                        ),
                    ),
                    'FAIL' => array(
                        '*' => array(
                            'state' => 'FAIL',
                        ),
                    ),
                    'CLOSE' => array(
                        'error' => array(
                            'state' => 'FAIL',
                        ),
                    ),
                ),
                'state' => 'CHECKOUT',
                'symbol' => 'close',
                'method' => '_close',
            ),
        );
    }

    /**
     * @group issue2
     * @group issue22
     * @group issue22_exception_message
     * @dataProvider provideStateSetsWithDestinationRefersToNonPublicMethod
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 212
     * @expectedExceptionMessageRegExp /^Argument \$stateSet has invalid value: destination refers to non-public method "[^"]*" for state "[^"]*" and symbol "[^"]*"$/
     */
    public function test_VerifyStateSet_DestinationRefersToNonPublicMethod_ThrowsException($stateSet, $state, $symbol, $method)
    {
        $this->_fsm->verifyStateSet($stateSet);
    }

    /**
     * @group issue22
     * @group issue22_exception_message
     * @dataProvider provideStateSetsWithDestinationRefersToNonPublicMethod
     */
    public function test_VerifyStateSet_DestinationRefersToNonPublicMethod_ThrowsException_CertainKeys($stateSet, $state, $symbol, $method)
    {
        $this->assertExceptionMessage($stateSet, 'state', "\"$state\"");
        $this->assertExceptionMessage($stateSet, 'symbol', "\"$symbol\"");
        $this->assertExceptionMessage($stateSet, 'method', "\"$method\"");
    }

    public function provideStateSetsWithStateWithNoReferenceTo()
    {
        return array(
            array(
                'stateSet' => array(
                    'INIT' => array(
                        '*' => array(
                            'action' => 'close',
                            'state' => 'CLOSE',
                        ),
                        'close' => array(
                            'action' => 'close',
                            'state' => 'CLOSE',
                        ),
                    ),
                    'CLOSE' => array(
                        '*' => array(
                            'state' => 'CLOSE',
                        ),
                    ),
                    'EXTRA_STATE' => array(
                        '*' => array(
                            'state' => 'CLOSE',
                        ),
                    ),
                ),
                'state' => 'EXTRA_STATE',
            ),
            array(
                'stateSet' => array(
                    'INIT' => array(
                        '*' => array(
                            'action' => 'close',
                            'state' => 'CLOSE',
                        ),
                        'close' => array(
                            'action' => 'close',
                            'state' => 'CLOSE',
                        ),
                    ),
                    'EXTRA_STATE_1' => array(
                        '*' => array(
                            'state' => 'CLOSE',
                        ),
                    ),
                    'CLOSE' => array(
                        '*' => array(
                            'state' => 'CLOSE',
                        ),
                    ),
                    'EXTRA_STATE_2' => array(
                        '*' => array(
                            'state' => 'CLOSE',
                        ),
                    ),
                ),
                'state' => 'EXTRA_STATE_1',
            ),
        );
    }

    /**
     * @group issue2
     * @group issue22
     * @group issue22_exception_message
     * @dataProvider provideStateSetsWithStateWithNoReferenceTo
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 213
     * @expectedExceptionMessageRegExp /^Argument \$stateSet has invalid value: there is a state "[^"]*" with no reference to$/
     */
    public function test_VerifyStateSet_StateWithNoReferenceTo_ThrowsException($stateSet, $state)
    {
        $this->_fsm->verifyStateSet($stateSet);
    }

    /**
     * @group issue22
     * @group issue22_exception_message
     * @dataProvider provideStateSetsWithStateWithNoReferenceTo
     */
    public function test_VerifyStateSet_StateWithNoReferenceTo_ThrowsException_CertainKeys($stateSet, $state)
    {
        $this->assertExceptionMessage($stateSet, 'state', "\"$state\"");
    }

    public function provideInvalidTypeArguments()
    {
        return array(
            array(false),
            array(1),
            array(1.1),
            array('false'),
            array(new stdClass()),
            array(null),
        );
    }

    /**
     * @dataProvider provideValidStateSets
     */
    public function test_VerifyStateSet_ValidArguments_ReturnsTrue($stateSet)
    {
        $result = $this->_fsm->verifyStateSet($stateSet);
        $this->assertTrue($result);
    }
}
