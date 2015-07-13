<?php

namespace Nylas\Models;

use Nylas\NylasAPIObject;
use Nylas\Models\Message;
use Nylas\NylasModelCollection;


class Namespaces extends NylasAPIObject {

    public $collectionName = 'n';

    public function __construct($api, $namespace) {
    }

    public function messages() {
        $this->namespace = $this->data['namespace_id'];
        $namespace = $this->namespace;
        $msgObj = new Message($this, $namespace);
        return new NylasModelCollection($msgObj, $this->klass, $namespace, array(), 0, array());
    }

}