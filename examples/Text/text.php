<?php

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/../../src/FiniteStateMachine.php')));
require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/Text.php')));

$text = <<<TEXT
Hello, my name is
Anna. I'm very strong!
Bye!

TEXT;
$t = new Text();
$t->setStateSet();
$counter = 0;
while ($text) {
    $char = substr($text, 0, 1);
    $text = substr($text, 1);
    $t->process($char);
    $counter++;
    if ($counter == 10) {
        $log = $t->sleep();
        unset($t);
        $t = new Text();
        $t->setStateSet($log);
    }
}
$t->displayLog();
