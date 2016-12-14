<?php
/**
 * Created by PhpStorm.
 * User: guillaume
 * Date: 13/11/16
 * Time: 14:27
 */

namespace App\Controller;


use App\Model\CommandeModel;
use App\Model\EtatModel;
use App\Model\PanierModel;
use App\Model\ProduitModel;
use App\Model\TypeProduitModel;
use App\Model\UserModel;
use App\Services\MyToken;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\Validator\Constraints as Assert;   // pour utiliser la validation
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;


class CommandeController implements ControllerProviderInterface{
    private $userModel;
    private $etatModel;
    private $commandeModel;
    private $panierModel;
    private $produitModel;
    private $typeProduitModel;

    private $myToken;

    public function initModel(Application $app){
        $this->commandeModel = new CommandeModel($app);
        $this->userModel = new UserModel($app);
        $this->etatModel = new EtatModel($app);
        $this->panierModel=new PanierModel($app);
        $this->produitModel = new ProduitModel($app);

        $this->myToken=new MyToken($app);
    }

    public function index(Application $app) {
        return $this->show($app);
    }

    public function show(Application $app) {
        $this->myToken=new MyToken($app);

        $this->commandeModel = new CommandeModel($app);
        $this->etatModel=new EtatModel($app);
        $donnees = $this->commandeModel->getAllCommandes();
        $etats=$this->etatModel->getAllEtats();
        return $app["twig"]->render('backOff/Commande/show.html.twig',['data'=>$donnees,'etats'=>$etats]);
    }

    public function describeCommande(Application $app, $id){

        $this->commandeModel=new CommandeModel($app);
        $this->panierModel=new PanierModel($app);

        $commande=$this->commandeModel->getCommandes($id);

        if(empty($commande['user_id']) or $commande['user_id'] ==  null ){
            return "error";
        }
        $paniers=$this->panierModel->getUserPanierCommande($commande['user_id']);


        return $app["twig"]->render('backOff/Commande/describeCommande.html.twig',['commande'=>$commande ,'paniers'=>$paniers]);
    }

    public function changeEtat(Application $app){

        if(!MyToken::verif_token()){
            return $app["twig"]->render('v_error_token.html.twig',['tokenattendue'=>$_SESSION['token'],'tokenrecu'=>$_POST['token']]);
        }

        if (isset($_POST['id']) && isset($_POST['etat'])){
            $donnees = [
                'id'=> htmlspecialchars($_POST['id']),
                'etat' => htmlspecialchars($_POST['etat'])
            ];

            $erreurs=[];
            if(! is_numeric($donnees['etat']))$erreurs['user_id']='saisir une valeur numérique';
            if(! is_numeric($donnees['id']))$erreurs['id']='saisir une valeur numérique';


            if(count($erreurs)>0){
                var_dump($erreurs);
                return "????";
            }

            $this->commandeModel=new CommandeModel($app);

            $this->commandeModel->updateEtat($donnees['id'],$donnees['etat']);

            return $this->show($app);
        }
        return "error";
    }

    public function showUser(Application $app){
        $this->commandeModel=new CommandeModel($app);
        $this->panierModel=new PanierModel($app);

        $idUser=$app['session']->get('user_id');
        $commandes=$this->commandeModel->getAllCommandesUser($idUser);
        $paniers=$this->panierModel->getUserPanierCommande($idUser);


        return $app["twig"]->render('frontOff/Commande/show.html.twig',['commandes'=>$commandes ,'paniers'=>$paniers]);
    }

    public function userCommandeAll(Application $app){
        $idUser=$app['session']->get('user_id');
        $this->panierModel=new PanierModel($app);
        $this->commandeModel=new CommandeModel($app);
        $this->produitModel=new ProduitModel($app);
        $this->etatModel=new EtatModel($app);
        $this->typeProduitModel = new TypeProduitModel($app);

        $nb=$this->panierModel->getNbPanierUser($idUser);

        $okpanier=true;
        $erreurs=[];
        if($nb>0){
            //verrification de la validation du panier

            $paniers=$this->panierModel->getUserPanier($idUser);
            for($i=0; $i<count($paniers); $i++){
                $produit=$this->produitModel->getProduit($paniers[$i]['produit_id']);

                if($paniers[$i]['dispo']!=1 ){
                    $erreurs[$paniers[$i]['id']]='indisponible';
                    $okpanier=false;
                }else{
                    if($paniers[$i]['quantite']>$produit['stock']){
                        $erreurs[$paniers[$i]['id']]=$produit['stock'].' en stock';
                        $okpanier=false;
                    }
                }

            }
        }else{
            $okpanier=false;
        }


        if($okpanier){

            //modif du stock
            $this->produitModel=new ProduitModel($app);
            $paniers=$this->panierModel->getUserPanier($idUser);
            for($i=0; $i<count($paniers); $i++){
                $produit=$this->produitModel->getProduit($paniers[$i]['produit_id']);
                $this->produitModel->changeStock($produit['id'],$produit['stock']-$paniers[$i]['quantite']);
            }

            $prix=floatval($this->panierModel->getUserSommePaniers($idUser)['total']);
            $idEtat=$this->etatModel->getIdEtats("A préparer")['id'];
            //on creer la commande
            $donnees = [
                'id' => null,
                'user_id' => $idUser,
                'prix' => $prix,
                'date_achat' => null,
                'etat_id' => $idEtat
            ];
            $this->commandeModel->userCommandeAll($donnees);

            return $app["twig"]->render('frontOff/Commande/message.html.twig',['montant'=>$prix]);
        }

        $this->myToken=new MyToken($app);
        
        $erreurs['stock']= "La quantité de l'article est supérieur à celle du stock ou panier vide";
        $id=$app['session']->get('user_id');
        $this->panierModel = new PanierModel($app);
        $panier = $this->panierModel->getUserPanier($id);
        $this->produitModel = new ProduitModel($app);
        $produits = $this->produitModel->getAllProduits();
        $total=$this->panierModel->getUserSommePaniers($id);

        $typeProduits = $this->typeProduitModel->getAllTypeProduits();

        return $app["twig"]->render('frontOff/frontOFFICE.html.twig',['dataProduit'=>$produits, 'dataPanier'=>$panier, 'dataType'=>$typeProduits, 'total'=>$total, 'erreurs'=>$erreurs]);
    }

    public function connect(Application $app) {
        $controllers = $app['controllers_factory'];

        $controllers->get('/', 'App\Controller\CommandeController::index')->bind('commande.index');
        $controllers->get('/show', 'App\Controller\CommandeController::show')->bind('commande.show');
        $controllers->get('/describe/{id}', 'App\Controller\CommandeController::describeCommande')->bind('commande.describe')->assert('id', '\d+');

        $controllers->get('/showUser', 'App\Controller\CommandeController::showUser')->bind('commande.showUser');

        $controllers->get('/commande', 'App\Controller\CommandeController::userCommandeAll')->bind('commande.userCommandeAll');

        $controllers->post('/update', 'App\Controller\CommandeController::changeEtat')->bind('commande.changeEtat');

        return $controllers;
    }


}