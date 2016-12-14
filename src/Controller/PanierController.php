<?php
namespace App\Controller;


use App\Model\CommandeModel;
use App\Model\UserModel;
use App\Services\MyToken;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;   // modif version 2.0

use Silex\ControllerCollection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\HttpFoundation\Request;   // pour utiliser request

use App\Model\PanierModel;
use App\Model\ProduitModel;
use App\Model\TypeProduitModel;

use Symfony\Component\Validator\Constraints as Assert;   // pour utiliser la validation
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Security;

class PanierController implements ControllerProviderInterface {

    private $panierModel;
    private $userModel;
    private $produitModel;
    private $commandeModel;
    private $typeProduitModel;

    private $myToken;

    public function initModel(Application $app){
        $this->panierModel = new PanierModel($app);
        $this->userModel = new UserModel($app);
        $this->produitModel= new ProduitModel($app);
        $this->commandeModel= new CommandeModel($app);

        $this->myToken=new MyToken($app);
    }


    public function index(Application $app) {
        return $this->showPanierUser($app);
    }

    /*
    public function show(Application $app) {
        $this->panierModel = new PanierModel($app);
        $donnees = $this->panierModel->getAllPaniers();
        return $app["twig"]->render('frontOff/Panier/show.html.twig',['data'=>$donnees]);

    } */

    public function showPanierUser(Application $app) {
        $this->myToken=new MyToken($app);

        $id=$app['session']->get('user_id');
        $this->panierModel = new PanierModel($app);
        //date de validite
        $this->panierModel->cleanPanierForDate();
        $panier = $this->panierModel->getUserPanier($id);
        $total=$this->panierModel->getUserSommePaniers($id);
        return $app["twig"]->render('frontOff/Panier/show.html.twig',['dataPanier'=>$panier, 'total'=>$total]);
    }

    public function showAccueilClient(Application $app){
        $this->myToken=new MyToken($app);

        $id=$app['session']->get('user_id');
        $this->panierModel = new PanierModel($app);
        $panier = $this->panierModel->getUserPanier($id);
        if (isset($_POST['Type'])){

            $type = $_POST['Type'];
            $this->produitModel=new ProduitModel($app);
            $produits = $this->produitModel->searchProduitType($type);

        }else {
            $this->produitModel = new ProduitModel($app);
            $produits = $this->produitModel->getAllProduits();
        }
        $this->typeProduitModel = new TypeProduitModel($app);
        $typeProduits = $this->typeProduitModel->getAllTypeProduits();
        $total=$this->panierModel->getUserSommePaniers($id);
        return $app["twig"]->render('frontOff/frontOFFICE.html.twig',['dataProduit'=>$produits, 'dataPanier'=>$panier,'dataType'=>$typeProduits, 'total'=>$total]);
    }

    public function updatePanierUser(Application $app){

        if(!MyToken::verif_token()){
            return $app["twig"]->render('v_error_token.html.twig',['tokenattendue'=>$_SESSION['token'],'tokenrecu'=>$_POST['token']]);
        }

        $this->panierModel=new PanierModel($app);
        $this->produitModel=new ProduitModel($app);
        $this-> typeProduitModel = new TypeProduitModel($app);
        $idUser=$app['session']->get('user_id');
        if (isset($_POST['id']) && isset($_POST['quantite'])){
            $donnees = [
                'id'=> htmlspecialchars($_POST['id']),
                'quantite' => htmlspecialchars($_POST['quantite']),
                'produit_id' => null,
                'user_id'=>$idUser,
                'prix'=>100,
                'dateAjoutPanier' => null,
                'commande_id' => null
            ];

            $erreurs=[];
            if(! is_numeric($donnees['quantite']))$erreurs['quantite']='saisir une valeur numérique';
            if(! is_numeric($donnees['user_id']))$erreurs['user_id']='saisir une valeur numérique';
            if(! is_numeric($donnees['id']))$erreurs['id']='saisir une valeur numérique';

            $valeur=$this->panierModel->getPaniers($donnees['id']);
            $donnees['produit_id']=$valeur['produit_id'];
            $prix=$this->produitModel->getProduit($donnees['produit_id'])['prix'];
            $donnees['prix']=$prix*$donnees['quantite'];
            $donnees['dateAjoutPanier']=$valeur['dateAjoutPanier'];

            if(! is_numeric($donnees['produit_id']))$erreurs['produit_id']='saisir une valeur numérique';

            if(count($erreurs)>0){
                var_dump($erreurs);
                return "????";
            }

            /*
             * calcul du prix
             */
            $this->panierModel->updatePaniers($donnees);

            $panier = $this->panierModel->getUserPanier($idUser);
            $produits = $this->produitModel->getAllProduits();
            $total=$this->panierModel->getUserSommePaniers($idUser);
            $typeProduits=$this->typeProduitModel->getAllTypeProduits();

            $this->myToken=new MyToken($app);
            return $app["twig"]->render('frontOff/frontOFFICE.html.twig',['dataProduit'=>$produits, 'dataPanier'=>$panier,'dataType'=>$typeProduits, 'total'=>$total]);
        }

        return "Erreur Panier Controller update";
    }

    public function addPanierUser(Application $app){

        if(!MyToken::verif_token()){
            return $app["twig"]->render('v_error_token.html.twig',['tokenattendue'=>$_SESSION['token'],'tokenrecu'=>$_POST['token']]);
        }

        $idUser=$app['session']->get('user_id');
        $this->panierModel = new PanierModel($app);
        $this->produitModel =new ProduitModel($app);

        if (isset($_POST['id']) && isset($_POST['quantite'])){
            $donnees = [
                'quantite' => htmlspecialchars($_POST['quantite']),
                'produit_id' =>  htmlspecialchars($_POST['id']),
                'user_id'=>$idUser,
                'prix'=>100,
                'dateAjoutPanier' => null,
                'commande_id' => null
            ];

            $erreurs=[];
            if(! is_numeric($donnees['quantite']))$erreurs['quantite']='saisir une valeur numérique';
            if(! is_numeric($donnees['user_id']))$erreurs['user_id']='saisir une valeur numérique';
            if(! is_numeric($donnees['produit_id']))$erreurs['user_id']='saisir une valeur numérique';

            if(count($erreurs)>0){
                var_dump($erreurs);
                return "????";
            }

            /*
             * calcul du prix
             */
            $data=$this->produitModel->getProduit($donnees['produit_id']);
            $donnees['prix']=$data['prix']*$donnees['quantite'];

            $this->panierModel->clearPaniers($idUser);
            $this->panierModel->insertPaniers($donnees);

            $addInfo=$this->produitModel->getProduit($donnees['produit_id']);
            $total=$this->panierModel->getUserSommePaniers($donnees['user_id']);
            return $app["twig"]->render('frontOff/Panier/add.html.twig',['total'=>$total, 'addInfo'=>$addInfo]);
        }
            return $app->abort(404, 'error Pb in addPanierUser');
    }

    public function deletePanierUser(Application $app, $id) {
        /*
        if(!MyToken::verif_token()){
            return $app["twig"]->render('v_error_token.html.twig',['tokenattendue'=>$_SESSION['token'],'tokenrecu'=>$_POST['token']]);
        }

       */

        $idUser=$app['session']->get('user_id');
        $this->panierModel = new PanierModel($app);
        $this->panierModel->deleteUserPaniers($id, $idUser);
        return $this->showAccueilClient($app);
    }


    
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('/', 'App\Controller\PanierController::index')->bind('panier.index');
        $controllers->get('/show', 'App\Controller\PanierController::show')->bind('panier.show');

        $controllers->get('/showUser', 'App\Controller\PanierController::showPanierUser')->bind('panier.showPanierUser');

        $controllers->match('/acceuilClient', 'App\Controller\PanierController::showAccueilClient')->bind('panier.showAccueilClient');

        $controllers->post('/ajout','App\Controller\PanierController::addPanierUser')->bind('panier.addPanierUser');

        $controllers->get('/delete/{id}', 'App\Controller\PanierController::deletePanierUser')->bind('panier.deletePanierUser')->assert('id','\d+');

        $controllers->post('/update','App\Controller\PanierController::updatePanierUser')->bind('panier.updatePanierUser');


        return $controllers;
    }
}