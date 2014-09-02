<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../FsmTestCase.php')));

/**
 * public function test_VerifyStateSet_InvalidTypeStateSet_ThrowsException
 * public function test_VerifyStateSet_EmptyStateSet_ThrowsException
 * public function test_VerifyStateSet_InvalidTypeState_ThrowsException
 * public function test_VerifyStateSet_FirstStateIsEmpty_ThrowsException
 * public function test_VerifyStateSet_NonFirstStateIsEmpty_DoesNotThrowException
 * public function test_VerifyStateSet_InvalidTypeSymbolSet_ThrowsException
 * public function test_VerifyStateSet_InvalidTypeSymbol_ThrowsException
 * public function test_VerifyStateSet_StateHasNoDefaultSymbol_DoesNotThrowException
 * public function test_VerifyStateSet_InvalidTypeDestination_ThrowsException
 * public function test_VerifyStateSet_DestinationHasNoState_ThrowsException
 * public function test_VerifyStateSet_DestinationRefersToAbsentState_ThrowsException
 * public function test_VerifyStateSet_DestinationRefetsToAbsentMethod_ThrowsException
 * public function test_VerifyStateSet_DestinationRefersToNonpublicMethod_ThrowsException
 * public function test_VerifyStateSet_ValidArguments_ReturnsTrue
 */
/**
 * public function test_VerifyStateSet_InvalidTypeArguments_ThrowsException
 * public function test_VerifyStateSet_InvalidValueArguments_ThrowsException() //Check for structure
 * public function test_VerifyStateSet_StateDoesNotHaveDefaultSymbol_DoesNotThrowException()
 * public function test_VerifyStateSet_SymbolRefersToAbsentState_ThrowsException()
 * public function test_VerifyStateSet_SymbolRefersToAbsentMethod_ThrowsException()
 * public function test_VerifyStateSet_SymbolRefersToNonpublicMethod_ThrowsException()
 * public function test_VerifyStateSet_SymbolDoesNotHaveState_ThrowsException()
 * public function test_VerifyStateSet_StateWithNoReferenceTo_ThrowsException()
 * public function test_VerifyStateSet_EmptyState_DoesNotThrowException()
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
     * @dataProvider provideInvalidTypeStateSets
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 101
     */
    public function test_VerifyStateSet_InvalidTypeStateSet_ThrowsException($stateSet)
    {
        try {
            $this->_fsm->verifyStateSet($stateSet);
        } catch (InvalidArgumentException $e) {
            $this->assertInvalidTypeArgumentExceptionMessage($e, 'stateSet');
            throw $e;
        }
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
     * @dataProvider provideEmptyStateSets
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 202
     */
    public function test_VerifyStateSet_EmptyStateSet_ThrowsException($stateSet)
    {
        try {
            $this->_fsm->verifyStateSet($stateSet);
        } catch (InvalidArgumentException $e) {
            $this->assertInvalidValueArgumentExceptionMessage($e, 'stateSet');
            throw $e;
        }
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
     * @dataProvider provideInvalidTypeArguments
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 101
     */
    public function test_VerifyStateSet_InvalidTypeArguments_ThrowsException($stateSet)
    {
        try {
            $this->_fsm->verifyStateSet($stateSet);
        } catch (InvalidArgumentException $e) {
            $this->assertInvalidTypeArgumentExceptionMessage($e, 'stateSet');
            throw $e;
        }
    }

    public function provideInvalidValueStateSets()
    {
        /*
                //Right content
                array(
                    'INIT' => array(
                        '*' => array(
                            'state' => 'INIT',
                        ),
                    ),
                ),
        */
        return array(
            array(
                'stateSet' => array(),
            ),
            array(
                'stateSet' => array(
                    1,
                    2,
                    3,
                ),
            ),
            array(
                'stateSet' => array(
                    array(),
                ),
            ),
            array(
                'stateSet' => array(
                    array(
                        1,
                        2,
                        3,
                    ),
                ),
            ),
            array(
                'stateSet' => array(
                    array(
                        array(),
                    ),
                ),
            ),
        );
    }

    /**
     * This method tests for a proper structure
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 102
     * @dataProvider provideInvalidValueStateSets
     */
    public function test_VerifyStateSet_InvalidValueArguments_ThrowsException($stateSet)
    {
        try {
            $this->_fsm->verifyStateSet($stateSet);
        } catch (InvalidArgumentException $e) {
            $this->assertInvalidValueArgumentExceptionMessage($e, 'stateSet');
            throw $e;
        }
    }

    public function provideStateSetsWithStateDoesNotHaveDefaultSymbol()
    {
        //There is INIT state does not have default symbol *
        return array(
            array(
                array(
                    'INIT' => array(
                        'close' => array(
                            'action' => 'close',
                            'state' => 'CLOSE',
                        ),
                    ),
                    'CLOSE' => array(
                        '*' => array(
                            'state' => 'INIT',
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * @dataProvider provideStateSetsWithStateDoesNotHaveDefaultSymbol
     * @expectedException Exception
     * @expectedExceptionMessage 93400d78ee68c6f379834cf07b5f478a
     */
    public function test_VerifyStateSet_StateDoesNotHaveDefaultSymbol_DoesNotThrowException($stateSet)
    {
        $this->_fsm->verifyStateSet($stateSet);
        throw new Exception('93400d78ee68c6f379834cf07b5f478a');
    }

    public function provideStateSetsWithSymbolRefersToAbsentState()
    {
        return array(
            array(
                array(
                    'INIT' => array(
                        '*' => array(
                            'action' => 'close',
                            'state' => 'NO_STATE',
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
                ),
            ),
            array(
                array(
                    'INIT' => array(
                        '*' => array(
                            'action' => 'close',
                            'state' => 'CLOSE',
                        ),
                        'close' => array(
                            'action' => 'close',
                            'state' => 'NO_STATE',
                        ),
                    ),
                    'CLOSE' => array(
                        '*' => array(
                            'state' => 'CLOSE',
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 121
     * @dataProvider provideStateSetsWithSymbolRefersToAbsentState
     */
    public function test_VerifyStateSet_SymbolRefersToAbsentState_ThrowsException($stateSet)
    {
        $this->_fsm->verifyStateSet($stateSet);
    }

    public function provideStateSetsWithSymbolRefersToAbsentMethod()
    {
        return array(
            array(
                array(
                    'INIT' => array(
                        '*' => array(
                            'action' => 'absentMethod',
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
                        'init' => array(
                            'state' => 'INIT',
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 122
     * @dataProvider provideStateSetsWithSymbolRefersToAbsentMethod
     */
    public function test_VerifyStateSet_SymbolRefersToAbsentMethod_ThrowsException($stateSet)
    {
        $this->_fsm->verifyStateSet($stateSet);
    }

    public function provideStateSetsWithSymbolRefersToNonpublicMethod()
    {
        return array(
            array(
                array(
                    'INIT' => array(
                        '*' => array(
                            'action' => '_close',
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
                        'init' => array(
                            'state' => 'INIT',
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 123
     * @dataProvider provideStateSetsWithSymbolRefersToNonpublicMethod
     */
    public function test_VerifyStateSet_SymbolRefersToNonpublicMethod_ThrowsException($stateSet)
    {
        $this->_fsm->verifyStateSet($stateSet);
    }

    public function provideStateSetsWithSymbolDoesNotHaveState()
    {
        return array(
            array(
                array(
                    'INIT' => array(
                        '*' => array(
                            'action' => 'close',
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
                ),
            ),
            array(
                array(
                    'INIT' => array(
                        '*' => array(
                            'action' => 'close',
                            'state' => 'CLOSE',
                        ),
                        'close' => array(
                            'action' => 'close',
                        ),
                    ),
                    'CLOSE' => array(
                        '*' => array(
                            'state' => 'CLOSE',
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 124
     * @dataProvider provideStateSetsWithSymbolDoesNotHaveState
     */
    public function test_VerifyStateSet_SymbolDoesNotHaveState_ThrowsException($stateSet)
    {
        $this->_fsm->verifyStateSet($stateSet);
    }

    public function provideStateSetsWithStateWithNoReferenceTo()
    {
        return array(
            array(
                array(
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
            ),
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 125
     * @dataProvider provideStateSetsWithStateWithNoReferenceTo
     */
    public function test_VerifyStateSet_StateWithNoReferenceTo_ThrowsException($stateSet)
    {
        $this->_fsm->verifyStateSet($stateSet);
    }

    public function provideStateSetsWithEmptyState()
    {
        return array(
            array(
                array(
                    'INIT' => array(
                        'close' => array(
                            'state' => 'CLOSE',
                            'action' => 'close',
                        ),
                    ),
                    //Final state
                    'CLOSE' => array(
                    ),
                ),
            ),
            array(
                array(
                    'INIT' => array(
                        'close' => array(
                            'state' => 'CLOSE',
                            'action' => 'close',
                        ),
                        'error' => array(
                            'state' => 'ERROR',
                        ),
                    ),
                    //2 final states
                    'CLOSE' => array(
                    ),
                    'ERROR' => array(
                    ),
                ),
            ),
        );
    }

    /**
     * @dataProvider provideStateSetsWithEmptyState
     * @expectedException Exception
     * @expectedExceptionMessage 5a14dd7e909f307a5ce6009fb9a8c505
     */
    public function test_VerifyStateSet_EmptyState_DoesNotThrowException($stateSet)
    {
        $this->_fsm->verifyStateSet($stateSet);
        throw new Exception('5a14dd7e909f307a5ce6009fb9a8c505');
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
