<?php

namespace Nylas\Models;

use Nylas\NylasAPIObject;


class Event extends NylasAPIObject {

    public $collectionName = 'events';

    public function __construct($api, $namespace) {
        parent::__construct();
    }

}