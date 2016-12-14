<?php

namespace App\Helper;

class HelperPrix {
    const TAUX_TVA=0.2;
    public static $taux_tva=0.2;

    public static function view($prix="") {
        if(filter_var($prix, FILTER_VALIDATE_FLOAT) !== false){
            //return number_format((float)$prix,2)." &euro;";
            return number_format((float)$prix,2)." €";
        }else{
            return false;
        }

    }
    public static function viewTVA($prix="") {
        if(filter_var($prix, FILTER_VALIDATE_FLOAT) !== false){
            $tva=$prix*self::TAUX_TVA;  // il n'est pas possible d'utiliser this dans une méthode static
            return self::view($tva);    // donc TAUX_TVA est considéré comme un attribut static
        }else{
            return false;
        }
    }
    public static function viewHT($prix="") {
        if(filter_var($prix, FILTER_VALIDATE_FLOAT) !== false){
            $prixHT=$prix*(1-SELF::$taux_tva);
            return self::view($prixHT);
        }else{
            return false;
        }
    }

    public static function goodFloat($float){
        $value=explode( '.', $float);


        if(count($value)>2||count($value)==0){
            return false;
        }

        if (count($value)==2){
            if(is_numeric($value[0]) && is_numeric($value[1])){
                if(strlen($value[0])<=5 && strlen($value[1])<=2 && (strlen($value[0])+strlen($value[1])<=5)){
                    return true;
                }
            }
        }

        if (count($value)==1){
            if(is_numeric($value[0])){
                if(strlen($value[0])<=5){
                    return true;
                }
            }
        }

        return false;
    }
}