<?php
namespace App\Controller;

use App\Model\TypeProduitModel;
use Silex\Application;
use Silex\Api\ControllerProviderInterface; // modif version 2.0
use App\Model\PanierModel;
use App\Model\ProduitModel;

class IndexController implements ControllerProviderInterface
{
    private $panierModel;
    private $userModel;
    private $produitModel;
    private $commandeModel;
    private $typeProduitModel;


    public function index(Application $app)
    {

        if ($app['session']->get('droit') == 'DROITclient')  {
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

            $this->typeProduitModel= new TypeProduitModel($app);
            $typeProduits = $this->typeProduitModel->getAllTypeProduits();

            $total=$this->panierModel->getUserSommePaniers($id);



            return $app["twig"]->render('frontOff/frontOFFICE.html.twig',['dataProduit'=>$produits, 'dataPanier'=>$panier,'dataType'=>$typeProduits, 'total'=>$total]);
        }
        // remplacer par une redirection :  return $app->redirect($app["url_generator"]->generate("Panier.index"));
        if ($app['session']->get('droit') == 'DROITadmin') {
            return $app["twig"]->render("backOff/backOFFICE.html.twig");
        }
        // remplacer par une redirection


        return $app["twig"]->render("accueil.html.twig");
    }

    public function errorDroit(Application $app){
        return $app["twig"]->render("errorDroit.html.twig");
    }

    public function connect(Application $app)
    {
        $index = $app['controllers_factory'];
        $index->match("/", 'App\Controller\IndexController::index')->bind('accueil');
        $index->match("/error", 'App\Controller\IndexController::errorDroit')->bind('index.errorDroit');
        return $index;
    }
}
