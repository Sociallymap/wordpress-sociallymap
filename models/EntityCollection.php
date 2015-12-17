<?php

class EntityCollection
{
    private $table_entity;
    private $table_options;
    private $configOptions;
    private $default_value;

    function __construct()
    {
        global $wpdb;
        $this->table_entity       = $wpdb->prefix.'sm_entities';
        $this->table_options      = $wpdb->prefix.'sm_entity_options';

        $config = new ConfigOption();
        $this->configOptions = $config->getConfig();
    }

    public function add($data)
    {
        global $wpdb;
        $entity = new Entity();
        $option = new Option();

        $dataEntity = [
            'activate'               => $data['activate'],
            'sm_entity_id'           => $data['sm_entity_id'],
            'author_id'              => wp_get_current_user()->ID,
            'name'                   => $data['name'],
            'last_published_message' => date('Y-m-d H:i:s'),
        ];

        $entityID = $entity->save($dataEntity);

        foreach ($data['category'] as $key => $value) {
            $dataOption = [
                'option_id' => 1,
                'value'     => $value,
            ];
            $option->save($dataOption, $entityID);
        }

        $dataOption = [
            'option_id' => 2,
            'value'     => $data['display_type']
        ];
        $option->save($dataOption, $entityID);

        $dataOption = [
            'option_id' => 3,
            'value'     => $data['publish_type']
        ];
        $option->save($dataOption, $entityID);

        $dataOption = [
            'option_id' => 4,
            'value'     => $data['link_canonical']
        ];
        $option->save($dataOption, $entityID);

        $dataOption = [
            'option_id' => 5,
            'value'     => $data['image']
        ];
        $option->save($dataOption, $entityID);

        $dataOption = [
            'option_id' => 6,
            'value'     => $data['readmore']
        ];
        $option->save($dataOption, $entityID);

        $dataOption = [
            'option_id' => 7,
            'value'     => $data['noindex']
        ];
        $option->save($dataOption, $entityID);

        $dataOption = [
            'option_id' => 8,
            'value'     => $data['nofolow']
        ];
        $option->save($dataOption, $entityID);
    }

    public function update($data)
    {
        global $wpdb;
        $entity = new Entity();
        $option = new Option();

        $dataEntity = [
            'id'           => $data['id'],
            'name'         => $data['name'],
            'activate'     => $data['activate'],
            'sm_entity_id' => $data['sm_entity_id'],
        ];

        $entity->update($dataEntity);

        $optionsEntity = [
            'idSource'       =>  $data['id'],
            'category'       =>  $data['category'],
            'publish_type'   =>  $data['publish_type'],
            'display_type'   =>  $data['display_type'],
            'link_canonical' =>  $data['link_canonical'],
            'image'          =>  $data['image'],
            'noindex'        =>  $data['noindex'],
            'nofolow'        =>  $data['nofolow'],
            'readmore'        =>  $data['readmore'],
        ];
        $option->update($optionsEntity);
    }

    public function all($orderKey = "", $orderSense = "")
    {
        global $wpdb;
        $entity = new Entity();
        $listRSS = [];

        $entitiesRequest = 'SELECT * FROM '.$this->table_entity;
        $entities = $wpdb->get_results($entitiesRequest);


        // LOAD ENTITIES
        foreach ($entities as $data) {
            $listRSS[] = $entity->getById($data->id, $orderKey, $orderSense);
        }

        return $listRSS;
    }

    public function deleteRowsByID($id)
    {
        global $wpdb;

        $entity = new Entity();
        $option = new Option();

        $entity->deleteById($id);
        $option->deleteByOwnerId($id);

        return ($entity && $option);
    }

    public function getByEntityId($entityId)
    {
        global $wpdb;

        $objectEntity = new Entity();
        $entity = $objectEntity->getByEntityId($entityId);

        return $entity;
    }
}
