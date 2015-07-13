<?php

namespace Nylas\Models;

use Nylas\NylasAPIObject;


class Message extends NylasAPIObject {

    public $collectionName = 'messages';

    public function __construct($api, $namespace) {
    }

}