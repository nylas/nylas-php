<?php

namespace Nylas\Models;

use Nylas\NylasAPIObject;


class Tag extends NylasAPIObject {

    public $collectionName = 'tags';

    public function __construct($api, $namespace) {
        parent::__construct();
    }

}