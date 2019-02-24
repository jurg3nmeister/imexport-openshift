<?php
/**
 * Description of BittionDateComponent: to change date formats for different servers globally
 * It will be added to git ignore because according to the server it could change
 * @author rey
 */

class BittionDateComponent extends Component {

//    Created: 15/04/2015 | Developer: reyro | Description: BEFORE SENDING date to the DB
    public function fnSetFormatDate($date) {
        $date = explode('/', $date);
//        dd/mm/yy
        $arrayDate['day'] = $date[0];
        $arrayDate['month'] = $date[1];
        $arrayDate['year'] = $date[2];
        return $arrayDate;
    }

//    Created: 09/04/2015 | Developer: reyro | Description: BEFORE SENDING date to the DB set to yyyy-mm-dd unix format
    public function fnSetUnixFormatDate($date) {
        $date = explode('/', $date);
//        dd/mm/yy
        $arrayDate['day'] = $date[0];
        $arrayDate['month'] = $date[1];
        $arrayDate['year'] = $date[2];
        return $arrayDate['year'].'-'.$arrayDate['month'].'-'.$arrayDate['day'];
    }

//    Created: 15/04/2015 | Developer: reyro | Description: BEFORE SENDING date and time to the DB
    public function fnSetFormatDateTime($date) {
        $date = explode(' ', $date);

        $dateNormal = explode('/',$date[0]);
        $dateTime = explode(':',$date[1]);

        $arrayDate['day'] = $dateNormal[0];
        $arrayDate['month'] = $dateNormal[1];
        $arrayDate['year'] = $dateNormal[2];
        $arrayDate['hour'] = $dateTime[0];
        $arrayDate['min'] = $dateTime[1];
        $arrayDate['sec'] = $dateTime[2];
        return $arrayDate;
    }


//    Created: 15/04/2015 | Developer: reyro | Description: When it RECEIVES a date from DB
    public function fnGetFormatDate($date) {

        if ($date == '') {
            return '';
        }
//        $date = substr($date, 0 , 10);
        return date('d/m/Y', strtotime($date)); //In this case not needed 'cause I changed DB format time to default "d/m/y" (not like it was on windows) Now is correct.
        //Will leave this function in case is needed in certain occasions
        return $date;
    }

//    Created: 15/04/2015 | Developer: reyro | Description: When it RECEIVES date and time from DB
    public function fnGetFormatDateTime($date) {
        if ($date == '') {
            return '';
        }

//        return date('d/m/Y', strtotime($date)); //In this case not needed 'cause I changed DB format time to default "d/m/y" (not like it was on windows) Now is correct.
        //Will leave this function in case is needed in certains occasions
        return $date;
    }

//END CLASS
}

?>