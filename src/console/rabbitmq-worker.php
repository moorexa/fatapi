<?php
use Lightroom\Core\BootCoreEngine;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Lightroom\Queues\QueueHandler;

// load configuration
$configuration = (isset($_ENV['rabbitmq']) ? $_ENV['rabbitmq'] : null); 

if ($configuration !== null) :

    // open connection
    $connection = new AMQPStreamConnection(
        $configuration['address'], 
        $configuration['port'], 
        $configuration['username'], 
        $configuration['password']
    );

    // load channel
    $channel = $connection->channel();

    // listen for jobs
    $channel->queue_declare($configuration['queueName'], false, true, false, false);

    // waiting 
    echo " [*] Waiting for messages. To exit press CTRL+C\n";

    $callback = function ($msg) {

        // un-serialize body
        $class = unserialize($msg->body);

        // print job name
        echo ' [x] Received ', $class->getJobName(), "\n";

        // get the closure scope
        $closureScope = $class->getClosureScope();

        // continue if class does not exists
        if (!class_exists($closureScope['namespace'])) :

            // register alias
            BootCoreEngine::registerAliases([
                // register closure class
                $closureScope['namespace'] => $closureScope['file']
            ]);

        endif;

        // completed 
        $completed = false;

        try 
        {
            // get the job
            $job = unserialize($class->getJob());

            // get closure
            $closure = $job->getClosure();

            // return value
            $returnValue = null;

            // call now
            if (is_callable($closure)) :

                // trigger taskReceived event here
                QueueHandler::triggerEvent('taskReceived', $class->getJobName(), $closure);

                try
                {
                    // call closure function
                    $returnValue = $closure();

                    // completed
                    $completed = true;
                }
                catch(Throwable $exception)
                {
                    echo ' [error] ' , $exception->getMessage() , "\n";
                }

            endif;
        }
        catch(Throwable $exception)
        {
            echo ' [error] ' , $exception->getMessage() , "\n";
        }

        // job has been completed
        if ($completed) :

            echo " [x] Done\n";
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);

            // trigger taskComplete event here
            QueueHandler::triggerEvent('taskComplete', $class->getJobName(), $returnValue);

        endif;

        
    };

    $channel->basic_qos(null, 1, null);
    $channel->basic_consume($configuration['queueName'], '', false, false, false, false, $callback);

    while ($channel->is_consuming()) {
        $channel->wait();
    }

    $channel->close();
    $connection->close();

else:

    echo " [error] Could not load rabbitmq configuration from \$_ENV\n";

endif;