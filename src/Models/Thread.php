<?php

namespace Nylas\Models;

use Nylas\Models\Message;
use Nylas\Models\Draft;

use Nylas\NylasAPIObject;
use Nylas\NylasModelCollection;


class Thread extends NylasAPIObject {

    public $collectionName = 'threads';

    public function __construct($api, $namespace) {
        parent::__construct();
    }

    public function messages() {
        $thread_id = $this->data['id'];
        $namespace = $this->data['namespace_id'];
        $msgObj = new Message($this, $namespace);
        return new NylasModelCollection($msgObj, $this->klass, $namespace, array("thread_id"=>$thread_id), 0, array());
    }

    public function drafts() {
        $thread_id = $this->data['id'];
        $namespace = $this->data['namespace_id'];
        $msgObj = new Draft($this, $namespace);
        return new NylasModelCollection($msgObj, $this->klass, $namespace, array("thread_id"=>$thread_id), 0, array());
    }


}