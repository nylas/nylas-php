<?php

namespace Nylas\Models;

use Nylas\Models\Event;
use Nylas\NylasAPIObject;
use Nylas\NylasModelCollection;


class Calendar extends NylasAPIObject {

    public $collectionName = 'calendars';

    public function __construct($api, $namespace) {
        parent::__construct();
    }

    public function events() {
        $calendar_id = $this->data['id'];
        $namespace = $this->data['namespace_id'];
        $msgObj = new Event($this, $namespace);
        return new NylasModelCollection($msgObj, $this->klass, $namespace, array("calendar_id"=>$calendar_id), 0, array());
    }


}