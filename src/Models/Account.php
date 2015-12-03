<?php

namespace Nylas\Models;

use Nylas\Models\Message;
use Nylas\Models\Thread;
use Nylas\Models\Tag;
use Nylas\Models\File;
use Nylas\Models\Contact;
use Nylas\Models\Calendar;
use Nylas\Models\Draft;
use Nylas\Models\Event;

use Nylas\NylasAPIObject;
use Nylas\NylasModelCollection;


class Account extends NylasAPIObject {

    public $collectionName = 'account';

    public function __construct() {
        parent::__construct();
    }

}