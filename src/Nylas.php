<?php

namespace Nylas;

require('../vendor/autoload.php'); // this shouldnt be required
require('./config.php');


use Nylas\Models;
use GuzzleHttp\Client as GuzzleClient;


error_reporting(E_ALL);


class Nylas {

    protected $apiServer = 'https://api.nylas.com';
    protected $authServer = 'https://www.nylas.com';
    protected $apiClient;
    protected $apiToken;
    protected $apiRoot = 'n';

    public function __construct($appID=NULL, $appSecret=NULL, $token=NULL) {
        $this->appID     = $appID;
        $this->appSecret = $appSecret;
        $this->apiToken  = $token;
        $this->apiClient = $this->createApiClient();
    }

    protected function createHeaders() {
        $token = 'Basic '.base64_encode($this->apiToken.':');
        return ['headers' => ['Authorization' => $token,
                              'X-Nylas-API-Wrapper' => 'php']];
    }

    private function createApiClient() {
        return new GuzzleClient(['base_url' => $this->apiServer]);
    }

    public function namespaces() {
        // needs to be a collection
    }

    public function messages($account) {
        $endpoint = '/n/'.$account.'/messages?limit=1';
        $data = $this->createApiClient()->get($endpoint, $this->createHeaders());
        $msgObj = new Models\Message($this, NS);
        // dont use constant here
        return new NylasModelCollection($msgObj, $this, NS, array());
    }

    // filter should be filters
    public function getResources($namespace, $klass, $filter) {
        $suffix = ($namespace) ? '/'.$klass->apiRoot.'/'.$namespace : '';
        $url = $this->apiServer.$suffix.'/'.$klass->collectionName;
        $url = $url.'?'.http_build_query($filter);
        $data = $this->apiClient->get($url, $this->createHeaders())->json();
        return $data; // map this to models
    }

    public function getResource($namespace, $klass, $id, $filters) {
        $extra = '';
        if(array_key_exists('extra', $filters)) {
            $extra = $filters['extra'];
            unset($filters['extra']);
        }
        $response = $this->getResourceRaw($namespace, $klass, $id, $filters);
        return $klass->create($this, $namespace, $response);
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
        $data = $this->apiClient->get($url, $this->createHeaders())->json();
        return $data; // map this to models
    }

    // public function create($klass, $api, $namespace, $objects) {
    //     // return $this->klass->create($this->api, $this->namespace, $params);
    //     $obj = $klass($api, $namespace);
    //     $obj['klass'] = $klass;
    //     foreach($klass->attrs as $attr) {
    //         if(array_key_exists($attr, $objects)) {
    //             $obj[$attr] = $objects[$attr];
    //         }
    //     }
    //     if(!array_key_exists('id', $objects)) {
    //         $obj['id'] = NULL;
    //     }
    //     return $obj;
    // }
}


class NylasModelCollection {

    private $chunkSize = 50;

    public function __construct($klass, $api, $namespace=NULL, $filter=NULL) {
        $this->klass = $klass;
        $this->api = $api;
        $this->namespace = $namespace;
        $this->filter = $filter;

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

    public function where($filter, $filters) {
        $this->filter = array_merge($this->filter, $filter);
        $this->filter['offset'] = 0;
        $collection = clone $this;
        $collection->filter = $this->filter;
        return $collection;
    }

    public function find($id) {
        return $this->_getModel($id);
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

    private $apiRoot;

    public function __construct($klass, $api, $namespace) {
        $this->id = NULL;
        $this->klass = $klass;
        $this->namespace = $namespace;
        $this->apiRoot = 'n';
    }

    public function create($klass, $namespace, $objects) {
        // return $this->klass->create($this->api, $this->namespace, $params);
        print_r(array($klass, $namespace, $objects));return;
        $obj = $klass($api, $namespace);
        $obj['klass'] = $klass;
        foreach($klass->attrs as $attr) {
            if(array_key_exists($attr, $objects)) {
                $obj[$attr] = $objects[$attr];
            }
        }
        if(!array_key_exists('id', $objects)) {
            $obj['id'] = NULL;
        }
        return $obj;
    }
}


$client = new Nylas(CLIENT, SECRET, TOKEN);
// print_r($client->messages(NS)->all(2));
// print_r($client->messages(NS)->where(array("from"=>"makrand@wearhacks.com"), array())->all(1));
print_r($client->messages(NS)->find('5s51vn0rgyxmqy3a1h7vh90bj'));
