<?php
/**
 * @author: Johnny Theill <j.theill@gmail.com>
 * @date: 07-02-2015 01:19
 */

namespace Theill11\Tests\Cart\Storage;

use Theill11\Cart\Item;
use Theill11\Cart\Storage\SessionStorage;

class SessionStorageTest extends \PHPUnit_Framework_TestCase
{

    public $key = '_test-key';

    protected function getSessionMock()
    {
        return $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\SessionInterface')->getMock();
    }

    public function testCanInstantiate()
    {
        $mock = $this->getSessionMock();
        $storage = new SessionStorage($mock, $this->key);
        $this->assertInstanceOf('Theill11\Cart\Storage\SessionStorage', $storage);
    }

    public function testGet()
    {
        $item1 = new Item(['id' => 1, 'name' => 'Test item 1', 'quantity' => 10, 'price' => 123.45]);
        $item2 = new Item(['id' => 2, 'name' => 'Test item 2', 'quantity' => 10, 'price' => 123.45]);

        $mock = $this->getSessionMock();
        $mock
            ->expects($this->exactly(4))
            ->method('get')
            ->with($this->key, [])
            ->willReturnOnConsecutiveCalls(
                [1 => $item1],
                [2 => $item2],
                [1 => new \stdClass()],
                [1 => $item1, 2 => $item2]
            );

        $storage = new SessionStorage($mock, $this->key);

        $this->assertNull($storage->get(null));
        $this->assertNull($storage->get(1));
        $this->assertNull($storage->get(1));
        $this->assertSame($item2, $storage->get(2));
    }

    public function testRemove()
    {
        $item1 = new Item(['id' => 1, 'name' => 'Test item 1', 'quantity' => 10, 'price' => 123.45]);
        $item2 = new Item(['id' => 2, 'name' => 'Test item 2', 'quantity' => 10, 'price' => 123.45]);

        $mock = $this->getSessionMock();
        $mock
            ->expects($this->exactly(4))
            ->method('get')
            ->with($this->key, [])
            ->willReturnOnConsecutiveCalls(
                [1 => $item1],
                [2 => $item2],
                [1 => new \stdClass()],
                [1 => $item1, 2 => $item2]
            );

        $mock
            ->expects($this->once())
            ->method('set')
            ->with($this->key, [2 => $item2]);

        $storage = new SessionStorage($mock, $this->key);

        $this->assertFalse($storage->remove(null));
        $this->assertFalse($storage->remove(1));
        $this->assertFalse($storage->remove(1));
        $this->assertTrue($storage->remove(1));
    }

    public function testHas()
    {
        $item1 = new Item(['id' => 1, 'name' => 'Test item 1', 'quantity' => 10, 'price' => 123.45]);
        $item2 = new Item(['id' => 2, 'name' => 'Test item 2', 'quantity' => 10, 'price' => 123.45]);

        $mock = $this->getSessionMock();
        $mock
            ->expects($this->exactly(4))
            ->method('get')
            ->with($this->key, [])
            ->willReturnOnConsecutiveCalls([1 => $item1], [2 => $item2], [1 => new \stdClass()], [1 => $item1]);

        $storage = new SessionStorage($mock, $this->key);

        $this->assertFalse($storage->has(null));
        $this->assertFalse($storage->has(1));
        $this->assertFalse($storage->has(1));
        $this->assertTrue($storage->has(1));
    }

    public function testSet()
    {
        $item1 = new Item(['id' => 1, 'name' => 'Test item 1', 'quantity' => 10, 'price' => 123.45]);
        $item2 = new Item(['id' => 2, 'name' => 'Test item 2', 'quantity' => 10, 'price' => 123.45]);

        $mock = $this->getSessionMock();
        $mock
            ->expects($this->exactly(3))
            ->method('get')
            ->with($this->key, [])
            ->willReturnOnConsecutiveCalls(
                [],
                [1 => $item1],
                [1 => $item1, 2 => $item2],
                [1 => $item1, 2 => $item2]
            );

        $mock
            ->expects($this->exactly(3))
            ->method('set')
            ->withConsecutive(
                [$this->key, [1 => $item1]],
                [$this->key, [1 => $item1, 2 => $item2]],
                [$this->key, [1 => $item1, 2 => $item2]]
            );

        $storage = new SessionStorage($mock, $this->key);

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

        $mock = $this->getSessionMock();
        $mock
            ->expects($this->exactly(3))
            ->method('get')
            ->with($this->key, [])
            ->willReturnOnConsecutiveCalls(
                [],
                [1 => $item1, 2 => new \stdClass()],
                [1 => $item1, 2 => $item2]
            );

        $mock
            ->expects($this->exactly(2))
            ->method('set')
            ->withConsecutive(
                [$this->key, [1 => $item1]]
            );

        $storage = new SessionStorage($mock, $this->key);
        $this->assertTrue($storage->add($item1));
        $this->assertFalse($storage->add($item2));
        // add Item with an id which already is added
        // TODO: test quantity is added correct
        $this->assertTrue($storage->add($item3));

    }

    public function testAll()
    {
        $item1 = new Item(['id' => 1, 'name' => 'Test item 1', 'quantity' => 10, 'price' => 123.45]);
        $item2 = new Item(['id' => 2, 'name' => 'Test item 2', 'quantity' => 10, 'price' => 123.45]);

        $mock = $this->getSessionMock();
        $mock
            ->expects($this->exactly(3))
            ->method('get')
            ->with($this->key, [])
            ->willReturnOnConsecutiveCalls(
                [],
                [1 => new \stdClass()],
                [1 => $item1, 2 => $item2]
            );
        $storage = new SessionStorage($mock, $this->key);

        $this->assertEquals(0, count($storage->all()));
        $this->assertEquals(0, count($storage->all()));
        $this->assertEquals(2, count($storage->all()));
    }

    public function testClear()
    {
        $mock = $this->getSessionMock();
        $mock
            ->expects($this->once())
            ->method('remove')
            ->with($this->key);
        $storage = new SessionStorage($mock, $this->key);

        $storage->clear();
    }
}
