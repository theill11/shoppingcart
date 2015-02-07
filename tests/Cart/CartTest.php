<?php
/**
 * @author Johnny Theill <j.theill@gmail.com>
 * @date 28-01-2015 12:51
 */

namespace Theill11\Tests\Cart;

use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Theill11\Cart\Cart;
use Theill11\Cart\Item;

class CartTest extends \PHPUnit_Framework_TestCase
{

    protected function getStorageMock()
    {
        return $this->getMockBuilder('Theill11\Cart\Storage\StorageInterface')->getMock();
    }

    public function testCanInstantiate()
    {
        $mock = $this->getStorageMock();

        $cart = new Cart($mock);
        $this->assertInstanceOf('Theill11\Cart\Cart', $cart);
    }

    public function testDefaultValues()
    {
        $mock = $this->getStorageMock();
        $mock
            ->expects($this->exactly(2))
            ->method('all')
            ->willReturn([]);

        $cart = new Cart($mock);
        $this->assertEquals(0, count($cart->getItems()));
        $this->assertEquals(0, $cart->getTotal());
    }

    public function testGetItem()
    {
        $mock = $this->getStorageMock();

        $item1 = new Item(['id' => 1, 'name' => 'Test item 1', 'quantity' => 10, 'price' => 123.45]);
        $item2 = new Item(['id' => 2, 'name' => 'Test item 2', 'quantity' => 20, 'price' => 987.65]);

        $mock
            ->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive([1], [2], [999])
            ->willReturnOnConsecutiveCalls($item1, $item2, null);

        $cart = new Cart($mock);

        $this->assertSame($item1, $cart->getItem(1));
        $this->assertSame($item2, $cart->getItem('2'));
        $this->assertNull($cart->getItem(999));
    }

    public function testGetItems()
    {
        $mock = $this->getStorageMock();

        $item1 = new Item(['id' => 1, 'name' => 'Test item 1', 'quantity' => 10, 'price' => 123.45]);
        $item2 = new Item(['id' => 2, 'name' => 'Test item 2', 'quantity' => 20, 'price' => 987.65]);

        $mock
            ->expects($this->exactly(2))
            ->method('all')
            ->willReturnOnConsecutiveCalls([1 => $item1], [1 => $item1, 2 => $item2]);

        $cart = new Cart($mock);

        $this->assertEquals(1, count($cart->getItems()));
        $this->assertEquals(2, count($cart->getItems()));
    }

    public function testAddItemOk()
    {
        $mock = $this->getStorageMock();

        $item1 = new Item(['id' => 1, 'name' => 'Test item 1', 'quantity' => 10, 'price' => 123.45]);
        $item2 = new Item(['id' => 2, 'name' => 'Test item 2', 'quantity' => 20, 'price' => 987.65]);

        $mock
            ->expects($this->exactly(2))
            ->method('add')
            ->withConsecutive([$this->identicalTo($item1)], [$this->identicalTo($item2)]);

        $cart = new Cart($mock);

        $cart->addItem($item1);
        $cart->addItem($item2);
    }

    public function testAddItems()
    {
        $mock = $this->getStorageMock();

        $item1 = new Item(['id' => 1, 'name' => 'Test item 1', 'quantity' => 10, 'price' => 123.45]);
        $item2 = new Item(['id' => 2, 'name' => 'Test item 2', 'quantity' => 20, 'price' => 987.65]);

        $mock
            ->expects($this->exactly(2))
            ->method('add')
            ->withConsecutive([$this->identicalTo($item1)], [$this->identicalTo($item2)]);

        $cart = new Cart($mock);

        $cart->addItems([$item1, $item2]);
    }

    /**
     * @param $options
     * @dataProvider addItemFailProvider
     * @expectedException \InvalidArgumentException
     */
    public function testAddItemFail($options)
    {
        $mock = $this->getStorageMock();
        $cart = new Cart($mock);

        $cart->addItem(new Item($options));
    }

    public function addItemFailProvider()
    {
        return [
            [[]],
            [['id' => 1]],
            [['id' => 1, 'name' => '']],
        ];
    }

    public function testSetItem()
    {
        $mock = $this->getStorageMock();

        $item1 = new Item(['id' => 1, 'name' => 'Test item 1', 'quantity' => 10, 'price' => 123.45]);
        $item2 = new Item(['id' => 2, 'name' => 'Test item 2', 'quantity' => 20, 'price' => 987.65]);

        $mock
            ->expects($this->exactly(2))
            ->method('set')
            ->withConsecutive([$this->identicalTo($item1)], [$this->identicalTo($item2)]);

        $cart = new Cart($mock);

        $cart->setItem($item1);
        $cart->setItem($item2);
    }

    public function testSetItems()
    {
        $mock = $this->getStorageMock();

        $item1 = new Item(['id' => 1, 'name' => 'Test item 1', 'quantity' => 10, 'price' => 123.45]);
        $item2 = new Item(['id' => 2, 'name' => 'Test item 2', 'quantity' => 20, 'price' => 987.65]);

        $mock
            ->expects($this->exactly(1))
            ->method('clear');

        $mock
            ->expects($this->exactly(2))
            ->method('add')
            ->withConsecutive([$this->identicalTo($item1)], [$this->identicalTo($item2)]);

        $cart = new Cart($mock);

        $cart->setItems([$item1, $item2]);
    }

    public function testRemoveItem()
    {
        $mock = $this->getStorageMock();

        $mock
            ->expects($this->exactly(2))
            ->method('remove')
            ->withConsecutive([1], [2]);

        $cart = new Cart($mock);

        $cart->removeItem(1);
        $cart->removeItem('2');
    }

    public function testHasItem()
    {
        $mock = $this->getStorageMock();

        $mock
            ->expects($this->exactly(3))
            ->method('has')
            ->withConsecutive([1], [2], [3])
            ->willReturnOnConsecutiveCalls(true, true, false);

        $cart = new Cart($mock);

        $this->assertTrue($cart->hasItem(1));
        $this->assertTrue($cart->hasItem(2));
        $this->assertFalse($cart->hasItem(3));
    }

    public function testClear()
    {
        $mock = $this->getStorageMock();

        $mock
            ->expects($this->exactly(1))
            ->method('clear');

        $cart = new Cart($mock);

        $cart->clear();
    }

    public function testGetTotal()
    {
        $mock = $this->getStorageMock();

        $item1 = new Item(['id' => 1, 'name' => 'Test item 1', 'quantity' => 10, 'price' => 123.45]);
        $item2 = new Item(['id' => 2, 'name' => 'Test item 2', 'quantity' => 10, 'price' => 987.65]);

        $mock
            ->expects($this->exactly(2))
            ->method('all')
            ->willReturnOnConsecutiveCalls([1 => $item1], [1 => $item1, 2 => $item2]);

        $cart = new Cart($mock);

        $this->assertEquals(1234.5, $cart->getTotal());
        $this->assertEquals(11111, $cart->getTotal());
    }

    public function testCount()
    {
        $mock = $this->getStorageMock();

        $item1 = new Item(['id' => 1, 'name' => 'Test item 1', 'quantity' => 10, 'price' => 123.45]);
        $item2 = new Item(['id' => 2, 'name' => 'Test item 2', 'quantity' => 10, 'price' => 987.65]);

        $mock
            ->expects($this->exactly(2))
            ->method('all')
            ->willReturnOnConsecutiveCalls([1 => $item1], [1 => $item1, 2 => $item2]);

        $cart = new Cart($mock);

        $this->assertEquals(1, count($cart));
        $this->assertEquals(2, count($cart));
    }

    public function testOffsetExists()
    {
        $mock = $this->getStorageMock();

        $mock
            ->expects($this->exactly(3))
            ->method('has')
            ->withConsecutive([1], [2], [3])
            ->willReturnOnConsecutiveCalls(true, true, false);

        $cart = new Cart($mock);

        $this->assertTrue(isset($cart[1]));
        $this->assertTrue(isset($cart[2]));
        $this->assertFalse($cart->offsetExists(3));
    }

    public function testOffsetGet()
    {
        $mock = $this->getStorageMock();

        $item1 = new Item(['id' => 1, 'name' => 'Test item 1', 'quantity' => 10, 'price' => 123.45]);
        $item2 = new Item(['id' => 2, 'name' => 'Test item 2', 'quantity' => 20, 'price' => 987.65]);

        $mock
            ->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive([1], [2], [999])
            ->willReturnOnConsecutiveCalls($item1, $item2, null);

        $cart = new Cart($mock);

        $this->assertSame($item1, $cart[1]);
        $this->assertSame($item2, $cart[2]);
        $this->assertNull($cart[999]);
    }

    public function testOffsetSetWhenIdIsNull()
    {
        $mock = $this->getStorageMock();

        $item1 = new Item(['id' => 1, 'name' => 'Test item 1', 'quantity' => 10, 'price' => 123.45]);
        $item2 = new Item(['id' => 2, 'name' => 'Test item 2', 'quantity' => 20, 'price' => 987.65]);

        $mock
            ->expects($this->exactly(2))
            ->method('add')
            ->withConsecutive([$this->identicalTo($item1)], [$this->identicalTo($item2)]);

        $cart = new Cart($mock);

        $cart[] = $item1;
        $cart[] = $item2;
    }

    public function testOffsetSetWhenIdIsValid()
    {
        $mock = $this->getStorageMock();

        $item1 = new Item(['id' => 1, 'name' => 'Test item 1', 'quantity' => 10, 'price' => 123.45]);
        $item2 = new Item(['id' => 2, 'name' => 'Test item 2', 'quantity' => 20, 'price' => 987.65]);

        $mock
            ->expects($this->exactly(2))
            ->method('set')
            ->withConsecutive([$this->identicalTo($item1)], [$this->identicalTo($item2)]);

        $cart = new Cart($mock);

        $cart[1] = $item1;
        $cart[2] = $item2;
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testOffsetSetWhenIdIsInvalid()
    {
        $mock = $this->getStorageMock();

        $item1 = new Item(['id' => 1, 'name' => 'Test item 1', 'quantity' => 10, 'price' => 123.45]);
        $item2 = new Item(['id' => 2, 'name' => 'Test item 2', 'quantity' => 20, 'price' => 987.65]);

        $mock
            ->expects($this->exactly(0))
            ->method('add');

        $mock
            ->expects($this->exactly(0))
            ->method('set');

        $cart = new Cart($mock);

        $cart[2] = $item1;
        $cart[1] = $item2;
    }

    public function testOffsetUnset()
    {
        $mock = $this->getStorageMock();

        $mock
            ->expects($this->exactly(2))
            ->method('remove')
            ->withConsecutive([1], [2]);

        $cart = new Cart($mock);

        unset($cart[1]);
        unset($cart[2]);
    }
}
