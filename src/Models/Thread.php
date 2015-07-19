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
        $this->api = $api;
        $this->namespace = $namespace;
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

    public function createReply() {
        return $this->drafts()->create(array("subject" => $this->data['subject'],
                                             "thread_id" => $this->data['id']));
    }

    public function addTags($tags) {
        return $this->_updateTags($tags);
    }

    public function removeTags($tags) {
        return $this->_updateTags(array(), $tags);
    }

    public function markAsRead() {
        return $this->_updateTags(array(), array('unread'));
    }

    public function markAsSeen() {
        return $this->_updateTags(array(), array('unseen'));
    }

    public function archive() {
        return $this->_updateTags(array('archive'), array('inbox'));
    }

    public function unarchive() {
        return $this->_updateTags(array('inbox'), array('archive'));
    }

    public function trash() {
        return $this->_updateTags(array('trash'), array());
    }

    public function star() {
        return $this->_updateTags(array('starred'), array());
    }

    public function unstar() {
        return $this->_updateTags(array(), array('starred'));
    }

    private function _updateTags($add=array(), $remove=array()) {
        $payload = array("add_tags" => $add,
                         "remove_tags" => $remove);
        return $this->api->klass->_updateResource($this->namespace, $this, $this->data['id'], $payload);
    }

}