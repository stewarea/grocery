<?php

    require_once __DIR__."/../vendor/autoload.php";
    //dompdf
    require_once __DIR__."/../vendor/dompdf/autoload.inc.php";


    require_once __DIR__."/../src/Customer.php";
    require_once __DIR__."/../src/Order.php";
    require_once __DIR__."/../src/Product.php";
    require_once __DIR__."/../src/Category.php";

    date_default_timezone_set('America/Los_Angeles');

    use Symfony\Component\Debug\Debug;
    Debug::enable();

    // use Symfony\Component\HttpFoundation\Session;

    use Symfony\Component\HttpFoundation\Request;  Request::enableHttpMethodParameterOverride();
    use Dompdf\Dompdf;
    // session_start();
    // if (empty($_SESSION['current_user'])) {
    //     $_SESSION['current_user'] = null;
    // }
    //
    // $total = 0;
    // $b = 0;
    // foreach ($_SESSION['cart_items'] as $item) {
    //     $product_id = $cart_item['product_id'];
    //     $purchase_quantity = $cart_item['purchase_quantity'];
    //     $purchase_price = $cart_item['price'];
    //     $purchase_subtotal = $purchase_quantity * $purchase_price;
    // }
    session_start();

    $app = new Silex\Application();

    $app['debug'] = true;

    $server = 'mysql:host=localhost:8889;dbname=shoppr';
    $username = 'root';
    $password = 'root';
    $DB = new PDO($server, $username, $password);

    $app->register(new Silex\Provider\TwigServiceProvider(), array('twig.path' => __DIR__.'/../views'
    ));

    $app->get("/", function() use ($app) {
      return $app['twig']->render('order.html.twig', array('orders' => Order::getAll()));
    });

    $app->post("/test/pdf", function() use ($app) {
        $new_order = new Order(null, $user_id=34, $order_date="11-11-1999", $delivery_date_time="11:00 11-11-1999");
        var_dump($new_order);


        // instantiate and use the dompdf class
        $dompdf = new Dompdf();
        $dompdf->loadHtml('http://webtricksandtreats.com/html-to-pdf-php/');

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'landscape');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream("hi.pdf");
      return $app['twig']->render('order.html.twig', array('orders' => Order::getAll()));
    });

    $app->get("/cart", function () use ($app){
         $newOrder = Order::find($_SESSION['order_id']);
         if($newOrder) $cart = $newOrder->getCart();
           else $cart = null;

         return $app['twig']->render('cart.html.twig', array('cart' => $cart));
   });


    $app->post("/cart", function () use ($app){
      $product = Product::find($_POST['product_id']);
      $product->setPurchaseQuantity($_POST['quantity']);

      if(!empty($_SESSION['order_id'])) $newOrder = Order::find($_SESSION['order_id']);
      else $newOrder = new Order(null, $_SESSION['user_id']);

      $newOrder->addProductToCart($product);
      $newOrder->save();

      $_SESSION['order_id'] = $newOder->id;
 });



    $app->post('login/', function () use ($app){
        $row = ['DB']->query("SELECT * FROM users WHERE email = {$_POST['email']} AND password = '{$_POST['password']}';");
        if($row) {
            // Store logged in user in session
            $_SESSION['user_id'] = $row->id;
            return $app['twig']->render('home.html.twig', array('orders' => Order::getAll()));
        }
    });
    // $app->post("/", function() use ($app) {
    //     $new_Stylist = new Stylist(null, $_POST['name'], $_POST['date_began'], $_POST['specialty']);
    //     $new_Stylist->save();
    //   return $app['twig']->render('home.html.twig', array('stylists' => Stylist::getAll()));
    // });
    //
    // $app->patch("/{id}/edit", function($id) use ($app) {
    //     $found_stylist= Stylist::find($id);
    //     $found_stylist->update($_POST['name'], $_POST['date_began'], $_POST['specialty']);
    //   return $app['twig']->render('home.html.twig', array('stylists' => Stylist::getAll()));
    // });
    //
    // $app->delete("/delete/{id}", function($id) use ($app) {
    //     $found_stylist = Stylist::find($id);
    //     $found_stylist->delete();
    //   return $app['twig']->render('home.html.twig', array('stylists' => Stylist::getAll()));
    // });
    //
    // $app->get("/stylist/{id}", function($id) use ($app) {
    //     $found_stylist = Stylist::find($id);
    //     $found_clients  = $found_stylist->getClients($id);
    //     return $app['twig']->render('stylist.html.twig', array('stylist' => $found_stylist, 'clients' => $found_clients, 'stylists' => Stylist::getAll()));
    // });
    //
    // $app->post("/stylist/{id}", function($id) use ($app) {
    //     $found_stylist = Stylist::find($id);
    //     $new_client = new Client (null, $_POST['name'], $_POST['last_appointment'], $_POST['next_appointment'], $_POST['stylist_id']);
    //     $new_client->save();
    //     $found_clients  = $found_stylist->getClients($id);
    //     return $app['twig']->render('stylist.html.twig', array('stylist' => $found_stylist, 'clients' => $found_clients, 'stylists' => Stylist::getAll()));
    // });
    //
    // $app->delete("/stylist/{id}/delete/{client_id}", function($id, $client_id) use ($app) {
    //     $found_stylist = Stylist::find($id);
    //     $new_client = Client::find($client_id);
    //     $new_client->delete();
    //     $found_clients =$found_stylist->getClients($id);
    //     return $app['twig']->render('stylist.html.twig', array('stylist' => $found_stylist, 'clients' => $found_clients, 'stylists' => Stylist::getAll()));
    // });
    //
    // $app->patch("/stylist/{id}/edit/", function($id) use ($app) {
    //     $found_stylist = Stylist::find($id);
    //     $stuff = $_POST['person'];
    //     $found_client = Client::find($stuff);
    //     $found_client->update($_POST['name'],$_POST['last_appointment'], $_POST['next_appointment']);
    //     $found_clients =$found_stylist->getClients($id);
    //     return $app['twig']->render('stylist.html.twig', array('stylist' => $found_stylist, 'clients' => $found_clients, 'stylists' => Stylist::getAll()));
    // });

    return $app;
?>
