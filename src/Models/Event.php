<?php

namespace Nylas\Models;

use Nylas\NylasAPIObject;


class Event extends NylasAPIObject {

    public $collectionName = 'events';
    public $attrs = array("id", "namespace_id", "title", "description",
                          "location", "read_only", "when", "busy",
                          "participants", "calendar_id", "recurrence",
                          "status", "master_event_id", "original_start_time");

    public function __construct($api) {
        parent::__construct();
        $this->api = $api;
        $this->namespace = NULL;
    }

    public function create($data, $api=NULL) {
        $sanitized = array();
        foreach($this->attrs as $attr) {
            if(array_key_exists($attr, $data)) {
                $sanitized[$attr] = $data[$attr];
            }
        }

        if(!$api) {
            $api = $this->api->klass;
        } else {
            $api = $api->api;
        }

        if(!array_key_exists('calendar_id', $sanitized)) {
            if($this->api->collectionName == 'calendars') {
                $sanitized['calendar_id'] = $this->api->id;
            } else {
                throw new Exception("Missing calendar_id", 1);
            }
        }

        $this->data = $sanitized;
        $this->api = $api;

        return $this->api->_createResource($this->namespace, $this, $this->data);
    }

    public function update($data) {
        $sanitized = array();
        foreach($this->attrs as $attr) {
            if(array_key_exists($attr, $data)) {
                $sanitized[$attr] = $data[$attr];
            }
        }

        return $this->api->_updateResource($this->namespace, $this, $this->id, $sanitized);
    }


    public function delete() {
        return $this->klass->_deleteResource($this->namespace, $this, $this->id);
    }

}