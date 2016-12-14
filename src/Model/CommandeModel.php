<?php
/**
 * Created by PhpStorm.
 * User: guillaume
 * Date: 12/11/16
 * Time: 20:32
 */

namespace App\Model;

use Doctrine\DBAL\Query\QueryBuilder;
use Silex\Application;

class CommandeModel {

    private $db;

    public function __construct(Application $app) {
        $this->db = $app['db'];
    }

    public function getAllCommandes(){
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('c.id', 'c.user_id', 'c.prix', 'c.date_achat', 'c.etat_id', 'u.nom', 'e.libelle','e.id as etat')
            ->from('commandes', 'c')
            ->innerJoin('c', 'users', 'u', 'c.user_id=u.id')
            ->innerJoin('c', 'etats', 'e', 'c.etat_id=e.id')
            ->addOrderBy('c.id');
        return $queryBuilder->execute()->fetchAll();
    }

    public function getAllCommandesUser($idUser){
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('c.id','c.prix', 'c.date_achat', 'e.libelle','e.id as etat')
            ->from('commandes', 'c')
            ->innerJoin('c','etats','e','c.etat_id=e.id')
            ->where('c.user_id='.$idUser);
        return $queryBuilder->execute()->fetchAll();
    }

    public function insertCommandes($donnees) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder->insert('commandes')
            ->values([
                'id' => '?',
                'user_id' => '?',
                'prix' => '?',
                'date_achat' => '?',
                'etat_id' => '?'
            ])
            ->setParameter(0, $donnees['id'])
            ->setParameter(1, $donnees['user_id'])
            ->setParameter(2, $donnees['prix'])
            ->setParameter(3, $donnees['date_achat'])
            ->setParameter(4, $donnees['etat_id']);
        return $queryBuilder->execute();
    }

    public function findFreeId(){
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('c.id')
            ->from('commandes', 'c')
            ->addOrderBy('c.id');
        $retour = $queryBuilder->execute()->fetchAll();

        $id=1;

        for ($i=0; $i<count($retour); $i++){
            if($retour[$i]['id']==$id){
                $id++;
            }
        }
        return $id;
    }

    public function userCommandeAll($donnees){
        $this->db->beginTransaction();
        $this->db->prepare($this->insertCommandes($donnees));
        $lastinsertid=$this->db->lastInsertid();
        $this->commandeAllPaniers($donnees['user_id'],$lastinsertid);
        $this->db->commit();
    }

    public function commandeAllPaniers($idUser, $idCom){
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('paniers')
            ->set('commande_id',$idCom)
            ->where('user_id = '.$idUser)
            ->andWhere('commande_id is null');

        return $queryBuilder->execute();
    }

    public function getCommandes($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('c.id', 'c.user_id', 'c.prix', 'c.date_achat', 'c.etat_id', 'u.nom', 'e.libelle')
            ->from('commandes', 'c')
            ->innerJoin('c', 'users', 'u', 'c.user_id=u.id')
            ->innerJoin('c', 'etats', 'e', 'c.etat_id=e.id')
            ->where('c.id= :id')
            ->setParameter('id', $id);
        return $queryBuilder->execute()->fetch();
    }

    public function updateEtat($idCom, $idEtat){
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('commandes')
            ->set('etat_id',$idEtat)
            ->where('id='.$idCom);
        return $queryBuilder->execute();
    }

    public function updateCommandes($donnees) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('commandes')
            ->set('user_id', '?')
            ->set('prix','?')
            ->set('date_achat','?')
            ->set('etat_id','?')
            ->where('id= ?')
            ->setParameter(0, $donnees['user_id'])
            ->setParameter(1, $donnees['prix'])
            ->setParameter(2, $donnees['date_achat'])
            ->setParameter(3, $donnees['etat_id'])
            ->setParameter(4, $donnees['id']);
        return $queryBuilder->execute();
    }

    public function deleteCommandes($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->delete('commandes')
            ->where('id = :id')
            ->setParameter('id',(int)$id);
        return $queryBuilder->execute();
    }
}
