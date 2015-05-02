<?php
namespace NovakSolutions\Infusionsoft;

class ObjectCache extends SmartCache{
    var $object;
    var $conditions;
    var $limit;
    var $page;
    var $returnFields;
    var $app_name;

    public function __construct(Base $object, array $conditions = array(), $ttl = 300, $limit = 1000, $page = 0, $returnFields = false, App $app = null){
        $this->object = $object;
        $this->conditions = $conditions;
        $this->limit = $limit;
        $this->page = $page;
        $this->returnFields = $returnFields;
        $this->app = $app;
        $this->app_name = $app == null ? AppPool::getApp()->getHostname() : $app->getHostname();
        parent::__construct('objects_' . $this->object->getTable() . '_' . $this->app_name . '_' . md5(
            http_build_query($conditions) .
            $this->limit .
            $this->page .
            ($returnFields ? http_build_query($returnFields) : '') .
            $this->app_name
        ) , 600,dirname(__FILE__) . '/cache/');
    }

    public function getDataFromSource(){
        return DataService::query($this->object, $this->conditions, $this->limit, $this->page, $this->returnFields, $this->app);
    }

    public function getById($id){
        $data = $this->getData();
        foreach($data as $object){
            if($object->Id == $id){
                return $object;
            }
        }

        return false;
    }

    public function addObjectToCache(Base $object) {
        // Get the cache data and add our new object
        $data = $this->getData();
        $data[] = $object;
        // Attempt to delete the cache
        try {
            $this->expireCache();
        } catch (Exception $e) {
            CakeLog::write('warning', "Problem expiring object cache during addObjectToCache, Error: ".$e->getMessage());
        }
        // Recache our new data
        $this->cacheData($data);
    }
}