<?php
namespace App\Controller;

use App\Model\CommentaireModel;
use App\Services\MyToken;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;   // modif version 2.0
use Symfony\Component\HttpFoundation\Request;   // pour utiliser request
use App\Model\ProduitModel;
use App\Model\TypeProduitModel;
use Symfony\Component\Validator\Constraints as Assert;   // pour utiliser la validation
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Security;

class ProduitController implements ControllerProviderInterface {
    private $produitModel;
    private $typeProduitModel;
    private $commentaireModel;

    private $myToken;


    public function initModel(Application $app){
        $this->produitModel = new ProduitModel($app);
        $this->typeProduitModel = new TypeProduitModel($app);
        $this-> commentaireModel = new CommentaireModel($app);

        $this->myToken = new MyToken($app);
    }

    public function index(Application $app) {
        return $this->show($app);
    }

    public function showStock(Application $app){
        $this->produitModel = new ProduitModel($app);
        $produits = $this->produitModel->getAllStockProduits();
        return $app["twig"]->render('backOff/Produit/stockValue.html.twig',['data'=>$produits]);
    }

    public function showProductsForSale(Application $app) {
        $this->produitModel = new ProduitModel($app);
        $produits = $this->produitModel->getAllProduits();
        return $app["twig"]->render('frontOff/Produit/showProduitForSale.html.twig',['data'=>$produits]);
    }

    public function lastProduit(Application $app){
        $id=$app['session']->get('produit');
        if($id!=NULL){
            return $this->describeProduit($app, $id);
        }else{
            return $app->redirect($app["url_generator"]->generate("produit.sale"));
        }

    }

    public function describeProduit(Application $app, $id){
        $this->myToken=new MyToken($app);

        $app['session']->set('produit', $id);

        $this->typeProduitModel = new TypeProduitModel($app);
        $this->produitModel = new ProduitModel($app);
        $donnees = $this->produitModel->getProduit($id);
        $this->commentaireModel = new CommentaireModel($app);
        $commentaires = $this-> commentaireModel->getAllCommentaires($id);
        return $app["twig"]->render('frontOff/Produit/describeProduit.html.twig',['produit'=>$donnees,'commentaires'=>$commentaires]);
    }

    public function searchProduit(Application $app){
        if (isset($_POST['recherhce'])){
            $in=htmlspecialchars($_POST['recherhce']);
            $this->produitModel = new ProduitModel($app);
            $produits = $this->produitModel->searchProduit($in);
            return $app["twig"]->render('frontOff/Produit/showProduitForSale.html.twig',['data'=>$produits]);
        }else{
            return $app["twig"]->render('frontOff/Produit/searchProduit.html.twig');
        }
    }

    public function searchProduitType(Application $app){

        if (isset($_POST['Type'])) {
            $type = $_POST['Type'];
            $this->produitModel=new ProduitModel($app);
            $produits = $this->produitModel->searchProduitType($type);



            return $app["twig"] -> render('frontOff/Produit/showProduitForSale.html.twig',['data'=>$produits]);
        }else {
            return $app["twig"]->render('frontOff/Produit/searchProduit.html.twig');
        }
    }
    // Show pour la page d'acceuil avec frontOFFICE
    public function showProductForClient(Application $app) {
        $this->produitModel = new ProduitModel($app);
        $produits = $this->produitModel->getAllProduits();

        return $app["twig"]->render('frontOff/frontOFFICE.html.twig',['data'=>$produits,]);

    }


    public function show(Application $app) {
        $this->produitModel = new ProduitModel($app);
        $produits = $this->produitModel->getAllProduits();
        return $app["twig"]->render('backOff/Produit/show.html.twig',['data'=>$produits]);
    }

    public function add(Application $app) {
        $this->mytoken=new MyToken($app);

        $this->typeProduitModel = new TypeProduitModel($app);
        $typeProduits = $this->typeProduitModel->getAllTypeProduits();
        return $app["twig"]->render('backOff/Produit/add.html.twig',['typeProduits'=>$typeProduits,'path'=>BASE_URL]);
    }

    public function validFormAdd(Application $app, Request $req) {
        if(!MyToken::verif_token()){
            return $app["twig"]->render('v_error_token.html.twig',['tokenattendue'=>$_SESSION['token'],'tokenrecu'=>$_POST['token']]);
        }

       // var_dump($app['request']->attributes);
        if (isset($_POST['nom']) && isset($_POST['typeProduit_id']) and isset($_POST['prix']) and isset($_FILES['photo']) and isset($_POST['stock'])) {
            $donnees = [
                'nom' => htmlspecialchars($_POST['nom']),                    // echapper les entrées
                'typeProduit_id' => htmlspecialchars($req->get('typeProduit_id')),
                'stock' => htmlspecialchars($req->get('stock')),
                'prix' => htmlspecialchars($req->get('prix')),
                'photo' => $app->escape($req->get('photo'))  //$req->query->get('photo')-> ne focntionne plus
            ];
            if ((! preg_match("/^[A-Za-z ]{2,}/",$donnees['nom']))) $erreurs['nom']='Nom composé de 2 lettres minimum';
            if(! is_numeric($donnees['typeProduit_id']))$erreurs['typeProduit_id']='Veuillez saisir une valeur';
            if(! is_numeric($donnees['stock']))$erreurs['stock']='Veuillez saisir une valeur';
            if(! is_numeric($donnees['prix']))$erreurs['prix']='Saisir une valeur numérique';
            if (! preg_match("/[A-Za-z0-9]{2,}.(jpeg|jpg|png)/",$donnees['photo'])) $erreurs['photo']='Nom de fichier incorrect (extension jpeg , jpg ou png)';

            if(! empty($erreurs))
            {
                $this->mytoken=new MyToken($app);
                $this->typeProduitModel = new TypeProduitModel($app);
                $typeProduits = $this->typeProduitModel->getAllTypeProduits();
                return $app["twig"]->render('backOff/Produit/add.html.twig',['donnees'=>$donnees,'erreurs'=>$erreurs,'typeProduits'=>$typeProduits]);
            }
            else
            {
                $this->produitModel = new ProduitModel($app);
                $this->produitModel->insertProduit($donnees);
                return $app->redirect($app["url_generator"]->generate("produit.index"));
            }

        }
        else
            return $app->abort(404, 'error Pb data form Add');
    }

    public function delete(Application $app, $id) {
        $this->mytoken=new MyToken($app);

        $this->typeProduitModel = new TypeProduitModel($app);
        $typeProduits = $this->typeProduitModel->getAllTypeProduits();
        $this->produitModel = new ProduitModel($app);
        $donnees = $this->produitModel->getProduit($id);
        return $app["twig"]->render('backOff/Produit/delete.html.twig',['typeProduits'=>$typeProduits,'donnees'=>$donnees]);
    }

    public function validFormDelete(Application $app, Request $req) {
        if(!MyToken::verif_token()){
            return $app["twig"]->render('v_error_token.html.twig',['tokenattendue'=>$_SESSION['token'],'tokenrecu'=>$_POST['token']]);
        }

        $id=$app->escape($req->get('id'));
        if (is_numeric($id)) {
            $this->produitModel = new ProduitModel($app);
            $this->produitModel->deleteProduit($id);
            return $app->redirect($app["url_generator"]->generate("produit.index"));
        }
        else
            return $app->abort(404, 'error Pb id form Delete');
    }


    public function edit(Application $app, $id) {
        $this->mytoken=new MyToken($app);

        $this->typeProduitModel = new TypeProduitModel($app);
        $typeProduits = $this->typeProduitModel->getAllTypeProduits();
        $this->produitModel = new ProduitModel($app);
        $donnees = $this->produitModel->getProduit($id);
        return $app["twig"]->render('backOff/Produit/edit.html.twig',['typeProduits'=>$typeProduits,'donnees'=>$donnees]);
    }

    public function validFormEdit(Application $app, Request $req) {
        if(!MyToken::verif_token()){
            return $app["twig"]->render('v_error_token.html.twig',['tokenattendue'=>$_SESSION['token'],'tokenrecu'=>$_POST['token']]);
        }

        // var_dump($app['request']->attributes);
        if (isset($_POST['nom']) && isset($_POST['typeProduit_id']) and isset($_POST['nom']) and isset($_POST['photo']) and isset($_POST['id']) and isset($_POST['stock']) and isset($_POST['dispo'])) {
            $donnees = [
                'nom' => htmlspecialchars($_POST['nom']),                    // echapper les entrées
                'typeProduit_id' => htmlspecialchars($req->get('typeProduit_id')),  //$app['request']-> ne focntionne plus
                'prix' => htmlspecialchars($req->get('prix')),
                'stock' => htmlspecialchars($req->get('stock')),
                'dispo' => htmlspecialchars($req->get('dispo')),
                'photo' => $app->escape($req->get('photo')),
                'id' => $app->escape($req->get('id'))
            ];
            if ((! preg_match("/^[A-Za-z ]{2,}/",$donnees['nom']))) $erreurs['nom']='Nom composé de 2 lettres minimum';
            if(! is_numeric($donnees['typeProduit_id']))$erreurs['typeProduit_id']='Veuillez saisir une valeur';
            if(! is_numeric($donnees['prix']))$erreurs['prix']='Saisir une valeur numérique';
            if (! preg_match("/[A-Za-z0-9]{2,}.(jpeg|jpg|png)/",$donnees['photo'])) $erreurs['photo']='Nom de fichier incorrect (extension jpeg , jpg ou png)';
            if(! is_numeric($donnees['id']))$erreurs['id']='Saisir une valeur numérique';
            if(! is_numeric($donnees['stock']))$erreurs['stock']='Saisir une valeur numérique';

            if(!is_numeric($donnees['dispo'])){
                $erreurs['dispo']='saisir une valeur numérique';
            }else if(is_numeric($donnees['stock'])&&$donnees['stock']<1&&$donnees['dispo']==1){
                $erreurs['dispo']='le stock n est pas sufisant';
            }

            $contraintes = new Assert\Collection(
                [
                    'id' => [new Assert\NotBlank(),new Assert\Type('digit')],
                    'typeProduit_id' => [new Assert\NotBlank(),new Assert\Type('digit')],
                    'stock' => [new Assert\NotBlank(),new Assert\Type('digit')],
                    'dispo' => [new Assert\NotBlank(),new Assert\Type('digit')],
                    'nom' => [
                        new Assert\NotBlank(['message'=>'saisir une valeur']),
                        new Assert\Length(['min'=>2, 'minMessage'=>"Le nom doit faire au moins {{ limit }} caractères."])
                    ],
                    //http://symfony.com/doc/master/reference/constraints/Regex.html
                    'photo' => [
                        new Assert\Length(array('min' => 5)),
                        new Assert\Regex([ 'pattern' => '/[A-Za-z0-9]{2,}.(jpeg|jpg|png)/',
                        'match'   => true,
                        'message' => 'nom de fichier incorrect (extension jpeg , jpg ou png)' ]),
                    ],
                    'prix' => new Assert\Type(array(
                        'type'    => 'numeric',
                        'message' => 'La valeur {{ value }} n\'est pas valide, le type est {{ type }}.',
                    ))
                ]);
            $errors = $app['validator']->validate($donnees,$contraintes);  // ce n'est pas validateValue

            if (count($erreurs) > 0 || count($errors)) {
                $this->mytoken=new MyToken($app);
                $this->typeProduitModel = new TypeProduitModel($app);
                $typeProduits = $this->typeProduitModel->getAllTypeProduits();
                return $app["twig"]->render('backOff/Produit/edit.html.twig',['donnees'=>$donnees,'errors'=>$errors,'erreurs'=>$erreurs,'typeProduits'=>$typeProduits]);
            }
            else
            {
                $this->produitModel = new ProduitModel($app);
                $this->produitModel->updateProduit($donnees);
                return $app->redirect($app["url_generator"]->generate("produit.index"));
            }

        }
        else
            return $app->abort(404, 'error Pb id form edit');

    }

    public function connect(Application $app) {  //http://silex.sensiolabs.org/doc/providers.html#controller-providers
        $controllers = $app['controllers_factory'];


        $controllers->get('/stock', 'App\Controller\produitController::showStock')->bind('produit.stock');

        $controllers->get('/', 'App\Controller\produitController::index')->bind('produit.index');
        $controllers->get('/show', 'App\Controller\produitController::show')->bind('produit.show');

        $controllers->get('/sale', 'App\Controller\produitController::showProductsForSale')->bind('produit.sale');

        $controllers->get('/last', 'App\Controller\produitController::lastProduit')->bind('produit.last');

        $controllers->get('/add', 'App\Controller\produitController::add')->bind('produit.add');
        $controllers->post('/add', 'App\Controller\produitController::validFormAdd')->bind('produit.validFormAdd');

        $controllers->get('/delete/{id}', 'App\Controller\produitController::delete')->bind('produit.delete')->assert('id', '\d+');
        $controllers->delete('/delete', 'App\Controller\produitController::validFormDelete')->bind('produit.validFormDelete');

        $controllers->get('/edit/{id}', 'App\Controller\produitController::edit')->bind('produit.edit')->assert('id', '\d+');
        $controllers->put('/edit', 'App\Controller\produitController::validFormEdit')->bind('produit.validFormEdit');

        $controllers->match('/search', 'App\Controller\produitController::searchProduit')->bind('produit.search');
        $controllers->match('/searchType', 'App\Controller\produitController::searchProduitType')->bind('produit.searchType');

        $controllers->get('/describe/{id}', 'App\Controller\produitController::describeProduit')->bind('produit.describe')->assert('id', '\d+');



        return $controllers;
    }
}
