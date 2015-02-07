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

    protected function getSessionMock()
    {
        return $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\SessionInterface')->getMock();
    }

    public function testCanInstantiate()
    {
        $mock = $this->getSessionMock();
        $storage = new SessionStorage($mock);
        $this->assertInstanceOf('Theill11\Cart\Storage\SessionStorage', $storage);
    }

    public function testGet()
    {
        $key = '_test-key';

        $item1 = new Item(['id' => 1, 'name' => 'Test item 1', 'quantity' => 10, 'price' => 123.45]);
        $item2 = new Item(['id' => 2, 'name' => 'Test item 2', 'quantity' => 10, 'price' => 123.45]);

        $mock = $this->getSessionMock();
        $mock
            ->expects($this->exactly(4))
            ->method('get')
            ->with($key, [])
            ->willReturnOnConsecutiveCalls(
                [1 => $item1],
                [2 => $item2],
                [1 => new \stdClass()],
                [1 => $item1, 2 => $item2]
            );

        $storage = new SessionStorage($mock, $key);

        $this->assertNull($storage->get(null));
        $this->assertNull($storage->get(1));
        $this->assertNull($storage->get(1));
        $this->assertSame($item2, $storage->get(2));
    }

    public function testRemove()
    {
        $key = '_test-key';

        $item1 = new Item(['id' => 1, 'name' => 'Test item 1', 'quantity' => 10, 'price' => 123.45]);
        $item2 = new Item(['id' => 2, 'name' => 'Test item 2', 'quantity' => 10, 'price' => 123.45]);

        $mock = $this->getSessionMock();
        $mock
            ->expects($this->exactly(4))
            ->method('get')
            ->with($key, [])
            ->willReturnOnConsecutiveCalls(
                [1 => $item1],
                [2 => $item2],
                [1 => new \stdClass()],
                [1 => $item1, 2 => $item2]
            );

        $mock
            ->expects($this->once())
            ->method('set')
            ->with($key, [2 => $item2]);

        $storage = new SessionStorage($mock, $key);

        $this->assertFalse($storage->remove(null));
        $this->assertFalse($storage->remove(1));
        $this->assertFalse($storage->remove(1));
        $this->assertTrue($storage->remove(1));
    }

    public function testHas()
    {
        $key = '_test-key';

        $item = new Item(['id' => 1, 'name' => 'Test item 1', 'quantity' => 10, 'price' => 123.45]);

        $mock = $this->getSessionMock();
        $mock
            ->expects($this->exactly(4))
            ->method('get')
            ->with($key, [])
            ->willReturnOnConsecutiveCalls([1 => $item], [2 => $item], [1 => new \stdClass()], [1 => $item]);

        $storage = new SessionStorage($mock, $key);

        $this->assertFalse($storage->has(null));
        $this->assertFalse($storage->has(1));
        $this->assertFalse($storage->has(1));
        $this->assertTrue($storage->has(1));
    }

    public function testSet()
    {
        $key = '_test-key';

        $item1 = new Item(['id' => 1, 'name' => 'Test item 1', 'quantity' => 10, 'price' => 123.45]);
        $item2 = new Item(['id' => 2, 'name' => 'Test item 2', 'quantity' => 10, 'price' => 123.45]);

        $mock = $this->getSessionMock();
        $mock
            ->expects($this->exactly(3))
            ->method('get')
            ->with($key, [])
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
                [$key, [1 => $item1]],
                [$key, [1 => $item1, 2 => $item2]],
                [$key, [1 => $item1, 2 => $item2]]
            );

        $storage = new SessionStorage($mock, $key);

        $storage->set($item1);
        $storage->set($item2);
        $storage->set($item2);
    }

    public function testAdd()
    {
        $key = '_test-key';

        $item1 = new Item(['id' => 1, 'name' => 'Test item 1', 'quantity' => 10, 'price' => 123.45]);
        $item2 = new Item(['id' => 2, 'name' => 'Test item 2', 'quantity' => 10, 'price' => 123.45]);
        // same as above
        $item3 = new Item(['id' => 2, 'name' => 'Test item 2', 'quantity' => 10, 'price' => 123.45]);

        $mock = $this->getSessionMock();
        $mock
            ->expects($this->exactly(3))
            ->method('get')
            ->with($key, [])
            ->willReturnOnConsecutiveCalls(
                [],
                [1 => $item1, 2 => new \stdClass()],
                [1 => $item1, 2 => $item2]
            );

        $mock
            ->expects($this->exactly(2))
            ->method('set')
            ->withConsecutive(
                [$key, [1 => $item1]]
            );

        $storage = new SessionStorage($mock, $key);
        $this->assertTrue($storage->add($item1));
        $this->assertFalse($storage->add($item2));
        // add Item with an id which already is added
        // TODO: test quantity is added correct
        $this->assertTrue($storage->add($item3));

    }

    public function testAll()
    {
        $key = '_test-key';

        $item1 = new Item(['id' => 1, 'name' => 'Test item 1', 'quantity' => 10, 'price' => 123.45]);
        $item2 = new Item(['id' => 2, 'name' => 'Test item 2', 'quantity' => 10, 'price' => 123.45]);

        $mock = $this->getSessionMock();
        $mock
            ->expects($this->exactly(3))
            ->method('get')
            ->with($key, [])
            ->willReturnOnConsecutiveCalls(
                [],
                [1 => new \stdClass()],
                [1 => $item1, 2 => $item2]
            );
        $storage = new SessionStorage($mock, $key);

        $this->assertEquals(0, count($storage->all()));
        $this->assertEquals(0, count($storage->all()));
        $this->assertEquals(2, count($storage->all()));
    }

    public function testClear()
    {
        $key = '_test-key';

        $mock = $this->getSessionMock();
        $mock
            ->expects($this->once())
            ->method('remove')
            ->with($key);
        $storage = new SessionStorage($mock, $key);

        $storage->clear();
    }
}
