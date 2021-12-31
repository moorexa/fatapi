# POST Requests Introduction
Welcome my friend, This documentation is solely for POST requests and how you can take complete advantage of it.

## How to send a request
It starts with building a request header with the required meta data, followed by the endpoint. Here, I would list what a typical request header should carry.

| Key | Value | Optional | Description |
|-----------|--------| ------ | ---- |
| x-meta-service | {eg. user} | no | Requests a service |
| x-meta-method | {eg. account} | yes | Requests a service method |
| x-meta-doc | {true or false} | yes | Requests documentation for that service |
| x-meta-id | {eg. id} | yes | Add extra id to the request

Next, we send a POST request to the API endpoint. See example below

```text
    HEADER 
    {
        x-meta-service : user,
        x-meta-method : account
        x-meta-id : 1
    }

    [POST] https://domain.com/api
``` 
Also note that some requests would require an authorize token which is mandatory to be added in the request header. And as we continue to develop this system, we would update this document and then show below a list of services that you can work with using the POST request method.

## Add a version
We can add a version number to the request as an argument or allow the system decide on what version to load. The system has a pre-config list of version numbers for some selected services. But by default, all defaults to version one. See the example below

```text
    [POST] https://domain.com/api/v2
```

## Make POST request using Socket.io
At any point in time you desire to utilize socket programming to facilitate communication instead of HTTP requests, we just might have a simple solution for you. Before you continue, ensure that you have 
```php 
composer require workerman/phpsocket.io
``` 
installed or just run 
```php 
php assist install socket
``` 
from your cmd or terminal to install all dependencies for socket.io.

Next, we start our socket server by running the following command
```php
php assist socket
```
This would start workerman socket server with the address **ws://0.0.0.0:8082**. And you can change this default settings here **src/environment.yaml**

## Sending a socket.io request with Javascript
To do this, you must have obtained socket.io cdn or installed socket.io client. Here, we would demostrate a complete proceedure to get you up to speed.

```html
<html lang="en">
<body>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.4.0/socket.io.min.js"></script>
    <script>
        let socket = io.connect('ws://0.0.0.0:8082');
        socket.on('connect', function () {

            console.log('connected');

            // making a get request
            socket.emit('meta.api', JSON.stringify({
                meta : {
                    service : 'user',
                    method : 'login',
                },
                header : {
                    'x-authorize-token' : 'ssjssue922484'
                },
                version: 'v1',
                signature: '67shdjddd-login',
                method: 'post',
                body: {
                    username: 'admin',
                    password: 'admin'
                }   
            }));

            // we can now listen for a response from the socket server 
            // using the unqiue signature.
            socket.on('67shdjddd-login', (data)=>{
                console.log(data);
            });

            socket.on('disconnect', function () {
                console.log('disconnected');
            });
        });
    </script>
</body>
</html>
```

## Sending a socket.io request with PHP
To do this, you don't need to install any dependency. Here, we would demostrate a complete proceedure to get you up to speed.

```php
use Lightroom\Socket\SocketClient;

// create connectionn
$socket = new SocketClient('0.0.0.0', '8082');

// you can queue more than one request
$socket->queue('meta.api', json_encode([
    'meta'     => [
        'service'   => 'user',
        'method'    => 'login'
    ],
    'header'    => [
        'x-authorize-token' => 'ssjssue922484'
    ],
    'method'    => 'post',
    'version'   => 'v1',
    'signature' => '8337sijdfu-login',
    'body'      => [
        'username' => 'admin',
        'password' => 'admin'
    ]
]));

// send all queues now
$socket->send();
```
At the moment, it makes sense to use the PHP implementation if you don't need to wait and listen for a response as demostrated for javascript. It comes handy when you need to send data to other services within the program.

Here is a complete breakdown on the sample data sent to **meta.api**

| Key | Value | Required | Description |
|-----|-------|-------------|-----------|
| meta | Object | yes | Request meta data for routing |
| header | Object | no | Request headers |
| version | String | no | Service version number |
| signature | String | yes | Digital identity for every request. Every response would be sent to that signature id. You should always change this for new requests. |
| method | String | yes | Request method. eg (post,get etc.) |
| body | Object | yes | POST request body |

## Complete POST Route List

| Service | Method[s] | Description |
|--------|------------|------------|
| | | |