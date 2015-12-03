<?php

namespace Nylas\Models;

use Nylas\NylasAPIObject;


class Message extends NylasAPIObject {

    public $collectionName = 'messages';

    public function __construct($api) {
        parent::__construct();
        $this->api = $api;
        $this->namespace = NULL;
    }

    public function raw() {
        $headers = array('Accept' => 'message/rfc822');
        $resource = $this->klass->getResourceData($this->namespace, $this, $this->data['id'], array('headers' => $headers));

        $data = '';
        while (!$resource->eof()) {
            $data .= $resource->read(1024);
        }

        return $data;
    }

}