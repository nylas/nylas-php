<?php

namespace Nylas;

class NylasAPIObject {

    public $apiRoot;

    public function __construct() {
    }

    public function json() {
        return $this->data;
    }

    public function _createObject($klass, $namespace, $objects) {
        $this->data = $objects;
        $this->klass = $klass;
        return $this;
    }

    public function __get($key) {
        if(array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }
        return NULL;
    }

}