<?php
/**
 * @author Johnny Theill <j.theill@gmail.com>
 * @date 19-01-2015 03:05
 */

namespace Theill11\Tests\Cart;

use Theill11\Cart\Item;

class ItemTest extends \PHPUnit_Framework_TestCase
{

    public function testCanInstantiate()
    {
        $item = new Item();
        $this->assertInstanceOf('Theill11\Cart\Item', $item);
    }

    /**
     * @param $expected
     * @param $int
     * @dataProvider validateIntegerProvider
     */
    public function testValidateInteger($expected, $int)
    {
        $item = new Item();
        $this->assertEquals($expected, $item->validateInteger($int));
    }

    public function validateIntegerProvider()
    {
        return [
            [true, 0],
            [true, PHP_INT_MAX],
            [true, 51251],
            [true, '515'],
            [true, 1],
            [false, 'string'],
            [false, null],
            [false, [1, 2]],
            [false, new \stdClass()],
            [false, -1],
            [false, PHP_INT_MAX + 1],
        ];
    }

    /**
     * @param $expected
     * @param $float
     * @dataProvider validateFloatProvider
     */
    public function testValidateFloat($expected, $float)
    {
        $item = new Item();
        $this->assertEquals($expected, $item->validateFloat($float));
    }

    public function validateFloatProvider()
    {
        return [
            [true, 0],
            [true, PHP_INT_MAX + 1],
            [true, 1.5],
            [true, '515.75'],
            [true, 1.0],
            [false, '15,5'],
            [false, null],
            [false, [1, 2]],
            [false, new \stdClass()],
            [false, -1.5],
        ];
    }

    /**
     * @param $expected
     * @param $string
     * @dataProvider validateStringProvider
     */
    public function testValidateString($expected, $string)
    {
        $item = new Item();
        $this->assertEquals($expected, $item->validateString($string));
    }

    public function validateStringProvider()
    {
        return [
            [true, 'Hello world'],
            [true, '1234'],
            [true, 'A'],
            [false, ''],
            [false, null],
            [false, [1, 2]],
            [false, new \stdClass()],
            [false, 123],
            [false, false],
        ];
    }

    /**
     * @param $expected
     * @param $values
     * @dataProvider isValidProvider
     */
    public function testIsValid($expected, $values)
    {
        $item = new Item($values);
        $this->assertEquals($expected, $item->isValid());
    }

    public function isValidProvider()
    {
        return [
            [true, ['id' => 1, 'name' => 'Test item', 'quantity' => 10, 'price' => 123.45]],
            [true, ['id' => 1, 'name' => 'Test item', 'quantity' => 10]],
            [true, ['id' => 1, 'name' => 'Test item']],
            [false, ['id' => 1]],
            [false, []],
        ];
    }

    public function testDefaultValues()
    {
        $item = new Item();
        $this->assertNull(null, $item->getId());
        $this->assertNull(null, $item->getName());
        $this->assertEquals(0, $item->getQuantity());
        $this->assertEquals(0, $item->getPrice());
        $this->assertEquals(0, $item->getTotal());
    }

    /**
     * @param $expected
     * @param $options
     * @dataProvider configureOkProvider
     */
    public function testConfigureOk($expected, $options)
    {
        $item = new Item();
        $item->configure($options);
        $this->assertEquals($expected['id'], $item->getId());
        $this->assertEquals($expected['name'], $item->getName());
        $this->assertEquals($expected['quantity'], $item->getQuantity());
        $this->assertEquals($expected['price'], $item->getPrice());
        $this->assertEquals($expected['total'], $item->getTotal());
    }

    public function configureOkProvider()
    {
        return [
            [
                ['id' => null, 'name' => null, 'quantity' => 0, 'price' => 0, 'total' => 0],
                [],
            ],
            [
                ['id' => 123, 'name' => 'Test item', 'quantity' => 10, 'price' => 75.25, 'total' => 752.5],
                ['id' => 123, 'name' => 'Test item', 'quantity' => 10, 'price' => 75.25],
            ],
            [
                ['id' => 123, 'name' => 'Test item', 'quantity' => 10, 'price' => 75.25, 'total' => 752.5],
                ['id' => '123', 'name' => 'Test item', 'quantity' => '10', 'price' => '75.25'],
            ],
        ];
    }

    /**
     * @param $options
     * @dataProvider configureFailProvider
     * @expectedException \InvalidArgumentException
     */
    public function testConfigureFail($options)
    {
        $item = new Item();
        $item->configure($options);
    }

    public function configureFailProvider()
    {
        return [
            [['id' => null]],
            [['id' => 'string']],
            [['name' => null]],
            [['name' => 105]],
            [['quantity' => null]],
            [['quantity' => 'string']],
            [['price' => null]],
            [['price' => 'string']],
        ];
    }

    /**
     * @expectedException \LogicException
     */
    public function testConfigureReadOnlyProperty()
    {
        $item = new Item();
        $item->configure(['total' => 250]);
    }

    public function testConfigureNewProperties()
    {
        $options = [
            'testNewIntValue' => 10,
            'testNewStringValue' => 'Hello world',
            'testNewArrayValue' => ['hello', 'world', 5],
            'testNewObjectValue' => new \stdClass(),
            'testNewFloatValue' => 1.5,

        ];

        $item = new Item();
        $item->configure($options);

        foreach ($options as $option => $value) {
            $this->assertEquals($value, $item->$option);
        }

    }

    /**
     * @param $expected
     * @param $options
     * @dataProvider configureOkProvider
     */
    public function testConstructByArray($expected, $options)
    {
        $item = new Item($options);
        $this->assertEquals($expected['id'], $item->getId());
        $this->assertEquals($expected['name'], $item->getName());
        $this->assertEquals($expected['quantity'], $item->getQuantity());
        $this->assertEquals($expected['price'], $item->getPrice());
        $this->assertEquals($expected['total'], $item->getTotal());
    }

    public function constructByArrayProvider()
    {
        return [
            [
                ['id' => null, 'name' => null, 'quantity' => 0, 'price' => 0, 'total' => 0],
                [],
            ],
            [
                ['id' => 123, 'name' => 'Test item', 'quantity' => 10, 'price' => 75.25, 'total' => 752.5],
                ['id' => 123, 'name' => 'Test item', 'quantity' => 10, 'price' => 75.25],
            ],
            [
                ['id' => 123, 'name' => 'Test item', 'quantity' => 10, 'price' => 75.25, 'total' => 752.5],
                ['id' => '123', 'name' => 'Test item', 'quantity' => '10', 'price' => '75.25'],
            ],
        ];
    }

    /**
     * @param $id
     * @dataProvider idOkProvider
     */
    public function testIdOk($id)
    {
        $item = new Item();
        $item->setId($id);
        $this->assertEquals($id, $item->getId());
    }

    public function idOkProvider()
    {
        return [
            [0], [5], [PHP_INT_MAX], ['10'],
        ];
    }

    /**
     * @param $id
     * @dataProvider idFailProvider
     * @expectedException \InvalidArgumentException
     */
    public function testIdFail($id)
    {
        $item = new Item();
        $item->setId($id);
    }

    public function idFailProvider()
    {
        return [
            [5.8], ['Hello'], [null], [-10], [PHP_INT_MAX + 1],
        ];
    }

    /**
     * @param $name
     * @dataProvider nameOkProvider
     */
    public function testNameOk($name)
    {
        $item = new Item();
        $item->setName($name);
        $this->assertEquals($name, $item->getName());
    }

    public function nameOkProvider()
    {
        return [
            ['A'], ['åse'], ['-.:'],
        ];
    }

    /**
     * @param $name
     * @dataProvider nameFailProvider
     * @expectedException \InvalidArgumentException
     */
    public function testNameFail($name)
    {
        $item = new Item();
        $item->setName($name);
    }

    public function nameFailProvider()
    {
        return [
            [''], [10], [true], [null], [-10], [[1, 2]],
        ];
    }

    /**
     * @param $quantity
     * @dataProvider quantityOkProvider
     */
    public function testQuantityOk($quantity)
    {
        $item = new Item();
        $item->setQuantity($quantity);
        $this->assertEquals($quantity, $item->getQuantity());
    }

    public function quantityOkProvider()
    {
        return [
            [0], [5], [PHP_INT_MAX], ['10'],
        ];
    }

    /**
     * @param $quantity
     * @dataProvider quantityFailProvider
     * @expectedException \InvalidArgumentException
     */
    public function testQuantityFail($quantity)
    {
        $item = new Item();
        $item->setQuantity($quantity);
    }

    public function quantityFailProvider()
    {
        return [
            [5.8], ['Hello'], [null], [-10], [PHP_INT_MAX + 1],
        ];
    }

    /**
     * @param $price
     * @dataProvider priceOkProvider
     */
    public function testPriceOk($price)
    {
        $item = new Item();
        $item->setPrice($price);
        $this->assertEquals($price, $item->getPrice());
    }

    public function priceOkProvider()
    {
        return [
            [0], [5], [PHP_INT_MAX], [PHP_INT_MAX +1], [10.5125152], [2000], ['10'],
        ];
    }

    /**
     * @param $price
     * @dataProvider priceFailProvider
     * @expectedException \InvalidArgumentException
     */
    public function testPriceFail($price)
    {
        $item = new Item();
        $item->setPrice($price);
    }

    public function priceFailProvider()
    {
        return [
            [-5.8], ['Hello'], [null], [-10],
        ];
    }

    /**
     * @param $quantity
     * @param $price
     * @param $expected
     * @dataProvider getTotalProvider
     */
    public function testGetTotal($quantity, $price, $expected)
    {
        $item = new Item();
        $item->setQuantity($quantity);
        $item->setPrice($price);
        $this->assertEquals($expected, $item->getTotal());
    }

    public function getTotalProvider()
    {
        return [
            [1, 543.10, 543.10],
            [5, 100, 500],
            [7, 234.17, 1639.19],
        ];
    }
}
