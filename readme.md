# FATAPI
A Powerful REST API Framework for small and large projects.

| Credits | Link | Bio |
|----------|------|-----|
| Moorexa | [www.moorexa.com] | An open source PHP Framework |
| Amadi Ifeanyi | [www.amadiify.com] | Father and author of Moorexa and more |


Crafted for PHP developers. It's great for personal, enterprise, and commercial backend applications.

# Why FatApi
Building backend applications for years led us to this one stop solution. Over the years we've built REST API's with the following request methods **GET, POST, DELETE, PUT** and we've also had to worry about;

1. Versioning
2. Building light then scalling to micro services
3. Managing multiple endpoints that are procedural
4. Creating standards for our requests and responses even when contracting some part of our backend application to remote developers
5. Generating documentation for every services without writing one line of code.
6. Getting extra stuffs done like obtaining packages for authentication, middlewares, live documentation, plugins and much more from a marketplace so we don't have to reinvent the wheel. 

Yeah, creating this takes so much time, and many startups, developer, or software agency would worry less if they had a foundational system like this that allows:

1. Connectivity to external services
2. Creating small programs that can be decoupled into micro services when the business scales
3. Building strict entities that are consitent across versions
4. Generating responses that follow standards, easy to comprehend and consistent with formats
5. Clean URL that explains what it does, allowing developers to obtain documentation with an additional request header **x-meta-doc**
4. Reduced cost in managing multiple endpoints and do more without having to add request ID's to your endpoints, keeping it neat and enjoyable.
5. Adopting new request standards that takes out **DELETE** and **PUT** request methods but, out of the box lets you can do lot more with only **GET** and **POST** request methods which has made FATAPI so unqiue and easy to work with.
6. Also allow you use all request methods for external services. Yeah, FATAPI also serves as a gateway, connecting you to other services you may have on multiple servers, and allow you version them with no sweat.
5. Getting up to speed by obtaining services, middlewares, plugins, documentation templates and much more from the **FatApi Marketplace**.

The list can go on and on. This is a low code movement and we what to help you increase productivity by getting your backend rolled out in no time, properly documented and enjoyable.

## Architectural Style or Pattern
1. Event-Bus pattern
2. Micro-Services
3. Monolithic or Peer pattern

# Requirements
1. PHP 7 and above
2. Knowledgeable in PHP and OOP
3. Added knowledge of Moorexa ORM is a plus but not neccessary, everything is auto generated for you.

# Installation
It's way easy!!! Ensure that you have composer installed and run the create-project command below
```php
composer create-project moorexa/fatapi project-name
```
The command above will create a “project-name” folder.

If you omit the “project-name” argument, the command will create an fatapi folder, which can be renamed as appropriate.

Running this command will install all you need, including local composer and all the required dependencies.

# Getting Started
First, we must authorize every requests. And to do that, we just need to generate a token from the command line using
```php
php fatapi make:token {unqiue name}
```
Where **{unqiue name}** can be the name of your project or something unique to you. Next, we update the **MustBeAuthorized** middleware located in **src/Middlewares/**. You can also check out the **FatApi marketplace** for authorization middlewares that allow you do much more like rate limter, timeout etc. 

By default, we've added **MustBeAuthorized** middleware so you can still get stuffs done without needing to go premium at this point. 

After generating this token, you should get a token size of **40**, copy it and update the **MustBeAuthorized** middleware as seen below.

```php
/**
 * @package MustBeAuthorized
 * @author Amadi Ifeanyi <amadiify.com>
 */
class MustBeAuthorized implements MiddlewareInterface
{
    /**
     * @var string $authorizationToken
     * 
     * You should generate a new token from the CLI and update authorizationToken with it
     * By default, the system would auto generate one and load the request with it which is not the best
     * use 'php fatapi make:token {unique name}' to generate and update $authorizationToken
     */
    public $authorizationToken = '388b77473d46a13724192ae7735219a2ecae7a1b';

    ...
```
The token should be your newly generated token. Next, you can now share this token to be added to the authorization header for every incoming request as seen below.
```http
Authorization : Bearer <generated token>
```


# Find help for a request method 
Starting new on fatapi? You can access a quick help for specific request methods. See table below
| method | endpoint | example |
|--------|----------|---------|
| GET | domain.com/help | localhost:8080/fatapi/help |
| POST | domain.com/help | localhost:8080/fatapi/help |

# Avaliable CLI Commands
Here are commands that can help you speed up development.

| Command | Example | Description |
|---------|----------|------------|
| make | php fatapi make user | This creates a new service in **src/Resources/** |
| make | php fatapi make user:v2 | This creates a new version for service user in **src/Resources/** |
| make:ext | php fatapi make:ext user | This creates a new service that connects to an external url in  **src/Resources/** |
| make:ext | php fatapi make:ext user:v2 | This creates a new version for an external service that connects to url in  **src/Resources/** |
| make:ware | php fatapi make:ware myMiddleware | This create a middleware that can be attached to a service or request method in **src/Middlewares/** |
| make:model | php fatapi make:model service/modelName | This create a new model for a particular service in **src/Resources/{service}/{version}/Model** |
| make:model | php fatapi make:model service/modelName:v2 | This create a new model for a particular service in **src/Resources/{service}/{version}/Model** |
| make:dbms | php fatapi make:dbms {connectionName} | This create a new connection method for your models in **src/Engine/DBMS.php** |
| make:route | php fatapi make:route {service}/{routeName} | This create a new route method for a service in **src/Resources/{service}/{version}** |
| make:token | php fatapi make:token {unqiue name} | This generates a unqiue token that can be used for request headers and more |


# How to create a new service
Services are like resources that contains one or more routes. They are packed with providers, models and some helpful classes and methods for building a functional and scalable systems.
Here we demostrate how to use the command line to generate one.
```php
php fatapi make {service}
```
where {service} can be a string without special characters execpt (_) and (-) eg. service-name, myservice, user, account, etc.

By default, if you don't include a version to the command, it generates that service with the default version **v1**. But just incase you want a new version, just specify it as seen below
```php
php fatapi make {service}:v2
```

After creating a service called **User** for example on version **v1** you should see the following files and folders
```php
- v1/
    - Documentation/
        - GetUser.md
        - PostUser.md
    - Data/
        - GeneralQuery.php
        - SQL.php
        - UnpackStruct.php
        - Struct.php
    - Model/
    - Events/
        - Listener.php
    - Providers/
        - CreateProvider.php
        - UpdateProvider.php
        - DeleteProvider.php
    - PostUser.php
    - GetUser.php
- readme.md
```
### Folder and file breakdown
Lets look at what this files and folders could help us accomplish and their usefulness.
| Directory | File | Description |
|-----------|------|-------------|
| Documentation | GetUser.md | Providers a documentation for the GetUser service, and can be called if **x-meta-doc** is added to the request header |
| Documentation | PostUser.md | Providers a documentation for the PostUser service, and can be called if **x-meta-doc** is added to the request header |
| Model | ... | Contains all our model classes for our routes and can be generated from the CLI or Terminal |
| Providers | CreateProvider.php | Contains all route methods that triggers a create transaction and are typically just routes that are added from the terminal if structured this way **"php fatapi make:route {service}/create-{routeName}"**|
| Providers | UpdateProvider.php | Contains all route methods that triggers an update transaction and are typically just routes that are added from the terminal if structured this way **"php fatapi make:route {service}/update-{routeName}"**|
| Providers | DeleteProvider.php | Contains all route methods that triggers a delete transaction and are typically just routes that are added from the terminal if structured this way **"php fatapi make:route {service}/delete-{routeName}"**|
| ... | PostUser.php | Our main handler for every post requests sent to this User service |
| ... | GetUser.php | Our main handler for every get requests sent to this User service |

# How to create a new route
Creating a route requires that you must have already generated a service and that the route does not exists for that service. A route would be a trigger to complete a transaction and they typically would take the request data sent, provide some inner workings that makes meaning to the data, and then send a response back through json or xml. Every service must have at least one or more route to be successful and below we would show you a basic command to generate one;
```php
php fatapi make:route {service}/{route} -{option}
```
Where **{option}** can either be **(post or get)**. So why {option}? They help be direct to where you what that route to be added. Remember we have two main request files called **PostUser.php** and **GetUser.php** using a User service as an example. 

Where **{service}** is a valid service name that exists in your **src/Resource/{version}** folder.

Where **{route}** is a valid method that does not exists in the method eg. submit-profile, etc.

## Create a route with a version number
You simply need to add a version number after the route name as seen below;
```php
php fatapi make:route {service}/{route}:{version} -{option}
```
Where **{version}** can be v1, v2, etc. 

## Create a route without an option
You can also benefit from some naming standard we've designed so that you stay consistent with your route names. This implies that you no longer need to add **-post** or **-get** when creating a route. The table below shows just how, and we would demostrate with a service called **User**;

| Keyword | Example | In Action | Description |
|---------|----------------|---------|-------------|
| get | **get**-users | *make:route user/get-users* or | This command creates a **GetUsers** route method in the **GetUser.php** file |
| submit | **submit**-record | *make:route user/submit-record* | This command creates a **SubmitRecord** route method in the **PostUser.php** file |
| create | **create**-user | *make:route user/create-user* | This command creates a **CreateUser** route method in the **Providers/CreateProvider.php** file |
| update | **update**-user | *make:route user/update-user* | This command creates a **UpdateUser** route method in the **Providers/UpdateProvider.php** file |
| delete | **delete**-user | *make:route user/delete-user* | This command creates a **DeleteUser** route method in the **Providers/DeleteProvider.php** file |

# How to make a simple POST request
To demostrate this, we would be using **nodejs** and the **axios** library. Take note of **/api**, that's our single point of entry.
```js
var axios = require('axios');
var FormData = require('form-data');
var data = new FormData();

var config = {
  method: 'post',
  url: 'http://someendpoint.com/api',
  headers: { 
    'x-meta-service': 'user', 
    'x-meta-method': 'login', 
    ...data.getHeaders()
  },
  data : data
};

axios(config)
.then(function (response) {
  console.log(JSON.stringify(response.data));
})
.catch(function (error) {
  console.log(error);
});

```
To obtain more information of what's required to send a request to the server, make this simple request to obtain help. 
```js
var axios = require('axios');

var config = {
  method: 'post',
  url: 'http://someendpoint.com/help',
};

axios(config)
.then(function (response) {
  console.log(response.data);
})
.catch(function (error) {
  console.log(error);
});
```
or just sent a POST request to that url to learn more. 

# How to make a simple GET request
To demostrate this, we would be using **nodejs** and the **axios** library. Take note of **/api**, that's our single point of entry.
```js
var axios = require('axios');

var config = {
  method: 'get',
  url: 'http://someendpoint.com/api',
  headers: { 
    'x-meta-service': 'account', 
  }
};

axios(config)
.then(function (response) {
  console.log(JSON.stringify(response.data));
})
.catch(function (error) {
  console.log(error);
});
```
To obtain more information of what's required to send a request to the server, make this simple request to obtain help. 
```js
var axios = require('axios');

var config = {
  method: 'get',
  url: 'http://someendpoint.com/help',
};

axios(config)
.then(function (response) {
  console.log(response.data);
})
.catch(function (error) {
  console.log(error);
});
```
or just sent a GET request from your browser to that url to learn more.

# Resource configuration style
Here you can instead generate a config.json file and transfer request to an external service using a general style or separate channel with different request method.

Here is a general request style that takes all the request and just push to one endpoint.

## General request style 1
```json
{
    "default": true,
    "type" : "api",
    "url" : "http://google.com/account/v1",
    "response" : {
        "type" : "application/json"
    }
}
```
## Here is a breakdown of what's happening

| Default | Description |
|------|-----|
| True | Would tell the system to use this configuration file |

| Type | Description |
|------|-----|
| Api | Would trigger an HTTP request to the external server |

| URL | Description |
|------|-----|
| Absolute URL | This URL is the server address that takes an route all requests |

| Response | Description |
|------|-----|
| type | This demands that the response type must be **application/json**  |

## Make GET or POST request using Socket.io
At any point in time you desire to utilize socket programming to facilitate communication instead of HTTP requests, we just might have a simple solution for you. Before you continue, ensure that you have 
```php 
composer require workerman/phpsocket.io
``` 
installed or just run 
```php 
php fatapi install socket
``` 
from your cmd or terminal to install all dependencies for socket.io.

Next, we start our socket server by running the following command
```php
php fatapi socket
```
This would start workerman socket server with the address **ws://0.0.0.0:8082**. And you can change this default settings here **framework/src/environment.yaml**

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
                    method : 'all',
                },
                header : {},
                version: 'v1',
                signature: '67shdjddd',
                method: 'get',
                query: {
                    limit: 20
                }   
            }));

            // we can now listen for a response from the socket server 
            // using the unqiue signature.
            socket.on('67shdjddd', (data)=>{
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
        'method'    => 'all'
    ],
    'header'    => [],
    'method'    => 'get',
    'version'   => 'v1',
    'signature' => '8337sijdfu',
    'query'      => [
        'limit' => 20,
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
| query | Object | no | GET query data |
| body | Object | no | POST request body |


# How to use a model in a service
Lets assume that you have created a service and a model. Your model should be avaliable to use from a namespace.

For this example, we would assume a test model in **src/Resources/Student/v1/Model/**. See a summary of what it should look like;

```php
namespace Resources\Student\v1\Model;

use Engine\RequestData;
use Engine\{Interfaces\ModelInterface, DBMS, Table, ModelHelper};
/**
 * @package Test Model
 * @author Amadi Ifeanyi <amadiify.com>
 */
class Test implements ModelInterface
{
    /**
     * This 'ModelHelper' trait contains the fillable method and DB method.
     */
    use ModelHelper;

    /**
     * @var int $id
     * This is significant to your model class. It gets its value when two things happens
     * 1. The system encounters x-meta-id in the request header
     * 2. The POST body sent contains a key 'id' along side a number as its value
     */
    public $id = 0;

    ....
}
```

Now for every request that's sent to a service, there exists a **Request** and **Response** class parameters. And this **Request** class holds the request data that has been validated and verified along side a method called **useModel()** that takes a model class name as a string and loads all the request data into that class for database operations. To demostrate this, we would assume a POST request to the Student service to the default Init method.

```php
namespace Resources\Student\v1;

/**
 * @method PostStudent Init
 * @param Request $request
 * @param Response $response
 * @return void
 * 
 * @start.doc
 * 
 * .. Your documentation content goes in here.
 * 
 */
public function Init(Request $request, Response $response) : void
{
    // our post data may contain
    // username, password, etc
    // next we just push that data to our test model and then run any of the
    // crud operations
    $model = $request->useModel(Model\Test::class);
    // this reads (Resources\Student\v1\Model\Test)

    // next we can run any operation that's avaliable in this model class
    // eg
    $model->Update(); // update a record

    // sample response
    $response->success('It works!');
}
```

# Getting a request ID
You have passed an ID to your request parameter or used the **x-meta-id** method to send an ID with your request? See how to get it below from a service method.
```php
namespace Resources\Student\v1;

/**
 * @method PostStudent Init
 * @param Request $request
 * @param Response $response
 * @return void
 * 
 * @start.doc
 * 
 * .. Your documentation content goes in here.
 * 
 */
public function Init(Request $request, Response $response) : void
{
    // our id is part of the request

    // sample response
    $response->success('It works!', [
        'id' => $request->id
    ]);
}
```

# How to connect your model to a database
First you need to have created a model file, next we create a new connection with the command below
```php
php fatapi make:dbms sessionConnection
```
Where **sessionConnection** can be any valid function name.

This would create a new method **sessionConnection()** in **Engine\DBMS** class located in **src/Engine/DBMS.php**.

The content of this method should look like this:
```php
/**
 * @method DBMS sessionConnection
 * @param string $table
 * @return DriverInterface|
 */
public static function sessionConnection(string $table = '') 
{
    // connection name
    $connectionName = '';

    // get connection
    $connection = self::CreateConnection($connectionName);

    // has table
    return $table != '' ? self::ConnectToTable($connection, $table) : $connection;
}
```
Next, you set the value of **$connectionName** to match a connection key created in Moorexa **src/database/database.php** file. An example of this could be the default 'new-db' connection key. So now, your connection method should look like this
```php
/**
 * @method DBMS sessionConnection
 * @param string $table
 * @return DriverInterface|
 */
public static function sessionConnection(string $table = '') 
{
    // connection name
    $connectionName = 'new-db';

    // get connection
    $connection = self::CreateConnection($connectionName);

    // has table
    return $table != '' ? self::ConnectToTable($connection, $table) : $connection;
}
```

If **connectionName** is left empty, Moorexa would try to use the default connection settings in **src/database/database.php**. Which means that you have to set a value for **development** or **live**. See example below
```php
->default(['development' => 'new-db', 'live' => '']);
```

Perharps you might like to add another connection setting so you make do of multiple connection settings, use the command below
```php
php assist database add connectionName
```
Where connectionName is something you would like to identify that connection with.

Next you open your model file and set the value of **$DBMSConnection** to the new method **sessionConnection or your unquieMethodName** created in **Engine\DBMS** class located in **src/Engine/DBMS.php**. See example below;
```php
namespace Resources\Student\v1\Model;
...
/**
 * @package Test Model
 * @author Amadi Ifeanyi <amadiify.com>
 */
class Test implements ModelInterface
{
    ...
    /**
     * @var string $DBMSConnection
     * This is a connection method name from our Engine\DBMS class and
     * it defaults to this model, to be accessed via 
     * - $this->DB()
     * or
     * - $this->DB(TABLE NAME)
     * Where TABLE NAME is a constant value from Engine\Table class or just a regular name.
     * 
     * You can also make queries to other connections via accessing them through
     * - DBMS::ConnectionName()
     * Where 'ConnectionName' is a connection method from our Engine\DBMS class
     */
    private $DBMSConnection = 'sessionConnection';
}
```
Now, your model is connected to a database using that connection method which typically fulfils every query through a connection key found in **src/database/database.php**.

# How to apply a middleware to a resource
Middleware provides services to your resources beyond those available from the core system. It can be described as "service glue". Creating one is simple and can be done with the following command below, where MIDDLEWARE_NAME is something unquiue to you.
```php
php fatapi make:ware MIDDLEWARE_NAME
```
Lets assume that we have called our middleware **MustBeAuthorized**, now we can apply it to a resource service class or method. To do this, open **src/Resources/middleware.json**. To demostrate this, we would apply this for every post requests particular to a service called **Student**, We can even go further by adding more to the array or be direct to a specific class method **Profile**.
```json
{
    "verbs" : {
        "POST" : ["Middlewares\\PostMustHaveData"]
    },
    "resources" : {
        "Resources\\Student\\v1\\PostStudent" : ["Middlewares\\MustBeAuthorized",],
        "Resources\\Student\\v1\\PostStudent::Profile" : ["Middlewares\\MustBeAuthorized or another middleware"]
    }
}
```

# How to apply a middleware to a method directly
Middlewares can now be added to your methods directly as part of the doc comment. We would demostrate with our assumed middleware **MustBeAuthorized** which can be found in **src/Middlewares/**.
```php
/**
 * @package GetAuthentication
 * @author Amadi Ifeanyi <amadiify.com>
 *
 * @start.doc
 * 
 * .. Your documentation content goes in here.
 */
class GetAuthentication implements ResourceInterface
{
    /**
     * @method GetAuthentication Init
     * @param Request $request
     * @param Response $response
     * @return void
     * @middleware Middlewares\MustBeAuthorized
     * 
     * @start.doc
     * 
     * .. Your documentation content goes in here.
     * 
     */
    public function Init(Request $request, Response $response) : void
    {
        $response->success('It works!');
    }

    ...
```
Take note of the tag **@middleware**! Now, every request into that route must pass that middleware before dealing with the request body. You can have more than one middleware as seen below
```php
/**
 * @package GetAuthentication
 * @author Amadi Ifeanyi <amadiify.com>
 *
 * @start.doc
 * 
 * .. Your documentation content goes in here.
 */
class GetAuthentication implements ResourceInterface
{
    /**
     * @method GetAuthentication Init
     * @param Request $request
     * @param Response $response
     * @return void
     * @middleware Middlewares\MustBeAuthorized
     * @middleware Middlewares\ExampleMiddleWare2
     * 
     * @start.doc
     * 
     * .. Your documentation content goes in here.
     * 
     */
    public function Init(Request $request, Response $response) : void
    {
        $response->success('It works!');
    }

    ...
```


# How to filter every input from a request
You just need to add an entry to the **src/Resources/input.json** file and then the system ensures that what is presented to you has been checked and passed. Else, it stops processing the request and asks the developer for the correct/required data. Now, lets take an example to demostrate, we call this service **student** with a method called **login**. This request method will be POST and would trigger the class file **PostStudent** in the Student resource folder. Now, to lock on this request for the required data we just add this to our **src/Resources/input.json** file

```json
[
    ...

    {
        "service" : "student",
        "method" : "login",
        "version" : "*",
        "verb" : "post",
        "body" : {
            "username" : "required|string|notag",
            "password" : "required|string|min:2"
        }
    }
]
```
If this is successful, it makes the data (username and password) avaliable to you via the **Engine\Request** class. And to demostrate see
```php
namespace Resources\Student\v1;

/**
 * @method PostStudent Init
 * @param Request $request
 * @param Response $response
 * @return void
 * 
 * @start.doc
 * 
 * .. Your documentation content goes in here.
 * 
 */
public function Login(Request $request, Response $response) : void
{
    // get username
    echo $request->username;

    // get password
    echo $request->password;

    // sample response
    $response->success('It works!', [
        'id' => $request->id
    ]);
}
```
Now using a model as showned in this documentation would pre-fill this request data to your model or you customize your model like this also;

```php
namespace Resources\Student\v1\Model;

use Engine\RequestData;
use Engine\{Interfaces\ModelInterface, DBMS, Table, ModelHelper};
/**
 * @package Test Model
 * @author Amadi Ifeanyi <amadiify.com>
 */
class Test implements ModelInterface
{
    /**
     * This 'ModelHelper' trait contains the fillable method and DB method.
     */
    use ModelHelper;

    /**
     * @var int $id
     * This is significant to your model class. It gets its value when two things happens
     * 1. The system encounters x-meta-id in the request header
     * 2. The POST body sent contains a key 'id' along side a number as its value
     */
    public $id = 0;

    // username
    public $Username;

    // password
    private $Password;

    // then add the fillable method
    /**
     * @method ModelInterface Fillable
     * @param RequestData $data
     * @return void
     * 
     * Has data that can be populated to the class 
     */
    public function Fillable(RequestData $data) : void
    {
        // set the username
        $this->Username = $data->username;

        // set the password
        $this->Password = $data->password;
    }

    ....
}
```

# How to switch versions
You can do this from the **src/Resources/versioning.json** file for different request methods. Lets paint a scenario, You've built your services and now you want your consumers to use a new version without having to update your frontend? This is possible for not just a service but for a specific method also. How do we do this? see example below 
```json
{
    "POST" : {
        "ExampleService" : {
            "version" : "v1"
        },
    },
    
    "GET" : {
        "ExampleService" : {
            "version" : "v2"
        }
    }
}
```
ExampleService is just a placeholder and can be your service name eg (student, user, account, etc.). To update the default version of a service method, just add a dot(.) followed by the service method. See example below;

```json
{
    "POST" : {
        ...
        "ExampleService.method" : {
            "version" : "v2"
        },
    },
    ...
}
```

# Default Query Filter for Get Requests
A great API is not complete without search and filters, this we know and here we've built more than one option to manage what to recieve without writing additional code. Here are some of the GET queries you can add to your URL.

1. ?sort=asc or desc
2. ?column=* or name,age etc
3. ?limit=0,4 or more
4. ?sortby=column|asc or desc
5. ?rowid={0-9 or string} 
6. ?search=column|data

# Model Special methods
Here is a complete list of our model magic methods that can be used in an external file for CRUD operations;
| Method | Translate To | Parameter | Example |
|--------|--------------|-----------|---------|
| ReadByID | Read() | {integer} | Resources\ModelClass::ReadByID(9) |
| DeleteByID | Delete() | {integer} | Resources\ModelClass::DeleteByID(9) |
| UpdateByID | Update() | {integer, array} | Resources\ModelClass::UpdateByID(9, [...]) |
| CreateWithData | Create() | {array} | Resources\ModelClass::CreateWithData([...]) |

# Sending Transactional and Business Emails
Out of the box, you can now send emails using the **symfony/mailer** package. We've extended this library to simplify it use cases starting from configuration down to sending mails with attachments and more. For more information on this package please visit [https://symfony.com/doc/current/mailer.html]

Having that out of the way, let's begin with the configuration. Open the file **src/Messaging/Emails/config.php** to make your configuration. 
```php
/**
 * Configuration for sending out emails
 */
return [
    'default' => 'Messaging\Emails\Handlers\SymfonyMailer',
    'dsn' => 'smtp://{user}:{pass}@{host}:{port}',
    'host' => 'smtp.mailtrap.io',
    'port' => 2525,
    'user' => '',
    'pass' => ''
];
```
Our default handler for the SymfonyMailer package is **Messaging\Emails\Handlers\SymfonyMailer::class** and you can change it if you feel like.

## Next, creating dynamic methods and linking them to a template file
You've got it already, from the **src/Messaging/Emails/email-list.json** file you can just add dynamic methods that would represent one or more template files. See an example below;
```json
{
    "sendWelcomeMessage" : {
        "category" : "LoadBusinessTemplate",
        "template" : "welcome",
        "subject" : "Welcome to my application",
        "from" : "",
        "entities" : {
            "name" : "required|string|notag"
        }
    }
}
```
The template "welcome" represents a template file that must have been created in **src/Messaging/Emails/Templates/Business/** directory as **welcome.html** and may contain one or more placeholders to mask actual data. You can also see the category. So far we have two categories and i'll take you where their template files lives;

| Category | Directory |
|----------|-----------|
| LoadBusinessTemplate | *src/Messaging/Emails/Templates/Business/* |
| LoadTransactionalTemplate | *src/Messaging/Emails/Templates/Transactional/* |

You are free to create more categories with additional methods that points to where a template can be fetched when called by the category name in **src/Messaging/Emails/EmailTemplate.php**.

"entities" tells what to expect from the developer. These fields can contain default data as seen below:
```json
    "entities" : {
        "name" : ["required|string|notag", "default name"]
    }
```
The rest is self explanatory. Now, we can send a mail to any email address from anywhere in our application. See example below

```php
use Messaging\Emails\EmailSender;

// send welcome message
EmailSender::sendWelcomeMessage(
    // data to replace placeholders with 
    [
        'name' => 'fatapi' // this would replace {name} with fatapi
    ],

    // extra option
    [
        'background' => true, // this would send the mail in the background
        'subject' => '', // (optional) but can help change the mail subject
        'from' => '', // (optional) but can help change the mail sender
        'to' => 'someone@example.com' // this is the receiver email address 
    ],

    // (optional callback)
    function($email)
    {
        // now you have the Symfony\Component\Mime\Email in $email to work with
        // let try attaching a file
        $email->attach(fopen('/path/to/documents/contract.doc', 'r'));
    }
);
```
# Sending Background Emails
Out of the box you can use the rabbitmq server and client for this. Moorexa already made this easy. Just ensure that you have your **rabbitmq-server** running and then you can run the following command on your cli to start the client listener;
```php
php assist start-rabbitmq-worker
```
Now you can now send background processes like emails, image processing and more. See Doc guide: https://rabbitmq.com/documentation.html for **rabbitmq-server**

# Sending Email Alerts
We've created a class called **Messaging\EmailAlerts::class** that houses all alerts to your server once a transaction is completed. A transaction can either be;
1. New customer order
2. New sign up
3. Failed purchase
etc.

These alerts can be added in the **Messaging\EmailAlerts** class in **src/Messaging/EmailAlerts.php** and called from that namespace when the need arises. See an example below

```php
<?php
namespace Messaging;

use Messaging\Emails\EmailSender;
/**
 * @package EmailAlerts
 * @author Amadi Ifeanyi <amadiify.com>
 * 
 * This alert is meant for internal email notifications
 */
class EmailAlerts
{
    /**
     * @var string $sendTo
     */
    public static $sendTo = 'alerts@yourdomain.com'; // destination address

    /**
     * @var bool $sendInBackground
     * 
     * To get the email to send in the background, ensure rabbitmq is running and rabbitmq client is running
     */
    public static $sendInBackground = true;

    /**
     * @method EmailAlerts newSubscriberAlert
     * @param array $data
     * @return void
     */
    public static function newSubscriberAlert(array $data = [])
    {
        EmailSender::newSubscriberAlert($data, [
            'background'    => self::$sendInBackground,
            'to'            => self::$sendTo,
            'subject'       => 'You have a new email subscriber'
        ]);
    }
}

// now we can just call
EmailAlerts::newSubscriberAlert();

```
Remember, all alerts must have a template file saved in **src/Messaging/Emails/Templates/Business/** for business and **src/Messaging/Emails/Templates/Transactional** for transactional mails like "login, confirm password" etc.


There you go, have fun building great stuffs with it.