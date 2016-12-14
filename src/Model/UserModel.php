<?php
namespace App\Model;

use Silex\Application;
use Doctrine\DBAL\Query\QueryBuilder;;

class UserModel {

	private $db;

	public function __construct(Application $app) {
		$this->db = $app['db'];
	}

	public function verif_login_mdp_Utilisateur($login,$mdp){

		$sql = "SELECT id,login,password,droit FROM users WHERE login = ? AND password = ?";
		$res=$this->db->executeQuery($sql,[$login,md5($mdp)]);   //md5($mdp);
		if($res->rowCount()==1)
			return $res->fetch();
		else
			return false;
	}

	public function getUser($user_id) {
		$queryBuilder = new QueryBuilder($this->db);
		$queryBuilder
			->select('*')
			->from('users')
			->where('id = :idUser')
			->setParameter('idUser', $user_id);
		return $queryBuilder->execute()->fetch();

	}

	public function freeLogin($login){

        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('*')
            ->from('users')
            ->where('login = :login')
            ->setParameter('login', $login);
        $values=$queryBuilder->execute()->fetchAll();

        if(count($values)<1){
            return true;
        }
        return false;
    }

    public function insertUser($donnees){
	    //$donnees['password']=md5($donnees['password']);
	    $queryBuilder = new QueryBuilder($this->db);
	    $queryBuilder
            ->insert('users')
            ->values([
                'email'=>'?',
                'password'=>'?',
                'login'=>'?',
                'nom'=>'?',
                'code_postal'=>'?',
                'ville'=>'?',
                'adresse'=>'?',
                'droit'=>'?'
            ])

            ->setParameter(0, $donnees['email'])
            ->setParameter(1, MD5($donnees['password']))
            ->setParameter(2, $donnees['login'])
            ->setParameter(3, $donnees['nom'])
            ->setParameter(4, $donnees['code_postal'])
            ->setParameter(5, $donnees['ville'])
            ->setParameter(6, $donnees['adresse'])
            ->setParameter(7,'DROITclient')
            ;
        return $queryBuilder->execute();
    }

    public function updateUser($donnees,$userId) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('users')
            ->set('email', '?')
            ->set('password','?')
            ->set('login','?')
            ->set('nom','?')
            ->set('code_postal','?')
            ->set('ville','?')
            ->set('adresse','?')
            ->where('id = ?')
            ->setParameter(0, $donnees['email'])
            ->setParameter(1, MD5($donnees['password']))
            ->setParameter(2, $donnees['login'])
            ->setParameter(3, $donnees['nom'])
            ->setParameter(4, $donnees['code_postal'])
            ->setParameter(5, $donnees['ville'])
            ->setParameter(6, $donnees['adresse'])
            ->setParameter(7,$userId);
        return $queryBuilder->execute();
    }

    public function getAllClient(){
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('*')
            ->from('users')
            ->where("droit like 'DROITclient'");
        return $queryBuilder->execute()->fetchAll();
    }

    public function deleteUser($id){
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->delete('users')
            ->where('id = :id')
            ->setParameter('id',$id);
        return $queryBuilder->execute();
    }
}