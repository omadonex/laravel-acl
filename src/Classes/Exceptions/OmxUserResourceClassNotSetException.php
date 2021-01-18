<?php

namespace Omadonex\LaravelAcl\Classes\Exceptions;

class OmxUserResourceClassNotSetException extends \Exception
{
    public function __construct()
    {
        $className = get_class($this);
        parent::__construct(trans("acl::exception.{$className}.message"));
    }
}