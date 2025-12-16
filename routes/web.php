<?php
/**
 * Web Routes for R&G Application
 * Define all application routes here
 */

// Home
$router->get('/', 'HomeController@index');

// Products
$router->get('/products', 'ProductController@index');
$router->get('/product/{id}', 'ProductController@show');
$router->get('/bijoux', 'ProductController@bijoux');
$router->get('/vetements-femme', 'ProductController@vetementsFemme');
$router->get('/vetements-homme', 'ProductController@vetementsHomme');

// Cart
$router->get('/cart', 'CartController@index');
$router->post('/cart/add', 'CartController@add');
$router->post('/cart/update', 'CartController@update');
$router->post('/cart/remove', 'CartController@remove');
$router->post('/cart/clear', 'CartController@clear');

// Checkout
$router->get('/checkout', 'CheckoutController@index');
$router->post('/checkout/process', 'CheckoutController@process');
$router->get('/checkout/success', 'CheckoutController@success');
$router->get('/checkout/cancel', 'CheckoutController@cancel');

// User Authentication
$router->any('/login', 'UserController@login');
$router->any('/register', 'UserController@register');
$router->get('/logout', 'UserController@logout');
$router->get('/compte', 'UserController@account');

// Admin Routes
$router->get('/admin', 'Admin\DashboardController@index');
$router->get('/admin/orders', 'Admin\OrderController@index');
$router->get('/admin/orders/{id}', 'Admin\OrderController@show');
$router->get('/admin/products', 'Admin\ProductController@index');
$router->post('/admin/products', 'Admin\ProductController@index');
$router->get('/admin/products/create', 'Admin\ProductController@create');
$router->get('/admin/products/{id}/edit', 'Admin\ProductController@edit');
$router->get('/admin/users', 'Admin\UserController@index');
