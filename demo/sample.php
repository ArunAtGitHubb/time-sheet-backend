<?php

include_once 'FileMaker.php';

use airmoi\FileMaker\FileMaker;
use airmoi\FileMaker\FileMakerException;
use airmoi\FileMaker\FileMakerValidationException;

require __DIR__ . '/../autoloader.php';

try {
    $fm = new FileMaker('Tigeen_TimeSheet_web', 'fm.tigeensolutions.com', 'tigeen_web', 'webtigeen_@123');
} catch (FileMakerException $e) {
    echo PHP_EOL;
    echo "EXCEPTION :" . PHP_EOL;
    echo "  - At :" . $e->getFile() . ' line ' . $e->getLine() . PHP_EOL;
    echo "  - Code :" . $e->getCode() . PHP_EOL;
    echo "  - Message :" . $e->getMessage() . PHP_EOL;
    echo "  - Stack :" . $e->getTraceAsString() . PHP_EOL;
} catch (Exception $e) {
    echo PHP_EOL;
    echo "EXCEPTION :" . PHP_EOL;
    echo "  - At :" . $e->getFile() . ' line ' . $e->getLine() . PHP_EOL;
    echo "  - Code :" . $e->getCode() . PHP_EOL;
    echo "  - Message :" . $e->getMessage() . PHP_EOL;
    echo "  - Stack :" . $e->getTraceAsString() . PHP_EOL;
}
