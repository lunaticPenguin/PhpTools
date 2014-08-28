<?php

$runner->addTestsFromDirectory(__DIR__ . '/classes');

$cloverWriter = new atoum\writers\file(__DIR__.'/build/atoum.clover.xml');
$cloverReport = new atoum\reports\asynchronous\clover();
$cloverReport->addWriter($cloverWriter);
$runner->addReport($cloverReport);
