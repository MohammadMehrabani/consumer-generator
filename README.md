# Laravel - Consumer Generator

![version](https://img.shields.io/badge/version-1.0.0-blue.svg) ![license](https://img.shields.io/badge/license-MIT-green.svg)

## Installation
You can install the package via Composer:
``` bash
composer require mohammadmehrabani/consumer-generator:dev-master
```

Next, you must install the service provider to `config/app.php`:

```php
'providers' => [
    // for laravel 5.4 and below
    MohammadMehrabani\ConsumerGenerator\ConsumerGeneratorServiceProvider::class,
];
```

Then, if you want to customize folder names, namespaces, etc... You need to publish config with command:
``` bash
php artisan vendor:publish --provider="MohammadMehrabani\ConsumerGenerator\ConsumerGeneratorServiceProvider" --tag="config"
```

Now you can edit `config/consumer-generator.php`

## Usage Consumer
Before using `generate` commdand you should customize `config/consumer-generator.php` for your own use.
You can simply use `consumer:generate` command by terminal:
``` bash
php artisan consumer:generate SMS --queue=sms
```
after run, by default create `Consumers` folder in `app` directory and create `SMSConsumer.php`.
``` php
<?php

namespace App\Consumers;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class SMSConsumer implements \MohammadMehrabani\ConsumerGenerator\ConsumerInterface
{
    protected $queue = 'sms';

    /**
     * Process incoming request
     */
    public function listen()
    {
        $connection = new AMQPStreamConnection(
            config('consumer-generator.host'),
            config('consumer-generator.port'),
            config('consumer-generator.user'),
            config('consumer-generator.password'),
            config('consumer-generator.vhost')
        );

        $channel = $connection->channel();

        $channel->queue_declare(
            $this->queue,          #queue
            false,                 #passive
            true,                  #durable, make sure that RabbitMQ will never lose our queue if a crash occurs
            false,                 #exclusive - queues may only be accessed by the current connection
            false                  #auto delete - the queue is deleted when all consumers have finished using it
        );

        /**
         * don't dispatch a new message to a worker until it has processed and
         * acknowledged the previous one. Instead, it will dispatch it to the
         * next worker that is not still busy.
         */
        $channel->basic_qos(
            null,        #prefetch size - prefetch window size in octets, null meaning "no specific limit"
            1,           #prefetch count - prefetch window in terms of whole messages
            null         #global - global=null to mean that the QoS settings should apply per-consumer, global=true to mean that the QoS settings should apply per-channel
        );

        /**
         * indicate interest in consuming messages from a particular queue. When they do
         * so, we say that they register a consumer or, simply put, subscribe to a queue.
         * Each consumer (subscription) has an identifier called a consumer tag
         */
        $channel->basic_consume(
            $this->queue,           #queue
            '',                     #consumer tag - Identifier for the consumer, valid within the current channel. just string
            false,                  #no local - TRUE: the server will not send messages to the connection that published them
            false,                  #no ack, false - acks turned on, true - off.  send a proper acknowledgment from the worker, once we're done with a task
            false,                  #exclusive - queues may only be accessed by the current connection
            false,                  #no wait - TRUE: the server will not respond to the method. The client should not wait for a reply method
            [$this, 'process']      #callback
        );

        while(count($channel->callbacks)) {
            echo 'Waiting for incoming messages'.PHP_EOL;
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }

    /**
     * process received request
     *
     * @param AMQPMessage $msg
     */
    public function process(AMQPMessage $msg)
    {
        // handle code

            // $msg->getBody();

        // end handle code

        /**
         * If a consumer dies without sending an acknowledgement the AMQP broker
         * will redeliver it to another consumer or, if none are available at the
         * time, the broker will wait until at least one consumer is registered
         * for the same queue before attempting redelivery
         */

        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
    }
}
```
start code in:

``` php

public function process(AMQPMessage $msg)
{
    // handle code

        $data = json_decode($msg->getBody(), 1);
        $mobile = $data['mobile'];
        // send SMS ...

    // end handle code
```
## Usage Producer
``` php
$user = ['user_id' => 12, 'mobile' => '09xxxxxxxxx'];
$producer = new \MohammadMehrabani\ConsumerGenerator\Producer();
$producer->setQueue('sms')->send(json_encode($user));
```

## Contributing
 
Thank you for considering contributing to the Consumer Generator! The contribution guide can be found in the CONTRIBUTING.md
