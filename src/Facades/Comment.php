<?php

namespace Wsmallnews\Comment\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Wsmallnews\Comment\Comment
 */
class Comment extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Wsmallnews\Comment\Comment::class;
    }
}
