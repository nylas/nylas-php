<?php

namespace Nylas\Models;

use Nylas\NylasAPIObject;


class File extends NylasAPIObject {

    public $collectionName = 'files';

    public function __construct($api, $namespace) {
        parent::__construct();
    }

}