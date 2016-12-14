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


class CommentaireModel {

    public function __construct(Application $app) {
        $this->db = $app['db'];
    }


    public function getAllCommentaires($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('com.id', 'com.commentaire', 'com.date_commentaire','u.nom')
            ->from('commentaires', 'com')
            ->innerJoin('com', 'users', 'u', 'com.user_id=u.id')
            ->innerJoin('com', 'produits', 'p', 'com.produit_id=p.id')
            ->where('produit_id=?')
            ->addOrderBy('com.date_commentaire')
            ->setParameter(0,$id);
        return $queryBuilder->execute()->fetchAll();

    }

    public function insertCommentaire($donnees) {

        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder->insert('commentaires')
            ->values([
                'commentaire' => '?',
                'date_commentaire' => '?',
                'user_id' => '?',
                'produit_id' => '?'
            ])
            ->where('produits_id= ?')
            ->andWhere('user_id = ?')
            ->setParameter(0, $donnees['commentaire'])
            ->setParameter(1, $donnees['date_commentaire'])
            ->setParameter(2, $donnees['user_id'])
            ->setParameter(3, $donnees['produit_id'])
        ;
        return $queryBuilder->execute();

    }

}