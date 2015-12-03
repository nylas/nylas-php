<?php

namespace Nylas\Models;

use Nylas\NylasAPIObject;


class Label extends NylasAPIObject {

    public $collectionName = 'labels';

    public function __construct($api) {
        parent::__construct();
    }

}