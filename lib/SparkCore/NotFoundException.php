<?php

namespace SparkCore;

class NotFoundException extends \RuntimeException implements Exception
{
    protected $code = 404;
}
