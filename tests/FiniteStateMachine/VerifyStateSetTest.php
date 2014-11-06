<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../FsmTestCase.php')));

/**
 * public function test_VerifyStateSet_InvalidTypeStateSet_ThrowsException
 * public function test_VerifyStateSet_EmptyStateSet_ThrowsException
 * public function test_VerifyStateSet_InvalidTypeState_ThrowsException
 * public function test_VerifyStateSet_InvalidTypeSymbolSet_ThrowsException
 * public function test_VerifyStateSet_FirstStateIsEmpty_ThrowsException
 * public function test_VerifyStateSet_NonFirstStateIsEmpty_DoesNotThrowException
 * public function test_VerifyStateSet_InvalidTypeSymbol_ThrowsException
 * public function test_VerifyStateSet_StateHasNoDefaultSymbol_DoesNotThrowException
 * public function test_VerifyStateSet_InvalidTypeDestination_ThrowsException
 * public function test_VerifyStateSet_DestinationHasNoState_ThrowsException
 * public function test_VerifyStateSet_DestinationRefersToAbsentState_ThrowsException
 * public function test_VerifyStateSet_DestinationHasInvalidTypeAction_ThrowsException
 * public function test_VerifyStateSet_DestinationRefersToAbsentMethod_ThrowsException
 * public function test_VerifyStateSet_DestinationRefersToNonPublicMethod_ThrowsException
 * public function test_VerifyStateSet_StateWithNoReferenceTo_ThrowsException
 * public function test_VerifyStateSet_ValidArguments_ReturnsTrue
 */
class Fsm_VerifyStateSetTest extends FsmTestCase
{
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
     * @dataProvider provideStateSetsWithInvalidTypeState
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 203
     */
    public function test_VerifyStateSet_InvalidTypeState_ThrowsException($stateSet)
    {
        try {
            $this->_fsm->verifyStateSet($stateSet);
        } catch (InvalidArgumentException $e) {
            $this->assertInvalidValueArgumentExceptionMessage($e, 'stateSet');
            $this->assertStringEndsWith('invalid type state', $e->getMessage());
            throw $e;
        }
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
     * @dataProvider provideStateSetsWithInvalidTypeSymbolSet
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 204
     */
    public function test_VerifyStateSet_InvalidTypeSymbolSet_ThrowsException($stateSet, $state)
    {
        try {
            $this->_fsm->verifyStateSet($stateSet);
        } catch (InvalidArgumentException $e) {
            $this->assertInvalidValueArgumentExceptionMessage($e, 'stateSet');
            $this->assertStringEndsWith("invalid type symbol set for state $state", $e->getMessage());
            throw $e;
        }
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
     * @dataProvider provideStateSetsWithEmptyFirstState
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 205
     */
    public function test_VerifyStateSet_FirstStateIsEmpty_ThrowsException($stateSet)
    {
        try {
            $this->_fsm->verifyStateSet($stateSet);
        } catch (InvalidArgumentException $e) {
            $this->assertInvalidValueArgumentExceptionMessage($e, 'stateSet');
            $this->assertStringEndsWith("first state is empty", $e->getMessage());
            throw $e;
        }
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
     * @dataProvider provideStateSetWithInvalidTypeSymbol
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 206
     */
    public function test_VerifyStateSet_InvalidTypeSymbol_ThrowsException($stateSet, $state)
    {
        try {
            $this->_fsm->verifyStateSet($stateSet);
        } catch (InvalidArgumentException $e) {
            $this->assertInvalidValueArgumentExceptionMessage($e, 'stateSet');
            $this->assertStringEndsWith("invalid type symbol for state $state", $e->getMessage());
            throw $e;
        }
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
     * @dataProvider provideStateSetsWithInvalidTypeDestination
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 207
     */
    public function test_VerifyStateSet_InvalidTypeDestination_ThrowsException($stateSet, $state, $symbol)
    {
        try {
            $this->_fsm->verifyStateSet($stateSet);
        } catch (InvalidArgumentException $e) {
            $this->assertInvalidValueArgumentExceptionMessage($e, 'stateSet');
            $this->assertStringEndsWith("invalid type destination for state $state and symbol $symbol", $e->getMessage());
            throw $e;
        }
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
     * @dataProvider provideStateSetsWithDestinationHasNoState
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 208
     */
    public function test_VerifyStateSet_DestinationHasNoState_ThrowsException($stateSet, $state, $symbol)
    {
        try {
            $this->_fsm->verifyStateSet($stateSet);
        } catch (InvalidArgumentException $e) {
            $this->assertInvalidValueArgumentExceptionMessage($e, 'stateSet');
            $this->assertStringEndsWith("destination has no state for state $state and symbol $symbol", $e->getMessage());
            throw $e;
        }
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
     * @dataProvider provideStateSetsWithDestinationRefersToAbsentState
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 209
     */
    public function test_VerifyStateSet_DestinationRefersToAbsentState_ThrowsException($stateSet, $state, $symbol, $absentState)
    {
        try {
            $this->_fsm->verifyStateSet($stateSet);
        } catch (InvalidArgumentException $e) {
            $this->assertInvalidValueArgumentExceptionMessage($e, 'stateSet');
            $this->assertStringEndsWith("destination refers to absent state $absentState for state $state and symbol $symbol", $e->getMessage());
            throw $e;
        }
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
     * @dataProvider provideStateSetsWithDestinationHasInvalidTypeAction
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 210
     */
    public function test_VerifyStateSet_DestinationHasInvalidTypeAction_ThrowsException($stateSet, $state, $symbol)
    {
        try {
            $this->_fsm->verifyStateSet($stateSet);
        } catch (InvalidArgumentException $e) {
            $this->assertInvalidValueArgumentExceptionMessage($e, 'stateSet');
            $this->assertStringEndsWith("destination has invalid type action for state $state and symbol $symbol", $e->getMessage());
            throw $e;
        }
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
     * @dataProvider provideStateSetsWithDestinationRefersToAbsentMethod
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 211
     */
    public function test_VerifyStateSet_DestinationRefersToAbsentMethod_ThrowsException($stateSet, $state, $symbol, $absentMethod)
    {
        try {
            $this->_fsm->verifyStateSet($stateSet);
        } catch (InvalidArgumentException $e) {
            $this->assertInvalidValueArgumentExceptionMessage($e, 'stateSet');
            $this->assertStringEndsWith("destination refers to absent method $absentMethod for state $state and symbol $symbol", $e->getMessage());
            throw $e;
        }
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
     * @dataProvider provideStateSetsWithDestinationRefersToNonPublicMethod
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 212
     */
    public function test_VerifyStateSet_DestinationRefersToNonPublicMethod_ThrowsException($stateSet, $state, $symbol, $method)
    {
        try {
            $this->_fsm->verifyStateSet($stateSet);
        } catch (InvalidArgumentException $e) {
            $this->assertInvalidValueArgumentExceptionMessage($e, 'stateSet');
            $this->assertStringEndsWith("destination refers to non-public method $method for state $state and symbol $symbol", $e->getMessage());
            throw $e;
        }
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
     * @dataProvider provideStateSetsWithStateWithNoReferenceTo
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 213
     */
    public function test_VerifyStateSet_StateWithNoReferenceTo_ThrowsException($stateSet, $state)
    {
        try {
            $this->_fsm->verifyStateSet($stateSet);
        } catch (InvalidArgumentException $e) {
            $this->assertInvalidValueArgumentExceptionMessage($e, 'stateSet');
            $this->assertStringEndsWith("there is a state $state with no reference to", $e->getMessage());
            throw $e;
        }
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
