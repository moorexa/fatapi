<?php
namespace Engine;

use function Lightroom\Requests\Functions\{headers, post, get};
use Lightroom\Socket\Interfaces\SocketListenerInterface;
/**
 * @package MetaDataSocket
 * @author Amadi Ifeanyi <amadiify.com>
 */
class MetaDataSocket implements SocketListenerInterface
{
    /**
     * @var array $connections
     */
    private static $connections = [];

    /**
     * @var mixed $io
     */
    private static $io;

    /**
     * @var array $activities
     */
    private static $activities = [];

    /**
     * @method SocketListenerInterface emit
     * @param string $event
     * @param array $config
     * This would emit an event from the socket server when something happens
     */
    public static function emit(string $event, array $config = [])
    {
        // @var mixed $socket
        $socket = $config['connection'];

        // @var mixed $server 
        self::$io = $config['data'];
        
        // api entry
        $socket->on('meta.api', function($data) use ($socket){

            // read encoded data
            $jsonData = json_decode($data);

            // is object??
            if (is_object($jsonData)) :

                // build the data format
                $rule = [
                    'meta'      => 'required|object',
                    'header'    => ['required|object', (object)[]],
                    'query'     => ['required|object', (object)[]],
                    'method'    => 'required|string|notag|min:3',
                    'version'   => ['required|string|notag', func()->finder('version')],
                    'signature' => 'string|notag',
                    'body'      => ['required|object', (object)[]]
                ];

                // validate the data sent
                $filter = filter((array) $jsonData, $rule);

                // are we good 
                if ($filter->isOk()) :

                    // set the request method
                    $_SERVER['REQUEST_METHOD'] = strtoupper($filter->method);

                    // env settings
                    $_ENV['USE_HEADER_FUNCTION'] = false;

                    // get the header class
                    $headerClass = headers();

                    // add service and method to header
                    foreach($filter->meta as $key => $value) $headerClass->set('x-meta-'.$key, $value);

                    // format header
                    $header = (array) $filter->header;

                    // set the header
                    if (count($header) > 0) foreach ($header as $key => $value) $headerClass->set($key, $value);

                    // format query
                    $query = (array) $filter->query;

                    // set the query
                    if (count($query) > 0) foreach ($query as $key => $value) get()->set($key, $value);

                    // format query
                    $body = (array) $filter->body;

                    // set the body
                    if (count($body) > 0) foreach ($body as $key => $value) post()->set($key, $value);

                    // get container file from services directory
                    $containerFile = get_path(func()->const('services'), '/container.php');

                    // load container file
                    if (file_exists($containerFile)) include_once $containerFile;

                    // allow for rendering again
                    \Lightroom\Templates\TemplateHandler::$renderCalled = false;

                    // cache output
                    $_ENV['CACHE_RENDER_OUTPUT'] = true;

                    // continue with version
                    MetaDataService::CreateWithVersion($filter->version);

                    // let us know that a request has been made
                    echo "#META Request: ".json_encode($filter->meta).", Signature: {$filter->signature}" . PHP_EOL;

                    // send response
                    self::reply($socket, $filter->signature, $_ENV['RENDER_CONTENT_OUTPUT_CACHE']);

                else:

                    // inform publisher
                    echo "Data format submitted was not properly formatted. We have sent a message to 'meta.api.error'" . PHP_EOL;

                    // Send reply
                    self::reply($socket, 'meta.api.error', 'The data / '. $data . '/ was not properly formatted. Please see the format below '. "\n\n" . json_encode($rule, JSON_PRETTY_PRINT));

                endif;

            endif;
            
        });
    }

    /**
     * @method Connections reply
     * @param mixed $socket
     * @param string $event
     * @param mixed $data
     */
    private static function reply($socket, string $event, $data)
    {
        // emit reply back to a user
        self::$io->to($socket->id)->emit($event, $data);
    }

    /**
     * @method SocketListenerInterface events
     * This method should register all your events to be listened for by the emit method
     * @return array
     */
    public static function events() : array
    {

    }

    /**
     * @method Connections debug
     * @param string $event 
     * @param mixed $data 
     */
    public static function debug(string $event, $data)
    {
        //fwrite(STDOUT, 'Event Fired: "' . $event . '", data :' . $data . PHP_EOL);
    }
}