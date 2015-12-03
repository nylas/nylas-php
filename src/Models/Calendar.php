<?php

namespace Nylas\Models;

use Nylas\Models\Event;
use Nylas\NylasAPIObject;
use Nylas\NylasModelCollection;


class Calendar extends NylasAPIObject {

    public $collectionName = 'calendars';

    public function __construct($api) {
        parent::__construct();
    }

    public function events() {
        $calendar_id = $this->data['id'];
        $msgObj = new Event($this);
        return new NylasModelCollection($msgObj, $this->klass, NULL, array("calendar_id"=>$calendar_id), 0, array());
    }


}