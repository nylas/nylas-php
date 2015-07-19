## Nylas PHP

PHP bindings for the Nylas REST API [https://www.nylas.com](https://www.nylas.com)

## Installation

You can install the library by running:

```php
cd nylas-php
composer install
```


## Usage

The Nylas REST API uses server-side (three-legged) OAuth, and this library provides convenience methods to simplify the OAuth process. Here's how it works:

1. You redirect the user to our login page, along with your App Id and Secret
1. Your user logs in
1. She is redirected to a callback URL of your own, along with an access code
1. You use this access code to get an authorization token to the API

For more information about authenticating with Nylas, visit the [Developer Documentation](https://www.nylas.com/docs/gettingstarted-hosted#authenticating).

In practice, the Nylas REST API client simplifies this down to two steps.

## Auth

**index.php**

```php
$client = new Nylas(CLIENT, SECRET);
$redirect_url = 'http://localhost:8080/login_callback.php';
$get_auth_url = $client->createAuthURL($redirect_url);

// redirect to Nylas auth server
header("Location: ".$get_auth_url);
```

**login_callback.php**

```php
$access_code = $_GET['code'];
$client = new Nylas(CLIENT, SECRET);
$get_token = $client->getAuthToken($access_code);

// save token in session
$_SESSION['access_token'] = $get_token;
```


## Fetching Namespaces

```php
$client = new Nylas(CLIENT, SECRET, TOKEN);
$namespace = $client->namespaces()->first();

echo $namespace->email_address;
echo $namespace->provider;
```



## Fetching Threads

```php
$client = new Nylas(CLIENT, SECRET, TOKEN);
$namespace = $client->namespaces()->first();

// Fetch the first thread
$first_thread = $namespace->threads()->first();
echo $first_thread->id;

// Fetch first 2 latest threads
$two_threads = $namespace->threads()->all(2);
foreach($two_threads as $thread) {
    echo $thread->id;
}

// List all threads with 'ben@nylas.com'
$search_criteria = array("any_email" => "ben@nylas.com");
$get_threads = $namespace->threads()->where($search_criteria)->items()
foreach($get_threads as $thread) {
    echo $thread->id;
}
```

## Working with Threads

```php
// List thread participants
foreach($thead->participants as $participant) {
    echo $participant->email;
    echo $participant->name;
}

// Mark as Read
$thread->markAsRead();

// Mark as Seen
$thread->markAsSeen();

// Archive
$thread->archive();

// Unarchive
$thread->unarchive();

// Trash
$thread->trash();

// Star
$thread->star();

// Unstar
$thread->unstar();

// Add or remove arbitrary tags
$to_add = array('cfa1233ef123acd12');
$to_remove = array('inbox');
$thread->addTags($to_add);
$thread->removeTags($to_remove);

// Listing messages
foreach($thread->messages()->items() as $message) {
    echo $message->subject;
    echo $message->body;
}
```

## Working with Files


```php
$client = new Nylas(CLIENT, SECRET, TOKEN);
$namespace = $client->namespaces()->first();

$file_path = '/var/my/folder/test_file.pdf';
$upload_resp = $namespace->files()->create($file_path);
echo $upload_resp->id;
```

## Working with Drafts

```php
$client = new Nylas(CLIENT, SECRET, TOKEN);
$namespace = $client->namespaces()->first();

$person_obj = new \Nylas\Models\Person('Kartik Talwar', 'kartik@nylas.com');
$message_obj = array( "to" => array($person_obj),
                      "subject" => "Hello, PHP!",
                      "body" => "Test <br> message");

$draft = $namespace->drafts()->create($message_obj);
$send_message = $draft->send();
echo $send_message->id;
```



## Open-Source Sync Engine

The [Nylas Sync Engine](http://github.com/nylas/sync-engine) is open-source, and you can also use the Python library with the open-source API. Since the open-source API provides no authentication or security, connecting to it is simple. When you instantiate the Nylas object, provide null for the App ID, App Secret, and API Token, and pass the fully-qualified address of your copy of the sync engine:

```php
$client = new Nylas(CLIENT, SECRET, TOKEN, 'http://localhost:5555/');
```

## Contributing

We'd love your help making Nylas better. Join the Google Group for project updates and feature discussion. We also hang out in `#nylas` on [irc.freenode.net](irc.freenode.net), or you can email [support@nylas.com](mailto:support@nylas.com).

Please sign the Contributor License Agreement before submitting pull requests. (It's similar to other projects, like NodeJS or Meteor.)