<?php
namespace App\Controller;

use App\Model\UserModel;

use App\Services\MyToken;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;   // modif version 2.0

use Symfony\Component\HttpFoundation\Request;   // pour utiliser request

//require "../../vendor/autoload.php";
use Gregwar\Captcha\CaptchaBuilder;


class UserController implements ControllerProviderInterface {

	private $userModel;

	private $myToken;

	public function index(Application $app) {
		return $this->connexionUser($app);
	}

	public function initmodel(Application $app){
	    $this->userModel=new UserModel($app);
	    $this->myToken=new MyToken($app);
    }

    public function show(Application $app){
        $this->userModel=new UserModel($app);
        $donnees=$this->userModel->getAllClient();
        return $app["twig"]->render('backOff/Client/show.html.twig',['data'=>$donnees]);
    }

	public function connexionUser(Application $app)
	{
		return $app["twig"]->render('v_session_connexion.html.twig');
	}

	public function validFormConnexionUser(Application $app, Request $req)
	{

		$app['session']->clear();
		$donnees['login']=$req->get('login');
		$donnees['password']=$req->get('password');

		$this->userModel = new UserModel($app);
		$data=$this->userModel->verif_login_mdp_Utilisateur($donnees['login'],$donnees['password']);

		if($data != NULL)
		{
			$app['session']->set('droit', $data['droit']);  //dans twig {{ app.session.get('droit') }}
			$app['session']->set('login', $data['login']);
			$app['session']->set('logged', 1);
			$app['session']->set('user_id', $data['id']);
			return $app->redirect($app["url_generator"]->generate("accueil"));
		}
		else
		{
			$app['session']->set('erreur','mot de passe ou login incorrect');
			return $app["twig"]->render('v_session_connexion.html.twig');
		}
	}

	private function captcha(Application $app){
        $builder = new CaptchaBuilder;
        $builder->build();

        $app['session']->set('phrase', $builder->getPhrase());

        return $builder->inline();
    }

    private function checkCaptcha(Application $app){
        if(isset($_POST['maPhrase'])) {
            if ($_POST['maPhrase'] == $app['session']->get('phrase')) {
                return true;
            }
        }
        return false;
    }

	public function addUser(Application $app){
        $this->mytoken=new MyToken($app);



        $this->userModel=new UserModel($app);
        $idUser = $app['session']->get('user_id');
        $donnees=$this->userModel->getUser($idUser);
        return $app["twig"]->render('frontOff/User/addUser.html.twig',['donnees'=>$donnees, 'builder'=>$this->captcha($app)]);

    }

    public function validFormAddUser(Application $app)
    {
        if(!MyToken::verif_token()){
            return $app["twig"]->render('v_error_token.html.twig',['tokenattendue'=>$_SESSION['token'],'tokenrecu'=>$_POST['token']]);
        }

        $this->userModel=new UserModel($app);

        if (isset($_POST['email']) && isset($_POST['password']) && isset($_POST['login']) && isset($_POST['nom'])
            && isset($_POST['code_postal']) && isset($_POST['ville']) && isset($_POST['adresse'])
        ) {
            $donnees = [
                'email' => htmlspecialchars($_POST['email']),
                'password' => htmlspecialchars($_POST['password']),
                'login' => htmlspecialchars($_POST['login']),
                'nom' => htmlspecialchars($_POST['nom']),
                'code_postal' => htmlspecialchars($_POST['code_postal']),
                'ville' => htmlspecialchars($_POST['ville']),
                'adresse' => htmlspecialchars($_POST['adresse'])
            ];

            $erreurs = [];

            if (!(filter_var($donnees['email'], FILTER_VALIDATE_EMAIL))) $erreurs['email'] = " Veuillez saisir une adresse email de ce type : aaaa@domaine.com ou .fr";
            if ((!preg_match("/^[A-Za-z0-9 ]{4,}/", $donnees['password']))) $erreurs['password'] = "Veuillez entrer un mot de pass de minimum 4 caractères";
            if ((!preg_match("/^[A-Za-z0-9 ]{2,}/", $donnees['login']))) $erreurs['login'] = "Veuillez entrer un login de minimum 2 caractères";
            if ((!preg_match("/^[A-Za-z ]{2,}/", $donnees['nom']))) $erreurs['nom'] = " Veuillez entrer un nom correcte de minimum 2 caractère";
            if ((!preg_match("/^[0-9]{5}/", $donnees['code_postal']))) $erreurs['code_postal'] = "Veuillez écrire un code postal de 5 chiffre";
            if ((!preg_match("/^[A-Za-z ]{2,}/", $donnees['ville']))) $erreurs['ville'] = "Entrer une ville valable";
            if ((!preg_match("/^[A-Za-z0-9 ]{1,}/", $donnees['adresse']))) $erreurs['adresse'] = "Entrer une adresse postale vablable";
            if (($_POST['password2']) != $donnees['password']) $erreurs['password2'] = " Veuillez réecrire une deuxième fois le mot de passe";

            if($donnees['login']!=$app['session']->get('login') and !$this->userModel->freeLogin($donnees['login'])){
               $erreurs['login']='Login dejà utilisé';
            }

            if(!$this->checkCaptcha($app)){
                $erreurs['captcha']="Captcha incorrecte";
            }



        if (count($erreurs)>0){
            $this->mytoken=new MyToken($app);
            $builder = new CaptchaBuilder;
            $builder->build();
            return $app["twig"]->render('frontOff/User/addUser.html.twig',['donnees'=>$donnees,'erreurs'=>$erreurs,'builder'=>$this->captcha($app)]);

        }

            $this->userModel->insertUser($donnees);
            return $app["twig"]->render('frontOff/User/messageAddUser.html.twig');

        }else{
            return $app->abort(404,'Erreur problème formulaire');
}
    }


	public function updateUser(Application $app){

        $this->mytoken=new MyToken($app);

        $this->userModel=new UserModel($app);

        $idUser = $app['session']->get('user_id');

        $donnees=$this->userModel->getUser($idUser);

        return $app["twig"]->render('frontOff/User/editUser.html.twig',['donnees'=>$donnees]);
    }


    public function validUpdateUser(Application $app){

        if(!MyToken::verif_token()){
            return $app["twig"]->render('v_error_token.html.twig',['tokenattendue'=>$_SESSION['token'],'tokenrecu'=>$_POST['token']]);
        }

        $this->userModel=new UserModel($app);
        $idUser = $app['session']->get('user_id');

        if (($_POST['email']) && isset($_POST['password']) && isset($_POST['login']) && isset($_POST['nom'])
            && isset($_POST['code_postal']) && isset($_POST['ville']) && isset($_POST['adresse'])) {
            $donnees = [
                'id' => $idUser,
                'email' => htmlspecialchars($_POST['email']),
                'password' => htmlspecialchars($_POST['password']),
                'login' => htmlspecialchars($_POST['login']),
                'nom' => htmlspecialchars($_POST['nom']),
                'code_postal' => htmlspecialchars($_POST['code_postal']),
                'ville' => htmlspecialchars($_POST['ville']),
                'adresse' => htmlspecialchars($_POST['adresse'])
            ];

            $erreurs=[];
            if (! is_numeric($donnees['id'])) $erreurs['id']= 'saisir une valeur numérique';
            //"^[_a-z0-9-]+(\\.[_a-z0-9-]+)*@[a-z0-9-]+(\\.[a-z0-9-]+)+$" - "/^[a-zA-Z0-9_]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$]/i"
            if (!( filter_var($donnees['email'],FILTER_VALIDATE_EMAIL))) $erreurs['email']= " Veuillez saisir une adresse email de ce type : aaaa@domaine.com ou .fr";
            //if ((! preg_match("/^[A-Za-z0-9 ]{4,}/",$donnees['email']))) $erreurs['email']= " Veuillez saisir une adresse email de ce type : aaaa@domaine.com/fr";
            if((! preg_match("/^[A-Za-z0-9 ]{4,}/",$donnees['password']))) $erreurs['password']="Veuillez entrer un mot de pass de minimum 4 caractères";
            if((! preg_match("/^[A-Za-z0-9 ]{2,}/",$donnees['login']))) $erreurs['login']="Veuillez entrer un login de minimum 2 caractères";
            if ((!preg_match("/^[A-Za-z ]{2,}/",$donnees['nom']))) $erreurs['nom'] = " Veuillez entrer un nom correcte de minimum 2 caractère";
            if ((!preg_match("/^[0-9]{5}/",$donnees['code_postal']))) $erreurs['code_postal'] = "Veuillez écrire un code postal de 5 chiffre";
            if ((!preg_match("/^[A-Za-z ]{2,}/",$donnees['ville']))) $erreurs['ville'] ="Entrer une ville valable";
            if((!preg_match("/^[A-Za-z0-9 ]{1,}/",$donnees['adresse']))) $erreurs['adresse']="Entrer une adresse postale vablable";
            if (($_POST['password2']) != $donnees['password']) $erreurs['password2'] =" Veuillez réecrire une deuxième fois le mot de pass";
            if($donnees['login']!=$app['session']->get('login') and !$this->userModel->freeLogin($donnees['login'])){
                $erreurs['login']='Login dejà utilisé';
            }

            if (count($erreurs)>0) {
                //$donnees['password']=$donnees['password'];//???
                $this->mytoken=new MyToken($app);
                return $app["twig"]->render('frontOff/User/editUser.html.twig',['donnees'=>$donnees,'erreurs'=>$erreurs]);
            }

            $this->userModel->updateUser($donnees,$idUser);
            return $app["twig"]->render('frontOff/User/message.html.twig');

        }else{
            return $app->abort(404,'Erreur problème formulaire');
        }

    }


    public function delete(Application $app, $id) {
        $this->mytoken=new MyToken($app);

        $this->userModel = new UserModel($app);
        $donnees = $this->userModel->getUser($id);
        return $app["twig"]->render('backOff/Client/delete.html.twig',['client'=>$donnees]);
    }

    public function validFormDelete(Application $app, Request $req) {
        if(!MyToken::verif_token()){
            return $app["twig"]->render('v_error_token.html.twig',['tokenattendue'=>$_SESSION['token'],'tokenrecu'=>$_POST['token']]);
        }

        $id=$app->escape($req->get('id'));
        if (is_numeric($id)) {
            $this->userModel=new UserModel($app);
            $this->userModel->deleteUser($id);
            return $app->redirect($app["url_generator"]->generate("client.show"));
        }
        else
            return $app->abort(404, 'error Pb id form Delete');
    }



	public function deconnexionSession(Application $app)
	{
		$app['session']->clear();
		$app['session']->getFlashBag()->add('msg', 'vous êtes déconnecté');
		return $app->redirect($app["url_generator"]->generate("accueil"));
	}

	public function connect(Application $app) {
		$controllers = $app['controllers_factory'];
		$controllers->match('/', 'App\Controller\UserController::index')->bind('user.index');

        $controllers->get('/show', 'App\Controller\UserController::show')->bind('client.show');

		$controllers->get('/login', 'App\Controller\UserController::connexionUser')->bind('user.login');
		$controllers->post('/login', 'App\Controller\UserController::validFormConnexionUser')->bind('user.validFormlogin');
		$controllers->get('/logout', 'App\Controller\UserController::deconnexionSession')->bind('user.logout');

        $controllers->match('/addUser', 'App\Controller\UserController::addUser')->bind('user.addUser');
        $controllers->post('/validAddUser','App\Controller\UserController::validFormAddUser')->bind('user.validFormAddUser');

        $controllers->match('/updateProfil','App\Controller\UserController::updateUser')->bind('user.updateUser');
        $controllers->post('/validupdateProfil','App\Controller\UserController::validUpdateUser')->bind('user.validUpdateUser');

        $controllers->get('/delete/{id}', 'App\Controller\UserController::delete')->bind('client.delete')->assert('id', '\d+');
        $controllers->delete('/delete', 'App\Controller\UserController::validFormDelete')->bind('client.validFormDelete');

		return $controllers;
	}

}