<?php

namespace Nylas\Models;

use Nylas\NylasAPIObject;
use Nylas\Models\Person;
use Nylas\Models\Send;


class Draft extends NylasAPIObject {

    public $collectionName = 'drafts';
    public $attrs = array('subject', 'to', 'cc', 'bcc',
                          'from', 'reply_to', 'thread_id',
                          'body', 'file_ids');

    public function __construct($api, $namespace) {
        parent::__construct();
    }

    public function create($data, $api) {
        $sanitized = array();
        foreach($this->attrs as $attr) {
            if(array_key_exists($attr, $data)) {
                $sanitized[$attr] = $data[$attr];
            }
        }

        $this->data = $sanitized;
        $this->api = $api->api;
        $this->namespace = $api->namespace;
        return $this;
    }

    public function update($data) {
        $allowed = array();
        foreach($this->attrs as $attr) {
            if(array_key_exists($attr, $data)) {
                $sanitized[$attr] = $data[$attr];
            }
        }

        $this->data = array_merge($this->data, $sanitized);
        return $this;
    }

    public function send($data=NULL) {
        $data = ($data) ? $data : $this->data;
        if(array_key_exists('id', $data)) {
            $resource = $this->api->_updateResource($this->namespace, $this, $id, $data);
        } else {
            $resource = $this->api->_createResource($this->namespace, $this, $data);
        }

        $send_object = new Send($this->api, $this->namespace);
        return $send_object->send($resource->data);
    }
}