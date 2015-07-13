<?php

namespace Nylas\Models;

use Nylas\NylasAPIObject;


class Draft extends NylasAPIObject {

    public $collectionName = 'drafts';

    public function __construct($api, $namespace) {
        parent::__construct();
    }

}