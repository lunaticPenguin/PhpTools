<?php

namespace App\Plugins\Tools\Tests\Units;

$config = include __DIR__ . '/../../../app/config/config.php';
include __DIR__ . '/../../../app/config/loader.php';

use \atoum;
use App\Plugins\Tools\Security as S;

class Security extends atoum
{
    public function testShareANDBits()
    {
        $this->boolean(S::shareANDBits(1, 2))->isFalse();
        $this->boolean(S::shareANDBits(1, 5))->isTrue();
    }
}
