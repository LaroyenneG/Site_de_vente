<?php
/**
 * Created by PhpStorm.
 * User: guillaume
 * Date: 30/10/16
 * Time: 20:43
 */

namespace App\Helper;

class Helper_date{

    public static function valideDate($date){

        if ( preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$date)){
            $month = $date[5].$date[6];
            $day = $date[8].$date[9];
            $year = $date[0].$date[1].$date[2].$date[3];
            if(checkdate($month, $day, $year)){
                return true;
            }
        }


        return false;
    }

    public static function humaintosql($date){
        if ( preg_match("/[0-9]{2}\/[0-9]{2}\/[0-9]{4}/",$date)){
            $month = $date[3].$date[4];
            $day = $date[0].$date[1];
            $year = $date[6].$date[7].$date[8].$date[9];

            return $year."-".$month."-".$day;
        }
        return $date;

    }

    public static function sqltohumain($date){
        if ( preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$date)){
            $month = $date[5].$date[6];
            $day =  $date[8].$date[9];
            $year = $date[0].$date[1].$date[2].$date[3];

            return $day."/".$month."/".$year;
        }
        return $date;
    }

    public static function ctof($date){
        if ( preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$date)){
            $month = $date[5].$date[6];
            $day =  $date[8].$date[9];
            $year = $date[0].$date[1].$date[2].$date[3];

            return $day."-".$month."-".$year;
        }
        return $date;
    }

    public static function ctoa($date){
        if ( preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$date)){
            $month = $date[5].$date[6];
            $day = $date[8].$date[9];
            $year = $date[0].$date[1].$date[2].$date[3];

            return $month."-".$day."-".$year;
        }
        return $date;
    }

    public static function ftoa($date){
        if ( preg_match("/[0-9]{2}-[0-9]{2}-[0-9]{4}/",$date)){
            $month = $date[3].$date[4];
            $day = $date[0].$date[1];
            $year = $date[6].$date[7].$date[8].$date[9];

            return $month."-".$day."-".$year;
        }
        return $date;
    }

    public static function atof($date){
        if ( preg_match("/[0-9]{2}-[0-9]{2}-[0-9]{4}/",$date)){
            $month = $date[0].$date[1];
            $day = $date[3].$date[4];
            $year = $date[6].$date[7].$date[8].$date[9];

            return $day."-".$month."-".$year;
        }
        return $date;
    }

    public static function view($date){
        if ( preg_match("/[0-9]{2}-[0-9]{2}-[0-9]{4}/",$date)){
            $val1 = $date[0].$date[1];
            $val2 = $date[3].$date[4];
            $year = $date[6].$date[7].$date[8].$date[9];

            return $val1."/".$val2."/".$year;
        }

        if ( preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$date)){
            $val1 = $date[5].$date[6];
            $val2 =  $date[8].$date[9];
            $year = $date[0].$date[1].$date[2].$date[3];

            return $year."/".$val1."/".$val2;
        }

        return $date;
    }
}