<?php
/**
 * Created by PhpStorm.
 * User: guillaume
 * Date: 12/11/16
 * Time: 20:33
 */

namespace App\Model;

use Doctrine\DBAL\Query\QueryBuilder;
use Silex\Application;


class EtatModel {

    public function __construct(Application $app) {
        $this->db = $app['db'];
    }

    public function getAllEtats() {

        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('e.id', 'e.libelle')
            ->from('etats', 'e')
            ->addOrderBy('e.libelle', 'ASC');
        return $queryBuilder->execute()->fetchAll();
    }

    public function insertEtats($donnees) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder->insert('etats')
            ->values([
                'libelle' => '?'
            ])
            ->setParameter(0, $donnees['libelle']);
        return $queryBuilder->execute();
    }

    public function getEtats($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('e.id', 'e.libelle')
            ->from('etats', 'e')
            ->where('id= :id')
            ->setParameter('id', $id);
        return $queryBuilder->execute()->fetch();
    }

    public function getIdEtats($name){
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('e.id')
            ->from('etats', 'e')
            ->where("e.libelle like '".$name."'");
        return $queryBuilder->execute()->fetch();
    }

    public function updateEtats($donnees) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('etats')
            ->set('libelle', '?')
            ->where('id= ?')
            ->setParameter(0, $donnees['libelle'])
            ->setParameter(1, $donnees['id']);
        return $queryBuilder->execute();
    }

    public function deleteEtats($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->delete('etats')
            ->where('id = :id')
            ->setParameter('id',(int)$id);
        return $queryBuilder->execute();
    }
}