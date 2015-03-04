<?php
/**
 * @author Johnny Theill <j.theill@gmail.com>
 * @date 28-01-2015 12:51
 */

namespace Theill11\Tests\Cart;

use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Theill11\Cart\Cart;
use Theill11\Cart\Item;

class CartTest extends \PHPUnit_Framework_TestCase
{

    /** @var Cart */
    protected $cart;
    protected $items = [];

    protected function setUp()
    {
        $storage = new MockArraySessionStorage();
        $attributes = new NamespacedAttributeBag();
        $session = new Session($storage, $attributes);
        $this->cart = new Cart($session);
    }

    public function getTestCart($widthData = false)
    {
        $storage = new MockArraySessionStorage();
        $attributes = new NamespacedAttributeBag();
        $session = new Session($storage, $attributes);

        $key = '_test_cart';
        if ($widthData) {
            $session->set("$key/1", $this->getTestItem(1));
            $session->set("$key/2", $this->getTestItem(2));
        }
        $cart = new Cart($session, $key);
        return $cart;
    }

    protected function getTestItem($id)
    {
        switch ($id) {
            case 1:
                return new Item(['id' => 1, 'name' => 'Test Item #1', 'quantity' => 1, 'price' => 123.45]);
            case 2:
                return new Item(['id' => 2, 'name' => 'Test Item #2', 'quantity' => 2, 'price' => 987.65]);
            case 3:
                return new Item(['id' => 3, 'name' => 'Test Item #3', 'quantity' => 15, 'price' => 0.75]);
        }
        return null;
    }

    public function testCanInstantiate()
    {
        $cart = $this->getTestCart(false);
        $this->assertInstanceOf('Theill11\Cart\Cart', $cart);
    }

    public function testDefaultValues()
    {
        $cart = $this->getTestCart(false);
        $this->assertEquals(0, count($cart->getItems()));
        $this->assertEquals(0, $cart->getTotal());
    }

    public function testGetItem()
    {
        $item1 = $this->getTestItem(1);
        $item2 = $this->getTestItem(2);

        $cart = $this->getTestCart(true);

        $this->assertEquals($item1, $cart->getItem(1));
        $this->assertEquals($item2, $cart->getItem('2'));
        $this->assertNull($cart->getItem(999));
    }

    public function testGetItems()
    {
        $item1 = $this->getTestItem(1);
        $item2 = $this->getTestItem(2);

        $cart = $this->getTestCart(true);

        $items = $cart->getItems();
        // ensure count
        $this->assertEquals(2, count($items));
        // ensure they are indexed by id
        $this->assertArrayHasKey(1, $items);
        $this->assertArrayHasKey(2, $items);
        // ensure they are Item objects with correct ids
        $this->assertEquals($item1, $items[1]);
        $this->assertEquals($item2, $items[2]);
    }

    public function testAddItemOk()
    {
        $item1 = $this->getTestItem(1);
        $item2 = $this->getTestItem(2);

        $cart = $this->getTestCart(false);

        // add first Item
        $cart->addItem($item1);
        $this->assertEquals(1, count($cart->getItems()));
        $this->assertArrayHasKey(1, $cart->getItems());

        // add second Item
        $cart->addItem($item2);
        $this->assertEquals(2, count($cart->getItems()));
        $this->assertArrayHasKey(2, $cart->getItems());

        // add another "Item 1"
        $cart->addItem($item1);
        $this->assertEquals(2, count($cart->getItems()));
        // check quantity is updated
        $this->assertEquals(2, $cart[1]->getQuantity());
    }

    public function testAddItems()
    {
        $item1 = $this->getTestItem(1);
        $item2 = $this->getTestItem(2);

        $cart = $this->getTestCart(false);

        $cart->addItems([$item1, $item2]);
        $this->assertEquals(2, count($cart->getItems()));
        $this->assertArrayHasKey(1, $cart);
        $this->assertArrayHasKey(2, $cart);
    }

    /**
     * @param $options
     * @dataProvider addItemFailProvider
     * @expectedException \InvalidArgumentException
     */
    public function testAddItemFail($options)
    {
        $cart = $this->getTestCart(false);

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
        $item1a = $this->getTestItem(1);
        $qty = $item1a->getQuantity();
        $item1b = $this->getTestItem(1);

        $cart = $this->getTestCart(false);

        $cart->setItem($item1a);
        $this->assertEquals(1, count($cart->getItems()));
        $this->assertArrayHasKey(1, $cart);

        // replace Item (should not change quantity)
        $cart->setItem($item1b);
        $this->assertEquals(1, count($cart->getItems()));
        $this->assertEquals($qty, $cart->getItem(1)->getQuantity());

    }

    public function testSetItems()
    {
        $item1 = $this->getTestItem(1);
        $qty1 = $item1->getQuantity();
        $item2 = $this->getTestItem(2);
        $qty2 = $item2->getQuantity();

        $cart = $this->getTestCart(true);

        $cart->setItems([$item1, $item2]);
        $this->assertEquals(2, count($cart->getItems()));
        $this->assertArrayHasKey(1, $cart);
        $this->assertArrayHasKey(2, $cart);
        $this->assertEquals($qty1, $cart->getItem(1)->getQuantity());
        $this->assertEquals($qty2, $cart->getItem(2)->getQuantity());
    }

    public function testRemoveItem()
    {
        $item1 = $this->getTestItem(1);
        $item2 = $this->getTestItem(2);
        $cart = $this->getTestCart(true);

        $this->assertEquals(2, count($cart->getItems()));

        // remove Item 1
        $this->assertEquals($item1, $cart->removeItem(1));
        $this->assertEquals(1, count($cart->getItems()));

        // not existing
        $this->assertNull($cart->removeItem(3));
        $this->assertEquals(1, count($cart->getItems()));

        // remove int as string
        $this->assertEquals($item2, $cart->removeItem(2));
        $this->assertEquals(0, count($cart->getItems()));

    }

    public function testHasItem()
    {
        $cart = $this->getTestCart(true);

        $this->assertTrue($cart->hasItem(1));
        $this->assertTrue($cart->hasItem('1'));
        $this->assertTrue($cart->hasItem(2));
        $this->assertFalse($cart->hasItem(3));
    }

    public function testClear()
    {
        $cart = $this->getTestCart(true);

        $this->assertEquals(2, count($cart->getItems()));
        $cart->clear();
        $this->assertEquals(0, count($cart->getItems()));
    }

    public function testGetTotal()
    {
        $cart = $this->getTestCart(false);
        $this->assertEquals(0, $cart->getTotal());

        $cart = $this->getTestCart(true);
        $this->assertEquals(2098.75, $cart->getTotal());

        $cart->addItem($this->getTestItem(1));
        $this->assertEquals(2222.2, $cart->getTotal());

        $cart->setItem($this->getTestItem(1));
        $this->assertEquals(2098.75, $cart->getTotal());
    }

    public function testCount()
    {
        $cart = $this->getTestCart(false);

        $this->assertEquals(0, count($cart));

        $cart->addItem($this->getTestItem(1));
        $this->assertEquals(1, count($cart));

        $cart->addItem($this->getTestItem(1));
        $this->assertEquals(1, count($cart));

        $cart->addItem($this->getTestItem(2));
        $this->assertEquals(2, count($cart));

        $cart->removeItem(1);
        $this->assertEquals(1, count($cart));
    }

    public function testOffsetExists()
    {
        $cart = $this->getTestCart(true);

        $this->assertTrue(isset($cart[1]));
        $this->assertTrue(isset($cart[2]));
        $this->assertFalse(isset($cart[3]));
        $this->assertFalse($cart->offsetExists(3));
    }

    public function testOffsetGet()
    {
        $item1 = $this->getTestItem(1);
        $item2 = $this->getTestItem(2);

        $cart = $this->getTestCart(true);

        $this->assertEquals($item1, $cart[1]);
        $this->assertEquals($item2, $cart[2]);
        $this->assertNull($cart[999]);
    }

    public function testOffsetSetWhenIdIsNull()
    {
        $item1 = $this->getTestItem(1);
        $item2 = $this->getTestItem(2);

        $cart = $this->getTestCart(false);

        $cart[] = $item1;
        $cart[] = $item2;

        $this->assertEquals(2, count($cart->getItems()));
        $this->assertArrayHasKey(1, $cart);
        $this->assertArrayHasKey(2, $cart);
    }

    public function testOffsetSetWhenIdIsValid()
    {
        $item1 = $this->getTestItem(1);
        $item2 = $this->getTestItem(2);

        $cart = $this->getTestCart(false);

        $cart[1] = $item1;
        $cart['2'] = $item2;

        $this->assertEquals(2, count($cart->getItems()));
        $this->assertArrayHasKey(1, $cart);
        $this->assertArrayHasKey(2, $cart);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testOffsetSetWhenIdIsInvalid()
    {
        $item1 = $this->getTestItem(1);

        $cart = $this->getTestCart(false);

        $cart[2] = $item1;
    }

    public function testOffsetUnset()
    {
        $cart = $this->getTestCart(true);

        unset($cart[1]);
        $this->assertEquals(1, count($cart->getItems()));
        unset($cart[3]);
        $this->assertEquals(1, count($cart->getItems()));
        unset($cart['2']);
        $this->assertEquals(0, count($cart->getItems()));
    }
}
