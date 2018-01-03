<?php

/*
 * This file is part of the DreamCommerce Shop AppStore package.
 *
 * (c) DreamCommerce
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace DreamCommerce\Component\ShopAppstore\Api\Resource;

use DreamCommerce\Component\ShopAppstore\Api\Resource;

class Status extends Resource
{
    /**
     * status: new
     */
    const TYPE_NEW = 1;

    /**
     * status: opened
     */
    const TYPE_OPENED = 2;

    /**
     * status: closed
     */
    const TYPE_CLOSED = 3;

    /**
     * status: not completed
     */
    const TYPE_UNREALIZED = 4;

    protected $name = 'statuses';
}