<?php

namespace Spark\Http;

class NotFoundException extends \RuntimeException implements \Spark\Exception
{
    protected $code = 404;
}
