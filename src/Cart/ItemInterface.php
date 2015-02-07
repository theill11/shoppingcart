<?php
/**
 * @author: Johnny Theill <j.theill@gmail.com>
 * @date: 03-02-2015 12:47
 */

namespace Theill11\Cart;

interface ItemInterface
{
    /**
     * @return int unique id
     */
    public function getId();

    /**
     * @return int number of items
     */
    public function getQuantity();

    /**
     * @param $newQuantity
     * @return void
     */
    public function setQuantity($newQuantity);

    /**
     * Get the total price for item
     * @return float
     */
    public function getTotal();

    /**
     * Must return true only if "id, name, quantity and price" is set to non empty values
     * @return bool
     */
    public function isValid();

}
