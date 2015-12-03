<?php

namespace Nylas\Models;

use Nylas\NylasAPIObject;


class File extends NylasAPIObject {

    public $collectionName = 'files';

    public function __construct($api) {
        parent::__construct();
        $this->api = $api;
        $this->namespace = NULL;
    }

    public function create($file_name) {
        $payload = array("name" => "file",
                         "filename" => basename($file_name),
                         "contents" => fopen($file_name, 'r'));
        $upload = $this->api->_createResource($this->namespace, $this, $payload);
        $data = $upload->data[0];
        $this->data = $data;
        return $this;
    }

    public function download() {
        $resource = $this->klass->getResourceData($this->namespace, $this, $this->data['id'], array('extra' => 'download'));

        $data = '';
        while (!$resource->eof()) {
            $data .= $resource->read(1024);
        }

        return $data;
    }

}