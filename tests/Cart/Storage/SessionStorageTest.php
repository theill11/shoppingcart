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

    protected $items = [];

    protected function setUp()
    {
        parent::setUp();
        $this->items['item1'] = new Item(['id' => 1, 'name' => 'Test item 1', 'quantity' => 10, 'price' => 123.45]);
        $this->items['item2'] = new Item(['id' => 2, 'name' => 'Test item 2', 'quantity' => 10, 'price' => 123.45]);
        $this->items['item3'] = new Item(['id' => 1, 'name' => 'Test item 1', 'quantity' => 10, 'price' => 123.45]);
    }

    protected function getSessionMock()
    {
        return $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\SessionInterface')->getMock();
    }

    protected function getSessionMockConfigured()
    {
        $mock =  $this->getSessionMock();
        $mock
            ->expects($this->exactly(5))
            ->method('get')
            ->with($this->key, [])
            ->willReturnOnConsecutiveCalls(
                [[]],
                [1 => $this->items['item1']],
                [2 => $this->items['item2']],
                [1 => new \stdClass()],
                [1 => $this->items['item1'], 2 => $this->items['item2']]
            );
        return $mock;
    }

    public function testCanInstantiate()
    {
        $mock = $this->getSessionMock();
        $storage = new SessionStorage($mock, $this->key);

        $this->assertInstanceOf('Theill11\Cart\Storage\SessionStorage', $storage);
    }

    public function testGet()
    {
        $mock = $this->getSessionMockConfigured();
        $storage = new SessionStorage($mock, $this->key);

        $this->assertNull($storage->get(null));
        $this->assertNull($storage->get(0));
        $this->assertNull($storage->get(1));
        $this->assertNull($storage->get(1));
        $this->assertInstanceOf('Theill11\Cart\ItemInterface', $storage->get(2));
    }

    public function testRemove()
    {
        $mock = $this->getSessionMockConfigured();
        $mock
            ->expects($this->once())
            ->method('set')
            ->with($this->key, [2 => $this->items['item2']]);
        $storage = new SessionStorage($mock, $this->key);

        $this->assertFalse($storage->remove(null));
        $this->assertFalse($storage->remove(0));
        $this->assertFalse($storage->remove(1));
        $this->assertFalse($storage->remove(1));
        $this->assertTrue($storage->remove(1));
    }

    public function testHas()
    {
        $mock = $this->getSessionMockConfigured();
        $storage = new SessionStorage($mock, $this->key);

        $this->assertFalse($storage->has(null));
        $this->assertFalse($storage->has(0));
        $this->assertFalse($storage->has(1));
        $this->assertFalse($storage->has(1));
        $this->assertTrue($storage->has(1));
    }

    public function testSet()
    {
        $mock = $this->getSessionMockConfigured();
        $mock
            ->expects($this->exactly(5))
            ->method('set')
            ->withConsecutive(
                [$this->key, [1 => $this->items['item1']]],
                [$this->key, [1 => $this->items['item1'], 2 => $this->items['item2']]],
                [$this->key, [2 => $this->items['item2'], 1 => $this->items['item1']]],
                [$this->key, [1 => $this->items['item1']]],
                [$this->key, [1 => $this->items['item1'], 2 => $this->items['item2']]]
            );
        $storage = new SessionStorage($mock, $this->key);

        $storage->set($this->items['item1']);
        $storage->set($this->items['item2']);
        $storage->set($this->items['item1']);
        $storage->set($this->items['item1']);
        $storage->set($this->items['item3']);
    }

    public function testAdd()
    {
        $mock = $this->getSessionMockConfigured();
        $mock
            ->expects($this->exactly(5))
            ->method('set')
            ->withConsecutive(
                [$this->key, [1 => $this->items['item1']]],
                [$this->key, [1 => $this->items['item1'], 2 => $this->items['item2']]],
                [$this->key, [2 => $this->items['item2'], 1 => $this->items['item1']]],
                [$this->key, [1 => $this->items['item1']]],
                [$this->key, [1 => $this->items['item1'], 2 => $this->items['item2']]]
            );
        $storage = new SessionStorage($mock, $this->key);

        $storage->add($this->items['item1']);
        $storage->add($this->items['item2']);
        $storage->add($this->items['item1']);
        $storage->add($this->items['item1']);
        $storage->add($this->items['item3']);
    }

    public function testAll()
    {
        $mock = $this->getSessionMockConfigured();
        $storage = new SessionStorage($mock, $this->key);

        $this->assertEquals(0, count($storage->all()));
        $this->assertEquals(1, count($storage->all()));
        $this->assertEquals(1, count($storage->all()));
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
