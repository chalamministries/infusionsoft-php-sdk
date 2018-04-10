<?php
class Infusionsoft_AffiliateProgramService extends Infusionsoft_Service{
     public static function getAffiliatePrograms(Infusionsoft_App $app = null){
        $params = array();

        return parent::send($app, "AffiliateProgramService.getAffiliatePrograms", $params, null, true);
    }
    
}