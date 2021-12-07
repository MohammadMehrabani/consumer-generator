<?php

namespace MohammadMehrabani\ConsumerGenerator;

use PhpAmqpLib\Message\AMQPMessage;

interface ConsumerInterface
{
    public function listen();
    public function process(AMQPMessage $msg);
}
