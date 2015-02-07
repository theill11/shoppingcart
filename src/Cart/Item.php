<?php
/**
 * @author Johnny Theill <j.theill@gmail.com>
 * @date 19-01-2015 02:33
 */

namespace Theill11\Cart;

/**
 * Class CartItem
 * Represents a product witch in turn can be passed to the Cart class
 *
 * @package Theill11\Cart
 */
class Item implements ItemInterface
{
    /** @var int Unique id for this item */
    protected $id;

    /** @var string Name of the item */
    protected $name;

    /** @var int Number of items */
    protected $quantity = 0;

    /** @var float Price for one item */
    protected $price = 0;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        if ($options) {
            $this->configure($options);
        }
    }

    /**
     * Configures the Item with the given values
     *
     * $options = [
     *      'id' => 123
     *      'name' => 'Item name',
     *      'quantity' => 3
     *      'price' => 25.75,
     * ];
     *
     * @param array $options
     */
    public function configure(array $options)
    {
        foreach ($options as $option => $value) {
            $method = 'set' . $option;
            if (method_exists($this, $method)) {
                $this->$method($value);
            } elseif (method_exists($this, 'get' . $option)) {
                throw new \LogicException('Cannot set read-only property: ' . $option);
            } else {
                $this->$option = $value;
            }
        }
    }

    /**
     * @return int Unique id of the item
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id int Unique id of the item
     * @throws \InvalidArgumentException
     */
    public function setId($id)
    {
        $options = ['options' => ['min_range' => 0, 'max_range' => PHP_INT_MAX]];
        if (false === filter_var($id, FILTER_VALIDATE_INT, $options)) {
            throw new \InvalidArgumentException('Id must be an integer and not negative');
        }
        $this->id = (int) $id;
    }

    /**
     * @return string Name of the item
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name Name of the item
     * @throws \InvalidArgumentException
     */
    public function setName($name)
    {
        if (!is_string($name) || strlen($name) < 1) {
            throw new \InvalidArgumentException('Name must be a string with at least one character');
        }
        $this->name = (string) $name;
    }

    /**
     * @return int Number of items
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity Number of items
     * @throws \InvalidArgumentException
     */
    public function setQuantity($quantity)
    {
        $options = ['options' => ['min_range' => 0, 'max_range' => PHP_INT_MAX]];
        if (false === filter_var($quantity, FILTER_VALIDATE_INT, $options)) {
            throw new \InvalidArgumentException('Quantity must be an integer and not negative');
        }
        $this->quantity = (int) $quantity;
    }

    /**
     * @return float Price for one item
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price Price for one item
     * @throws \InvalidArgumentException
     */
    public function setPrice($price)
    {
        $options = ['options' => ['decimal' => '.']];
        if (false === filter_var($price, FILTER_VALIDATE_FLOAT, $options) || $price < 0) {
            throw new \InvalidArgumentException('Price must be numeric and not negative');
        }
        $this->price = (float) $price;
    }

    /**
     * @return float Total price for all items
     */
    public function getTotal()
    {
        return $this->quantity * $this->price;
    }

    /**
     * Check if the the Item is ready for adding to Cart
     * @return bool return true only if "id, name, quantity and price" is set to non empty values
     */
    public function isValid()
    {
        return
            $this->validateInteger($this->id) &&
            $this->validateString($this->name) &&
            $this->validateInteger($this->quantity) &&
            $this->validateFloat($this->price);
    }

    public function validateInteger($value)
    {
        $options = ['options' => ['min_range' => 0, 'max_range' => PHP_INT_MAX]];
        return filter_var($value, FILTER_VALIDATE_INT, $options) !== false;
    }

    public function validateFloat($value)
    {
        $options = ['options' => ['decimal' => '.']];
        return filter_var($value, FILTER_VALIDATE_FLOAT, $options) !== false && ($value >= 0);
    }

    public function validateString($value)
    {
        return is_string($value) && strlen($value) > 0;
    }
}
