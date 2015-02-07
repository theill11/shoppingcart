<?php
/**
 * @author: Johnny Theill <j.theill@gmail.com>
 * @date: 07-02-2015 01:20
 */

namespace Theill11\Tests\Cart\Storage;

use Theill11\Cart\Storage\CookieStorage;

class CookieStorageTest extends \PHPUnit_Framework_TestCase
{
    protected function getRequestMock()
    {
        return $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')->getMock();
    }
    protected function getResponseMock()
    {
        return $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')->getMock();
    }

    public function testCanInstantiate()
    {
        $requestMock = $this->getRequestMock();
        $responseMock = $this->getResponseMock();
        $storage = new CookieStorage($requestMock, $responseMock);
        $this->assertInstanceOf('Theill11\Cart\Storage\CookieStorage', $storage);
    }
}
