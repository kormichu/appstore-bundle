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

final class OrderProduct extends Resource implements IdentifierAwareInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'order-products';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierName(): string
    {
        return 'id';
    }
}