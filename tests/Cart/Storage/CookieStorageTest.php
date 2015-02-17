<?php
/**
 * @author: Johnny Theill <j.theill@gmail.com>
 * @date: 07-02-2015 01:20
 */

namespace Theill11\Tests\Cart\Storage;

use Theill11\Cart\Item;
use Theill11\Cart\Storage\CookieStorage;

class CookieStorageTest extends \PHPUnit_Framework_TestCase
{
    public $name = '_test-name';

    protected function getRequestMock()
    {
        $bagMock = $this->getBagMock();
        $mock = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')->getMock();
        $mock->cookies = $bagMock;
        return $mock;
    }
    protected function getResponseMock()
    {
        return $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')->getMock();
    }

    protected function getBagMock()
    {
        return $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')->getMock();
    }

    protected function getHeaderMock()
    {
        return $this->getMockBuilder('Symfony\Component\HttpFoundation\ResponseHeaderBag')->getMock();
    }

    public function testCanInstantiate()
    {
        $requestMock = $this->getRequestMock();
        $responseMock = $this->getResponseMock();
        $storage = new CookieStorage($requestMock, $responseMock, $this->name);
        $this->assertInstanceOf('Theill11\Cart\Storage\CookieStorage', $storage);
    }

    public function testGet()
    {
        $item1 = new Item(['id' => 1, 'name' => 'Test item 1', 'quantity' => 10, 'price' => 123.45]);
        $item2 = new Item(['id' => 2, 'name' => 'Test item 2', 'quantity' => 10, 'price' => 123.45]);

        $bagMock = $this->getBagMock();
        $bagMock
            ->expects($this->exactly(4))
            ->method('get')
            ->with($this->name)
            ->willReturnOnConsecutiveCalls(
                serialize([1 => $item1]),
                serialize([2 => $item2]),
                serialize([1 => new \stdClass()]),
                serialize([1 => $item1, 2 => $item2])
            );
        $requestMock = $this->getRequestMock();
        $requestMock->cookies = $bagMock;

        $responseMock = $this->getResponseMock();

        $storage = new CookieStorage($requestMock, $responseMock, $this->name);

        $this->assertNull($storage->get(null));
        $this->assertNull($storage->get(0));
        $this->assertNull($storage->get(1));
        $this->assertInstanceOf('Theill11\Cart\ItemInterface', $storage->get(2));
    }

    public function testRemove()
    {
        $item1 = new Item(['id' => 1, 'name' => 'Test item 1', 'quantity' => 10, 'price' => 123.45]);
        $item2 = new Item(['id' => 2, 'name' => 'Test item 2', 'quantity' => 10, 'price' => 123.45]);

        $bagMock = $this->getBagMock();
        $bagMock->expects($this->any())
            ->method('get')
            ->with($this->name)
            ->willReturnOnConsecutiveCalls(
                serialize([1 => $item1]),
                serialize([2 => $item2]),
                serialize([1 => new \stdClass()]),
                serialize([1 => $item1, 2 => $item2])
            );

        $headerMock = $this->getHeaderMock();
        $headerMock
            ->expects($this->once())
            ->method('setCookie')
            ->with($this->isInstanceOf('Symfony\Component\HttpFoundation\Cookie'));

        $requestMock = $this->getRequestMock();
        $requestMock->cookies = $bagMock;

        $responseMock = $this->getResponseMock();
        $responseMock->headers = $headerMock;

        $storage = new CookieStorage($requestMock, $responseMock, $this->name);

        $this->assertFalse($storage->remove(null));
        $this->assertFalse($storage->remove(1));
        $this->assertFalse($storage->remove(1));
        $this->assertTrue($storage->remove(1));
    }

    public function testHas()
    {
        $item1 = new Item(['id' => 1, 'name' => 'Test item 1', 'quantity' => 10, 'price' => 123.45]);
        $item2 = new Item(['id' => 2, 'name' => 'Test item 2', 'quantity' => 10, 'price' => 123.45]);

        $bagMock = $this->getBagMock();
        $bagMock->expects($this->any())
            ->method('get')
            ->with($this->name)
            ->willReturnOnConsecutiveCalls(
                serialize([1 => $item1]),
                serialize([2 => $item2]),
                serialize([1 => new \stdClass()]),
                serialize([1 => $item1, 2 => $item2])
            );

        $requestMock = $this->getRequestMock();
        $requestMock->cookies = $bagMock;

        $responseMock = $this->getResponseMock();

        $storage = new CookieStorage($requestMock, $responseMock, $this->name);

        $this->assertFalse($storage->has(null));
        $this->assertFalse($storage->has(1));
        $this->assertFalse($storage->has(1));
        $this->assertTrue($storage->has(1));
    }

    public function testSet()
    {
        $item1 = new Item(['id' => 1, 'name' => 'Test item 1', 'quantity' => 10, 'price' => 123.45]);
        $item2 = new Item(['id' => 2, 'name' => 'Test item 2', 'quantity' => 10, 'price' => 123.45]);

        $bagMock = $this->getBagMock();
        $bagMock->expects($this->any())
            ->method('get')
            ->with($this->name)
            ->willReturnOnConsecutiveCalls(
                serialize([]),
                serialize([1 => $item1]),
                serialize([1 => $item1, 2 => $item2]),
                serialize([1 => $item1, 2 => $item2])
            );

        $callOne = function ($subject) use ($item1) {
            return
                is_callable([$subject, 'getName']) &&
                $subject->getName() === $this->name &&
                is_callable([$subject, 'getValue']) &&
                $subject->getValue() === serialize([1 => $item1]);
        };

        $callTwo = function($subject) use ($item1, $item2) {
            return
                is_callable([$subject, 'getName']) &&
                $subject->getName() === $this->name &&
                is_callable([$subject, 'getValue']) &&
                $subject->getValue() === serialize([1 => $item1, 2 => $item2]);
        };

        $callThree = function($subject) use ($item1, $item2) {
            return
                is_callable([$subject, 'getName']) &&
                $subject->getName() === $this->name &&
                is_callable([$subject, 'getValue']) &&
                $subject->getValue() === serialize([1 => $item1, 2 => $item2]);
        };

        $headerMock = $this->getHeaderMock();
        $headerMock
            ->expects($this->exactly(3))
            ->method('setCookie')
            ->withConsecutive(
                [$this->callback($callOne)],
                [$this->callback($callTwo)],
                [$this->callback($callThree)]
            )
        ;

        $requestMock = $this->getRequestMock();
        $requestMock->cookies = $bagMock;

        $responseMock = $this->getResponseMock();
        $responseMock->headers = $headerMock;

        $storage = new CookieStorage($requestMock, $responseMock, $this->name);

        $storage->set($item1);
        $storage->set($item2);
        $storage->set($item2);
    }

    public function testAdd()
    {
        $item1 = new Item(['id' => 1, 'name' => 'Test item 1', 'quantity' => 10, 'price' => 123.45]);
        $item2 = new Item(['id' => 2, 'name' => 'Test item 2', 'quantity' => 10, 'price' => 123.45]);
        // same as above
        $item3 = new Item(['id' => 2, 'name' => 'Test item 2', 'quantity' => 10, 'price' => 123.45]);

        $bagMock = $this->getBagMock();
        $bagMock->expects($this->exactly(3))
            ->method('get')
            ->with($this->name)
            ->willReturnOnConsecutiveCalls(
                serialize([]),
                serialize([1 => $item1, 2 => new \stdClass()]),
                serialize([1 => $item1, 2 => $item2])
            );

        $callOne = function ($subject) use ($item1) {
            return
                is_callable([$subject, 'getName']) &&
                $subject->getName() === $this->name &&
                is_callable([$subject, 'getValue']) &&
                $subject->getValue() === serialize([1 => $item1]);
        };

        $callTwo = function ($subject) use ($item1, $item2) {
            return
                is_callable([$subject, 'getName']) &&
                $subject->getName() === $this->name &&
                is_callable([$subject, 'getValue']) &&
                $subject->getValue() === serialize([1 => $item1, 2 => $item2]);
        };

        $callThree = function($subject) use ($item1, $item2) {
            $item2 = clone $item2;
            $item2->setQuantity(20);
            return
                is_callable([$subject, 'getName']) &&
                $subject->getName() === $this->name &&
                is_callable([$subject, 'getValue'])
                && $subject->getValue() === serialize([1 => $item1, 2 => $item2])
                ;
        };

        $headerMock = $this->getHeaderMock();
        $headerMock
            ->expects($this->exactly(3))
            ->method('setCookie')
            ->withConsecutive(
                [$this->callback($callOne)],
                [$this->callback($callTwo)],
                [$this->callback($callThree)]
            )
        ;

        $requestMock = $this->getRequestMock();
        $requestMock->cookies = $bagMock;

        $responseMock = $this->getResponseMock();
        $responseMock->headers = $headerMock;

        $storage = new CookieStorage($requestMock, $responseMock, $this->name);

        $this->assertTrue($storage->add($item1));
        $this->assertTrue($storage->add($item2));
        // add Item with an id which already is added
        // TODO: test quantity is added correct
        $this->assertTrue($storage->add($item3));
    }



    public function testAll()
    {
        $item1 = new Item(['id' => 1, 'name' => 'Test item 1', 'quantity' => 10, 'price' => 123.45]);
        $item2 = new Item(['id' => 2, 'name' => 'Test item 2', 'quantity' => 10, 'price' => 123.45]);

        $bagMock = $this->getBagMock();
        $bagMock->expects($this->exactly(3))
            ->method('get')
            ->with($this->name)
            ->willReturnOnConsecutiveCalls(
                serialize([]),
                serialize([1 => new \stdClass()]),
                serialize([1 => $item1, 2 => $item2])
            );

        $requestMock = $this->getRequestMock();
        $requestMock->cookies = $bagMock;

        $responseMock = $this->getResponseMock();

        $storage = new CookieStorage($requestMock, $responseMock, $this->name);

        $this->assertEquals(0, count($storage->all()));
        $this->assertEquals(0, count($storage->all()));
        $this->assertEquals(2, count($storage->all()));
    }

    public function testClear()
    {
        $requestMock = $this->getRequestMock();

        $headerMock = $this->getHeaderMock();
        $headerMock
            ->expects($this->exactly(1))
            ->method('clearCookie')
            ->with($this->name)
        ;

        $responseMock = $this->getResponseMock();
        $responseMock->headers = $headerMock;

        $storage = new CookieStorage($requestMock, $responseMock, $this->name);

        $storage->clear();
    }
}
