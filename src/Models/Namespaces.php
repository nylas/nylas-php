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


class Namespaces extends NylasAPIObject {

    public $collectionName = 'n';

    public function __construct($api, $namespace) {
        parent::__construct();
    }

    public function messages() {
        $this->namespace = $this->data['namespace_id'];
        $namespace = $this->namespace;
        $msgObj = new Message($this, $namespace);
        return new NylasModelCollection($msgObj, $this->klass, $namespace, array(), 0, array());
    }

    public function threads() {
        $this->namespace = $this->data['namespace_id'];
        $namespace = $this->namespace;
        $msgObj = new Thread($this, $namespace);
        return new NylasModelCollection($msgObj, $this->klass, $namespace, array(), 0, array());
    }

    public function drafts() {
        $this->namespace = $this->data['namespace_id'];
        $namespace = $this->namespace;
        $msgObj = new Draft($this, $namespace);
        return new NylasModelCollection($msgObj, $this->klass, $namespace, array(), 0, array());
    }

    public function tags() {
        $this->namespace = $this->data['namespace_id'];
        $namespace = $this->namespace;
        $msgObj = new Tag($this, $namespace);
        return new NylasModelCollection($msgObj, $this->klass, $namespace, array(), 0, array());
    }

    public function files() {
        $this->namespace = $this->data['namespace_id'];
        $namespace = $this->namespace;
        $msgObj = new File($this, $namespace);
        return new NylasModelCollection($msgObj, $this->klass, $namespace, array(), 0, array());
    }

    public function contacts() {
        $this->namespace = $this->data['namespace_id'];
        $namespace = $this->namespace;
        $msgObj = new Contact($this, $namespace);
        return new NylasModelCollection($msgObj, $this->klass, $namespace, array(), 0, array());
    }

    public function calendars() {
        $this->namespace = $this->data['namespace_id'];
        $namespace = $this->namespace;
        $msgObj = new Calendar($this, $namespace);
        return new NylasModelCollection($msgObj, $this->klass, $namespace, array(), 0, array());
    }

    public function events() {
        $this->namespace = $this->data['namespace_id'];
        $namespace = $this->namespace;
        $msgObj = new Event($this, $namespace);
        return new NylasModelCollection($msgObj, $this->klass, $namespace, array(), 0, array());
    }

}