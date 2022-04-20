<?php

require_once 'checkString.php';

use PHPUnit\Framework\TestCase;


class checkStringTest extends TestCase
{
    /**
     * @dataProvider providerCheckString
     */
    public function testCheckString($a, $b)
    {
        $this->assertEquals($a, checkString($b));
    }

    /**
     * @return array[]
     */
    public function providerCheckString()
    {
        return array(
            array(true, '45455'),
            array(false, '2228**55'),
            array(false, '4545**555444*55**'),
            array(true, '4545*555444*55*'),
        );
    }