<?php
//***************************************
// Montage des controleurs sur le routeur

use App\Helper\HelperMiddleWare;

$app->mount("/", new App\Controller\IndexController($app));
$app->mount("/produit", new App\Controller\ProduitController($app));
$app->mount("/panier", new App\Controller\PanierController($app));
$app->mount("/commande", new App\Controller\CommandeController($app));
$app->mount("/connexion", new App\Controller\UserController($app));
$app->mount("/commentaires", new App\Controller\CommentaireController($app));

$middleware=new HelperMiddleWare();
$app=$middleware->verifDroit($app);