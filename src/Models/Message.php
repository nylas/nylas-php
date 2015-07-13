<?php

namespace Nylas\Models;

use Nylas\NylasAPIObject;


class Message extends NylasAPIObject {

    public $collectionName = 'messages';

    public function __construct($api, $namespace) {
        parent::__construct($this, $api, $namespace);
        $this->attr = [];
        $this->apiRoot =  'n';
    }

}