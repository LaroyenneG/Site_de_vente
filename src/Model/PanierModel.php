<?php
/**
 * Created by PhpStorm.
 * User: guillaume
 * Date: 12/11/16
 * Time: 20:31
 */

namespace App\Model;


use Doctrine\DBAL\Query\QueryBuilder;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class PanierModel {
    private $db;

    public function __construct(Application $app) {
        $this->db = $app['db'];
    }

    public function getAllPaniers() {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('p.id', 'p.quantite', 'p.prix', 'p.dateAjoutPanier', 'p.user_id', 'p.produit_id', 'p.commande_id')
            ->from('paniers', 'p');
        return $queryBuilder->execute()->fetchAll();

    }

    public function getUserPanier($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('p.id', 'p.quantite', 'p.prix', 'p.dateAjoutPanier', 'p.user_id', 'p.produit_id', 'p.commande_id','x.nom', 'x.photo','x.stock','x.dispo')
            ->from('paniers', 'p')
            ->innerJoin('p', 'produits', 'x', 'p.produit_id=x.id')
            ->where('p.user_id = '.$id)
            ->andWhere('p.commande_id is null');

        return $queryBuilder->execute()->fetchAll();
    }

    public function getNbPanierUser($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('count(p.id) as nb')
            ->from('paniers', 'p')
            ->where('p.user_id ='.$id)
            ->andWhere('p.commande_id is null')
            ->setParameter('id', $id);

        $retour=$queryBuilder->execute()->fetch();
        return $retour['nb'];
    }

    public function getUserPanierDelete($id){
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('p.id', 'p.quantite', 'p.prix', 'p.dateAjoutPanier', 'p.user_id', 'p.produit_id', 'p.commande_id','x.nom', 'x.photo')
            ->from('paniers', 'p')
            ->innerJoin('p', 'produits', 'x', 'p.produit_id=x.id')
            ->where('p.user_id = :id')
            ->setParameter('id', $id);
        return $queryBuilder->execute()->fetch();

    }

    public function getUserSommePaniers($id){
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('SUM(p.prix) as total')
            ->from('paniers', 'p')
            ->where('p.user_id = :id')
            ->andWhere('p.commande_id is null')
            ->setParameter('id', $id);
        return $queryBuilder->execute()->fetch();
    }

    public function clearPaniers($id){
        /*
         * gestion des redondances
         */
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('p.id', 'p.quantite','produit_id', 'prix')
            ->from('paniers', 'p')
            ->where('p.user_id = '.$id)
            ->andWhere('p.commande_id IS NULL');

        $data = $queryBuilder->execute()->fetchAll();

        $idcopy=[];
        $quantite=0;
        $value=[];
        for($i = 0; $i < count($data); ++$i) {
            $value=$data[$i];

            for($j = 0; $j < count($data); ++$j) {
                if($i!=$j&&$value['produit_id']==$data[$j]['produit_id']){
                    $b=true;
                    for($x=0; $x<count($idcopy) ;$x++){
                        if($idcopy[$x]==$value['id']){
                            $b=false;
                        }
                    }
                    if($b){
                        $idcopy[count($idcopy)]=$value['id'];
                        $quantite+=$value['quantite'];
                    }
                }
            }
        }

        if(count($idcopy)>1){

            $queryBuilder = new QueryBuilder($this->db);
            $queryBuilder
                ->select('p.prix')
                ->from('produits', 'p')
                ->innerJoin('p', 'typeProduits', 't', 'p.typeProduit_id=t.id')
                ->where('p.id= :id')
                ->setParameter('id', $value['produit_id']);
            $recherhce=$queryBuilder->execute()->fetch();

            for ($i=1; $i<count($idcopy);$i++){
                $this->deleteUserPaniers($idcopy[$i],$id);
            }
            $queryBuilder = new QueryBuilder($this->db);
            $queryBuilder
                ->update('paniers')
                ->set('quantite', $quantite)
                ->set('prix', $recherhce['prix']*$quantite)
                ->set('commande_id', 'null')
                ->where('id='.$idcopy[0]);
            return $queryBuilder->execute();
        }
    }

    public function insertPaniers($donnees) {
        $recherche = new QueryBuilder($this->db);
        $recherche
            ->select('p.id', 'p.quantite', 'p.prix', 'p.dateAjoutPanier', 'p.user_id', 'p.produit_id', 'p.commande_id')
            ->from('paniers', 'p')
            ->where('p.user_id = '.$donnees['user_id'])
            ->andWhere('p.produit_id = '.$donnees['produit_id'])
            ->andWhere('p.commande_id is null');
        $result=$recherche->execute()->fetchAll();

        if(count($result)>0){

            $queryBuilder = new QueryBuilder($this->db);
            $queryBuilder
                ->update('paniers')
                ->set('quantite', ($donnees['quantite']+$result[0]['quantite']))
                ->set('prix', ($donnees['prix']+$result[0]['prix']))
                ->set('dateAjoutPanier', 'null')
                ->set('user_id', $donnees['user_id'])
                ->set('produit_id', $donnees['produit_id'])
                ->set('commande_id', 'null')
                ->where('id='.$result[0]['id']);
            return $queryBuilder->execute();

        }else{
            $queryBuilder = new QueryBuilder($this->db);
            $queryBuilder->insert('paniers')
                ->values([
                    'quantite' => '?',
                    'prix' => '?',
                    'dateAjoutPanier' => '?',
                    'user_id' => '?',
                    'produit_id' => '?',
                    'commande_id' => '?'
                ])
                ->setParameter(0, $donnees['quantite'])
                ->setParameter(1, $donnees['prix'])
                ->setParameter(2, $donnees['dateAjoutPanier'])
                ->setParameter(3, $donnees['user_id'])
                ->setParameter(4, $donnees['produit_id'])
                ->setParameter(5, $donnees['commande_id']);
            return $queryBuilder->execute();
        }
    }

    function getPaniers($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('p.id', 'p.quantite', 'p.prix', 'p.dateAjoutPanier','p.produit_id')
            ->from('paniers', 'p')
            ->where('p.id='.$id);
        return $queryBuilder->execute()->fetch();
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

    public function commadePaniers($id, $idUser, $idCom){
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('paniers')
            ->set('commande_id','?')
            ->where('id=', '?')
            ->andWhere('user_id', '?')
            ->setParameter(0, $idCom)
            ->setParameter(1, $id)
            ->setParameter(2, $idUser);
        return $queryBuilder->execute();
    }

    public function updatePaniers($donnees) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('paniers')
            ->set('quantite', '?')
            ->set('prix','?')
            ->set('dateAjoutPanier','?')
            ->set('user_id','?')
            ->set('produit_id','?')
            ->set('commande_id','?')
            ->where('id= ?')
            ->setParameter(0, $donnees['quantite'])
            ->setParameter(1, $donnees['prix'])
            ->setParameter(2, $donnees['dateAjoutPanier'])
            ->setParameter(3, $donnees['user_id'])
            ->setParameter(4, $donnees['produit_id'])
            ->setParameter(5, $donnees['commande_id'])
            ->setParameter(6, $donnees['id']);
        return $queryBuilder->execute();
    }

    public function deleteUserPaniers($id, $user){
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->delete('paniers')
            ->where('id = ?')
            ->andWhere('user_id = ?')
            ->setParameter(0,$id)
            ->setParameter(1,$user);
        return $queryBuilder->execute();
    }

    public function deletePaniers($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->delete('paniers')
            ->where('id = ?')
            ->setParameter(0,$id);
        return $queryBuilder->execute();
    }

    public function getUserPanierCommande($idUser) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('p.id', 'p.quantite', 'p.prix', 'p.dateAjoutPanier', 'p.produit_id', 'p.commande_id','x.nom', 'x.photo')
            ->from('paniers', 'p')
            ->innerJoin('p', 'produits', 'x', 'p.produit_id=x.id')
            ->where('p.user_id = '.$idUser)
            ->andWhere('p.commande_id is not null');
        return $queryBuilder->execute()->fetchAll();
    }

    public function cleanPanierForDate(){
        $queryBuilder = new QueryBuilder($this->db);
        $nbjours=5;
        $queryBuilder
            ->delete('paniers')
            ->where('DATEDIFF(NOW(),dateAjoutPanier)>'.$nbjours)
            ->andWhere('commande_id=NULL');
        return $queryBuilder->execute();
    }
}