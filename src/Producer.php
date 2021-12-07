<?php

namespace MohammadMehrabani\ConsumerGenerator;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Producer
{
    protected $queue;
    protected $passive = false;
    protected $durable = true;
    protected $exclusive = false;
    protected $autoDelete = false;
    protected $exchange = '';
    protected $deliveryMode = 2;

    public function setQueue($queue)
    {
        $this->queue = $queue;

        return $this;
    }

    public function setPassive($passive)
    {
        $this->passive = $passive;

        return $this;
    }

    public function setDurable($durable)
    {
        $this->durable = $durable;

        return $this;
    }

    public function setExclusive($exclusive)
    {
        $this->exclusive = $exclusive;

        return $this;
    }

    public function setAutoDelete($autoDelete)
    {
        $this->autoDelete = $autoDelete;

        return $this;
    }

    public function setExchange($exchange)
    {
        $this->exchange = $exchange;

        return $this;
    }

    public function setDeliveryMode($deliveryMode)
    {
        $this->deliveryMode = $deliveryMode;

        return $this;
    }

    /**
     * Sends an message to the workers
     *
     * @param string $message
     */
    public function send($message)
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
            $this->queue,       #queue - Queue names may be up to 255 bytes of UTF-8 characters
            $this->passive,     #passive - can use this to check whether an exchange exists without modifying the server state
            $this->durable,     #durable, make sure that RabbitMQ will never lose our queue if a crash occurs - the queue will survive a broker restart
            $this->exclusive,   #exclusive - used by only one connection and the queue will be deleted when that connection closes
            $this->autoDelete   #auto delete - queue is deleted when last consumer unsubscribes
        );

        $msg = new AMQPMessage(
            $message,
            ['delivery_mode' => $this->deliveryMode] # make message persistent, so it is not lost if server crashes or quits
        );

        $channel->basic_publish(
            $msg,             #message
            $this->exchange,  #exchange
            $this->queue      #routing key (queue)
        );

        $channel->close();
        $connection->close();
    }
}