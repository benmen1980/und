<?php
namespace Psr\SimpleCache;

interface CacheInterface {}

/*...*/
interface CacheException
{
}

/*...*/
interface InvalidArgumentException extends CacheException
{
}