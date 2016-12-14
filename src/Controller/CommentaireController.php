<?php

namespace App\Controller;


use App\Model\CommentaireModel;

use App\Model\ProduitModel;
use App\Model\TypeProduitModel;
use App\Services\MyToken;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\Validator\Constraints as Assert;   // pour utiliser la validation
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;


class CommentaireController implements ControllerProviderInterface {
    private $commentaireModel;
    private $produitModel;

    private $myToken;

    public function initModel(Application $app){
        $this->commentaireModel=new CommentaireModel($app);
        $this->produitModel=new ProduitModel($app);
        $this->myToken=new MyToken($app);
    }

    public function index(Application $app)
    {

    }



    public function addCommentaire (Application $app)
    {
        if(!MyToken::verif_token()){
            return $app["twig"]->render('v_error_token.html.twig',['tokenattendue'=>$_SESSION['token'],'tokenrecu'=>$_POST['token']]);
        }

        $idUser = $app["session"]->get('user_id');

        if (isset($_POST['comments']) && isset($_POST['id_produit'])) {
            $donnees = [
                'commentaire' => htmlspecialchars($_POST['comments']),
                'date_commentaire' => null,
                'user_id' => $idUser,
                'produit_id' => htmlspecialchars($_POST['id_produit'])
            ];

            $erreurs=[];
            if(!is_numeric($donnees['user_id']))$erreurs['user_id'] =" User id non numérique";
            if (!is_numeric($donnees['produit_id']))$erreurs['produit_id']=" Id dur produit non numérique ";

            if ( count($erreurs)>0){
                return "????";
            }
            $this->commentaireModel = new CommentaireModel($app);
            $this->commentaireModel->insertCommentaire($donnees);
            $this->typeProduitModel = new TypeProduitModel($app);
            $this->produitModel = new ProduitModel($app);
            $produit = $this->produitModel->getProduit($donnees['produit_id']);
            $commentaires = $this-> commentaireModel->getAllCommentaires($donnees['produit_id']);
            return $app["twig"]->render('frontOff/Produit/describeProduit.html.twig',['produit'=>$produit,'commentaires'=>$commentaires]);

        }

        return "lol";
    }








    public function connect(Application $app) {

        $controllers = $app['controllers_factory'];

        $controllers->get('/', 'App\Controller\CommentaireController::index')->bind('commentaire.index');
        $controllers->post('/commentaires', 'App\Controller\CommentaireController::addCommentaire')->bind('commentaire.addCommentaire');

        return $controllers;
    }
}