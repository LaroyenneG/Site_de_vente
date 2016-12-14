<?php

namespace App\Model;

use Doctrine\DBAL\Query\QueryBuilder;
use Silex\Application;

class ProduitModel {

    private $db;

    public function __construct(Application $app) {
        $this->db = $app['db'];
    }

    public function getAllProduits() {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('p.id', 't.libelle', 'p.nom', 'p.prix', 'p.photo', 'p.info','p.stock', 'p.dispo','p.typeProduit_id')
            ->from('produits', 'p')
            ->innerJoin('p', 'typeProduits', 't', 'p.typeProduit_id=t.id')
            ->addOrderBy('p.nom', 'ASC');
        return $queryBuilder->execute()->fetchAll();

    }

    public function insertProduit($donnees) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder->insert('produits')
            ->values([
                'nom' => '?',
                'typeProduit_id' => '?',
                'prix' => '?',
                'photo' => '?',
                'stock' => '?',
                'dispo' => '0'
            ])
            ->setParameter(0, $donnees['nom'])
            ->setParameter(1, $donnees['typeProduit_id'])
            ->setParameter(2, $donnees['prix'])
            ->setParameter(3, $donnees['photo'])
            ->setParameter(4, $donnees['stock'])
        ;
        return $queryBuilder->execute();
    }

    function getProduit($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('p.id', 't.libelle', 'p.nom', 'p.prix', 'p.photo','p.info','p.stock', 'p.dispo','p.typeProduit_id')
            ->from('produits', 'p')
            ->innerJoin('p', 'typeProduits', 't', 'p.typeProduit_id=t.id')
            ->where('p.id= :id')
            ->setParameter('id', $id);
        return $queryBuilder->execute()->fetch();
    }

    public function changeStock($id, $stock){
        $dispo=1;
        if($stock<1){
            $dispo=0;
        }
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('produits')
            ->set('stock', '?')
            ->set('dispo','?')
            ->where('id= ?')
            ->setParameter(0, $stock)
            ->setParameter(1, $dispo)
            ->setParameter(2, $id);
        return $queryBuilder->execute();
    }

    public function updateProduit($donnees) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('produits')
            ->set('nom', '?')
            ->set('typeProduit_id','?')
            ->set('prix','?')
            ->set('photo','?')
            ->set('stock','?')
            ->set('dispo','?')
            ->where('id= ?')
            ->setParameter(0, $donnees['nom'])
            ->setParameter(1, $donnees['typeProduit_id'])
            ->setParameter(2, $donnees['prix'])
            ->setParameter(3, $donnees['photo'])
            ->setParameter(4, $donnees['stock'])
            ->setParameter(5, $donnees['dispo'])
            ->setParameter(6, $donnees['id']);
        return $queryBuilder->execute();
    }

    public function deleteProduit($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->delete('produits')
            ->where('id = :id')
            ->setParameter('id',(int)$id);
        return $queryBuilder->execute();
    }

    public function searchProduit($in){
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('p.id', 't.libelle', 'p.nom', 'p.prix', 'p.photo','p.stock', 'p.dispo')
            ->from('produits', 'p')
            ->innerJoin('p', 'typeProduits', 't', 'p.typeProduit_id=t.id')
            ->where("t.libelle like '%".$in."%'")
            ->orWhere("p.nom like '%".$in."%'")
            ->orWhere("p.prix like '".$in."%'")
            ->addOrderBy('p.nom', 'ASC');
        return $queryBuilder->execute()->fetchAll();
    }

    public function searchProduitType($type){
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('p.id', 't.libelle', 'p.nom', 'p.prix', 'p.photo','p.stock', 'p.dispo')
            ->from('produits', 'p')
            ->innerJoin('p', 'typeProduits', 't', 'p.typeProduit_id=t.id')
            ->where("t.libelle like '%".$type."%'")
            -> addOrderBy('p.nom','ASC');
        return $queryBuilder->execute()->fetchAll();
    }

    public function getAllStockProduits(){
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('p.id', 't.libelle', 'p.nom', 'p.prix', 'p.photo', 'p.info','p.stock', 'p.dispo')
            ->from('produits', 'p')
            ->innerJoin('p', 'typeProduits', 't', 'p.typeProduit_id=t.id')
            ->addOrderBy('p.stock');
        $value=$queryBuilder->execute()->fetchAll();

        for($i=0; $i<count($value); $i++){
            $value[$i]['warning']='Ok';
            if($value[$i]['dispo']==0||$value[$i]['stock']<10){
                $value[$i]['warning']='Attention';
            }
        }

        return $value;
    }

}