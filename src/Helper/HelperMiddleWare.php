<?php
/**
 * Created by PhpStorm.
 * User: guillaume
 * Date: 31/10/16
 * Time: 20:06
 */

namespace App\Helper;

use Symfony\Component\HttpFoundation\Request;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;


class HelperMiddleWare {



    public function verifDroit(Application $app) {

        $app->before(function (\Symfony\Component\HttpFoundation\Request $request) use ($app) {

            $droitAdmin='DROITadmin';
            $droitClient='DROITclient';

            $actions=[["produit.show",$droitAdmin],["produit.delete",$droitAdmin],["produit.validFormDelete",$droitAdmin],["produit.last",$droitClient,["produit.edit",$droitAdmin],["produit.validFormEdit",$droitAdmin]],
                ["panier.showPanierUser",$droitClient],["panier.showAccueilClient",$droitClient], ["panier.addPanierUser",$droitClient],["panier.deletePanierUser",$droitClient], ["panier.updatePanierUser",$droitClient],
                ["user.updateUser",$droitClient], ["user.validUpdateUser",$droitClient], ["client.delete",$droitAdmin],["client.validFormDelete",$droitAdmin],
                ["commande.show",$droitAdmin],["commande.describe",$droitAdmin], ["commande.showUser",$droitClient],["commande.userCommandeAll",$droitClient],["commande.changeEtat",$droitAdmin],["commentaire.addCommentaire",$droitClient],
                ["commentaire.addCommentaire",$droitClient]];

            foreach ($actions as $action){

                $route=$action[0];
                $droit=$action[1];

                if($droit=='DROITadmin'){
                    $nomRoute = $request->get("_route");
                    if ($app['session']->get('droit') != 'DROITadmin' && $nomRoute == $route) {
                        return $app->redirect($app["url_generator"]->generate("index.errorDroit"));
                    }
                }else{
                    $nomRoute = $request->get("_route");
                    if ($app['session']->get('droit') != 'DROITadmin'&&$app['session']->get('droit') != 'DROITclient' && $nomRoute == $route) {
                        return $app->redirect($app["url_generator"]->generate("index.errorDroit"));
                    }
                }
            }
        });

        return $app;
    }

}