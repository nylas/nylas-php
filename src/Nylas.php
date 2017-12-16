<?php

namespace Nylas;


use Nylas\Models;
use GuzzleHttp\Client as GuzzleClient;


class Nylas {

    protected $apiServer = 'https://api.nylas.com';
    protected $apiClient;
    protected $apiToken;
    public $apiRoot = '';

    public function __construct($appID, $appSecret, $token=NULL, $apiServer=NULL) {
        $this->appID     = $appID;
        $this->appSecret = $appSecret;
        $this->apiToken  = $token;
        $this->apiClient = $this->createApiClient();

        if($apiServer) {
            $this->apiServer = $apiServer;
        }
    }

    protected function createHeaders() {
        $token = 'Basic '.base64_encode($this->apiToken.':');
        $headers = array('headers' => ['Authorization' => $token,
                                       'X-Nylas-API-Wrapper' => 'php']);
        return $headers;
    }

    private function createApiClient() {
        return new GuzzleClient(['base_url' => $this->apiServer]);
    }

    public function createAuthURL($redirect_uri, $login_hint=NULL) {
        $args = array("client_id" => $this->appID,
                      "redirect_uri" => $redirect_uri,
                      "response_type" => "code",
                      "scope" => "email",
                      "login_hint" => $login_hint,
                      "state" => $this->generateId());

        return $this->apiServer.'/oauth/authorize?'.http_build_query($args);
    }

    public function getAuthToken($code) {
        $args = array("client_id" => $this->appID,
                      "client_secret" => $this->appSecret,
                      "grant_type" => "authorization_code",
                      "code" => $code);

        $url = $this->apiServer.'/oauth/token';
        $payload = array();
        $payload['headers']['Content-Type'] = 'application/x-www-form-urlencoded';
        $payload['headers']['Accept'] = 'text/plain';
        $payload['body'] = $args;

        $response = json_decode($this->apiClient->post($url, $payload)->getBody(),true);

        if(array_key_exists('access_token', $response)) {
            $this->apiToken = $response['access_token'];
        }

        return $this->apiToken;
    }

    public function account() {
        $apiObj = new NylasAPIObject();
        $nsObj = new Models\Account();
        $accountData = $this->getResource('', $nsObj, '', array());
        $account = $apiObj->_createObject($accountData->klass, NULL, $accountData->data);
        return $account;
    }

    public function threads() {
        $msgObj = new Models\Thread($this);
        return new NylasModelCollection($msgObj, $this, NULL, array(), 0, array());
    }

    public function messages() {
        $msgObj = new Models\Message($this);
        return new NylasModelCollection($msgObj, $this, NULL, array(), 0, array());
    }

    public function drafts() {
        $msgObj = new Models\Draft($this);
        return new NylasModelCollection($msgObj, $this, NULL, array(), 0, array());
    }

    public function labels() {
        $msgObj = new Models\Label($this);
        return new NylasModelCollection($msgObj, $this, NULL, array(), 0, array());
    }

    public function files() {
        $msgObj = new Models\File($this);
        return new NylasModelCollection($msgObj, $this, NULL, array(), 0, array());
    }

    public function contacts() {
        $msgObj = new Models\Contact($this);
        return new NylasModelCollection($msgObj, $this, NULL, array(), 0, array());
    }

    public function calendars() {
        $msgObj = new Models\Calendar($this);
        return new NylasModelCollection($msgObj, $this, NULL, array(), 0, array());
    }

    public function events() {
        $msgObj = new Models\Event($this);
        return new NylasModelCollection($msgObj, $this, NULL, array(), 0, array());
    }

    public function getResources($namespace, $klass, $filter) {
        $suffix = ($namespace) ? '/'.$klass->apiRoot.'/'.$namespace : '';
        $url = $this->apiServer.$suffix.'/'.$klass->collectionName;
        $url = $url.'?'.http_build_query($filter);
        $data = json_decode($this->apiClient->get($url, $this->createHeaders())->getBody());

        $mapped = array();
        foreach ($data as $i) {
            $mapped[] = clone $klass->_createObject($this, $namespace, $i);
        }
        return $mapped;
    }

    public function getResource($namespace, $klass, $id, $filters) {
        $extra = '';
        if(array_key_exists('extra', $filters)) {
            $extra = $filters['extra'];
            unset($filters['extra']);
        }
        $response = $this->getResourceRaw($namespace, $klass, $id, $filters);
        return $klass->_createObject($this, $namespace, $response);
    }

    public function getResourceRaw($namespace, $klass, $id, $filters) {
        $extra = '';
        if(array_key_exists('extra', $filters)) {
            $extra = $filters['extra'];
            unset($filters['extra']);
        }
        $prefix = ($namespace) ? '/'.$klass->apiRoot.'/'.$namespace : '';
        $postfix = ($extra) ? '/'.$extra : '';
        $url = $this->apiServer.$prefix.'/'.$klass->collectionName.'/'.$id.$postfix;
        $url = $url.'?'.http_build_query($filters);
        $data = json_decode($this->apiClient->get($url, $this->createHeaders())->getBody(),true);
        return $data;
    }

    public function getResourceData($namespace, $klass, $id, $filters) {
        $extra = '';
        $customHeaders = array();
        if(array_key_exists('extra', $filters)) {
            $extra = $filters['extra'];
            unset($filters['extra']);
        }
        if(array_key_exists('headers', $filters)) {
            $customHeaders = $filters['headers'];
            unset($filters['headers']);
        }
        $prefix = ($namespace) ? '/'.$klass->apiRoot.'/'.$namespace : '';
        $postfix = ($extra) ? '/'.$extra : '';
        $url = $this->apiServer.$prefix.'/'.$klass->collectionName.'/'.$id.$postfix;
        $url = $url.'?'.http_build_query($filters);
        $customHeaders = array_merge($this->createHeaders()['headers'], $customHeaders);
        $headers = array('headers' => $customHeaders);
        $data = $this->apiClient->get($url, $headers)->getBody();
        return $data;
    }

    public function _createResource($namespace, $klass, $data) {
        $prefix = ($namespace) ? '/'.$klass->apiRoot.'/'.$namespace : '';
        $url = $this->apiServer.$prefix.'/'.$klass->collectionName;

        $payload = $this->createHeaders();
        if($klass->collectionName == 'files') {
            $payload['headers']['Content-Type'] = 'multipart/form-data';
            $payload['body'] = $data;
        } else {
            $payload['headers']['Content-Type'] = 'application/json';
            $payload['json'] = $data;
        }

        $response = json_decode($this->apiClient->post($url, $payload)->getBody(),true);
        return $klass->_createObject($this, $namespace, $response);
    }

    public function _updateResource($namespace, $klass, $id, $data) {
        $prefix = ($namespace) ? '/'.$klass->apiRoot.'/'.$namespace : '';
        $url = $this->apiServer.$prefix.'/'.$klass->collectionName.'/'.$id;

        if($klass->collectionName == 'files') {
            $payload['headers']['Content-Type'] = 'multipart/form-data';
            $payload['body'] = $data;
        } else {
            $payload = $this->createHeaders();
            $payload['json'] = $data;
            $response = json_decode($this->apiClient->put($url, $payload)->getBody(),true);
            return $klass->_createObject($this, $namespace, $response);
        }
    }

    public function _deleteResource($namespace, $klass, $id) {
        $prefix = ($namespace) ? '/'.$klass->apiRoot.'/'.$namespace : '';
        $url = $this->apiServer.$prefix.'/'.$klass->collectionName.'/'.$id;

        $payload = $this->createHeaders();
        $response = json_decode($this->apiClient->delete($url, $payload)->getBody(),true);
        return $response;
    }

    private function generateId() {
        // Generates unique UUID
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
}

}


class NylasModelCollection {

    private $chunkSize = 50;

    public function __construct($klass, $api, $namespace=NULL, $filter=array(), $offset=0, $filters=array()) {
        $this->klass = $klass;
        $this->api = $api;
        $this->namespace = $namespace;
        $this->filter = $filter;
        $this->filters = $filters;

        if(!array_key_exists('offset', $filter)) {
            $this->filter['offset'] = 0;
        }
    }

    public function items() {
        $offset = 0;
        while (True) {
            $items = $this->_getModelCollection($offset, $this->chunkSize);
            if(!$items) {
                break;
            }
            foreach ($items as $item) {
                yield $item;
            }
            if (count($items) < $this->chunkSize) {
                break;
            }
            $offset += count($items);
        }
    }

    public function first() {
        $results = $this->_getModelCollection(0, 1);
        if ($results) {
            return $results[0];
        }
        return NULL;
    }

    public function all($limit=INF) {
        return $this->_range($this->filter['offset'], $limit);
    }

    public function where($filter, $filters=array()) {
        $this->filter = array_merge($this->filter, $filter);
        $this->filter['offset'] = 0;
        $collection = clone $this;
        $collection->filter = $this->filter;
        return $collection;
    }

    public function find($id) {
        return $this->_getModel($id);
    }

    public function create($data) {
        return $this->klass->create($data, $this);
    }

    private function _range($offset, $limit) {
        $result = array();
        while (count($result) < $limit) {
            $to_fetch = min($limit - count($result), $this->chunkSize);
            $data = $this->_getModelCollection($offset+count($result), $to_fetch);
            $result = array_merge($result, $data);

            if(!$data || count($data) < $to_fetch) {
                break;
            }
        }
        return $result;
    }

    private function _getModel($id) {
        // make filter a kwarg filters
        return $this->api->getResource($this->namespace, $this->klass, $id, $this->filter);
    }

    private function _getModelCollection($offset, $limit) {
        $this->filter['offset'] = $offset;
        $this->filter['limit'] = $limit;
        return $this->api->getResources($this->namespace, $this->klass, $this->filter);
    }

}


class NylasAPIObject {

    public $apiRoot;

    public function __construct() {
    }

    public function json() {
        return $this->data;
    }

    public function _createObject($klass, $namespace, $objects) {
        $this->data = $objects;
        $this->klass = $klass;
        return $this;
    }

    public function __get($key) {
        if(array_key_exists($key, $this->data)) {
            return $this->data->$key;
        }
        return NULL;
    }

}

?>
