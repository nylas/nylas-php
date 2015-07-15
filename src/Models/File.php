<?php

namespace Nylas\Models;

use Nylas\NylasAPIObject;


class File extends NylasAPIObject {

    public $collectionName = 'files';

    public function __construct($api, $namespace) {
        parent::__construct();
        $this->api = $api;
        $this->namespace = $namespace;
    }

    public function create($file_name) {
        $payload = array("name" => "file",
                         "filename" => basename($file_name),
                         "contents" => fopen($file_name, 'r'));
        return $this->api->klass->_createResource($this->namespace, $this, $payload);
    }

}