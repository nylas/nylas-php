<?php

namespace Nylas\Models;

use Nylas\NylasAPIObject;


class Message extends NylasAPIObject {

    public $collectionName = 'messages';

    public function __construct($api, $namespace) {
        parent::__construct();
        $this->api = $api;
        $this->namespace = $namespace;
    }

    public function raw() {
        $resource = $this->klass->getResourceRaw($this->namespace, $this, $this->data['id'], array('extra' => 'rfc2822'));
        if(array_key_exists('rfc2822', $resource)) {
            return base64_decode($resource['rfc2822']);
        }
        return NULL;
    }

}