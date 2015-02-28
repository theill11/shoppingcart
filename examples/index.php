<?php

require_once('../vendor/autoload.php');

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Theill11\Cart\Cart;
use Theill11\Cart\Item;
use Theill11\Cart\Storage\CookieStorage;
use Theill11\Cart\Storage\SessionStorage;

// initialize request/response
$request = Request::createFromGlobals();
$response = new Response();

// setup storage
if (!true) {
    // use session to store the items
    $session = new Session();
    $request->setSession($session);
    $storage = new SessionStorage($session);
} else {
    // or use cookies
    $storage = new CookieStorage($request, $response);
}
// create the Cart
$cart = new Cart($storage);

// test data
$products[1] = ['id' => '1', 'name' => 'Test item one', 'price' => '10.00'];
$products[2] = ['id' => '2', 'name' => 'Test item two', 'price' => '100.00'];
$products[3] = ['id' => '3', 'name' => 'Test item three', 'price' => '50.57'];
$products[4] = ['id' => '4', 'name' => 'Test item four', 'price' => '0.25'];
$products[5] = ['id' => '5', 'name' => 'Test item five', 'price' => '756.00'];

// handle incoming request
if ($request->isMethod('post')) {
    // get the action form the post var
    $action = $request->request->get('action');
    switch ($action) {
        case 'add': // add item eg. from product listing
            $id = $request->get('id');
            if (isset($products[$id])) {
                // create the Item
                $item = new Item($products[$id]);
                // we dont supply a quantity in form below so it defaults to "1"
                $item->setQuantity($request->get('quantity', 1));
                // add the Item to the Cart
                $cart->addItem($item);
            }
            break;
        case 'remove': // remove item from Cart
            $cart->removeItem($request->get('id'));
            break;
        case 'quantity': // update quantity on Item in Cart
            // get Item from Cart
            $item = $cart->getItem($request->get('id'));
            if ($item) {
                // set new quantity
                $item->setQuantity($request->get('quantity'));
                // use setItem() to replace existing Item
                $cart->setItem($item);
            }
            break;
        case 'clear': // Clear the Cart
            $cart->clear();
            break;
    }
    // simple refresh
    $response->setStatusCode(303);
    $response->headers->set('Location', 'index.php');
    $response->prepare($request);
    $response->send();
    exit();
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cart Example</title>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css"/>
    <style>
        .double-underline {
            border-bottom: 1px solid black;
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Simple Cart Example</h1>
    <form action="" method="post">
        <input type="hidden" name="action" value="clear"/>
        <input type="submit" value="Clear cart" class="btn btn-danger"/>
    </form>
    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th class="text-right">Id</th>
            <th class="text-left">Name</th>
            <th class="text-right">Quantity</th>
            <th class="text-right">Price</th>
            <th class="text-right">Total</th>
            <th class="text-center">Actions</th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <th colspan="4" class="text-right">Sum (<?= count($cart) ?> items)</th>
            <th class="text-right"><span class="double-underline"><?= number_format($cart->getTotal(), 2) ?></span></th>
            <th></th>
        </tr>
        </tfoot>
        <tbody>
        <?php foreach ($cart->getItems() as $id => $item): ?>
            <tr>
                <td class="text-right"><?= $item->getId() ?></td>
                <td><?= $item->getName() ?></td>
                <td class="text-right">
                    <form action="" method="post" class="form-inline">
                        <input type="hidden" name="action" value="quantity"/>
                        <input type="hidden" name="id" value="<?= $item->getId() ?>"/>
                        <div class="input-group">
                            <input type="text" name="quantity" value="<?= $item->getQuantity() ?>" class="form-control"/>
                            <span class="input-group-btn">
                                <input type="submit" value="Update" class="btn btn-info"/>
                            </span>
                        </div>
                    </form>
                </td>
                <td class="text-right"><?= number_format($item->getPrice(), 2) ?></td>
                <td class="text-right"><?= number_format($item->getTotal(), 2) ?></td>
                <td>
                    <form action="" method="post">
                        <input type="hidden" name="action" value="remove"/>
                        <input type="hidden" name="id" value="<?= $item->getId() ?>"/>
                        <input type="submit" value="Remove" class="btn btn-warning"/>
                    </form>

                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <h2>Products</h2>
    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th class="text-right">Id</th>
            <th>Name</th>
            <th class="text-right">Price</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($products as $product): ?>
            <tr>
                <td class="text-right"><?= $product['id'] ?></td>
                <td><?= $product['name'] ?></td>
                <td class="text-right"><?= $product['price'] ?></td>
                <td>
                    <form action="" method="post">
                        <input type="hidden" name="action" value="add"/>
                        <input type="hidden" name="id" value="<?= $product['id'] ?>"/>
                        <input type="submit" value="Add" class="btn btn-success"/>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
