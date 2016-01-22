<?php

//This service will populate returned objects with a compound key for the Id.  This facilitates use with iDos (A Proprietery NovakSolutions tool).

class Infusionsoft_ContactGroupAssignDataService extends Infusionsoft_Service {
    static $lastProcessedContactId = 0;
    public static function queryWithOrderBy($object, $queryData, $orderByField = null, $ascending = true, $limit = 1000, $page = 0, $returnFields = false, Infusionsoft_App $app = null){

        $results = array();
        Infusionsoft_ContactGroupAssign::removeField('Id');
        $results = Infusionsoft_DataService::queryWithOrderBy($object, $queryData, $orderByField, $ascending, $limit, $page, $returnFields, $app);
        Infusionsoft_ContactGroupAssign::addCustomField('Id');
        self::addCompoundKeyToResults($results);

        if($orderByField == 'ContactId' && $ascending){
            if($page > 0){
                self::removeRecordsForContactsAlreadyProcseed(self::$lastProcessedContactId, $results);
            }
            $lastRecordOfResultSet = $results[count($results)-1];
            $lastContactId = $lastRecordOfResultSet->ContactId;
            $foundLastRecordForLastContact = false;
            $extraPages = 1;
            while(!$foundLastRecordForLastContact){
                Infusionsoft_ContactGroupAssign::removeField('Id');
                $extraResults = Infusionsoft_DataService::queryWithOrderBy($object, $queryData, $orderByField, $ascending, $limit, $page + $extraPages, $returnFields, $app);
                Infusionsoft_ContactGroupAssign::addCustomField('Id');
                self::addCompoundKeyToResults($extraResults);
                foreach($extraResults as $extraResult){
                    //If somehow, between calls, a lot of tags are applied, and our "last" contact gets pushed down to the second page, we need to not break because of new contacts higher up on the page.
                    if($extraResult->ContactId <= $lastContactId){
                        $results[] = $extraResult;
                    } else {
                        $foundLastRecordForLastContact = true;
                        break;
                    }
                }
                $extraPages++;
            }
            return $results;
        }
        /** @var Infusionsoft_ContactGroupAssign $result */


        return $results;
    }

    public static function addCompoundKeyToResults(array &$results){
        foreach($results as &$result){
            $result->Id = $result->ContactId * 10000000 + $result->GroupId;
        }
    }

    /**
     * @param $results
     */
    public static function removeRecordsForContactsAlreadyProcseed($lastProcessedContactId, &$results){
        /** @var Infusionsoft_ContactGroupAssign $result */
        foreach ($results as $key => $result) {
            if ($result->ContactId < $lastProcessedContactId) {
                unset($results[$key]);
            } else {
                return;
            }
        }
    }
}