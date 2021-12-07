<?php

namespace MohammadMehrabani\ConsumerGenerator\Exceptions;

use Exception;

class StubException extends Exception
{
    public static function fileNotFound($file)
    {
        return new static('Stub file does not exists: '.$file);
    }
}
