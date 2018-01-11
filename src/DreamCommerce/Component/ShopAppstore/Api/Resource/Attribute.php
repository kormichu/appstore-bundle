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

final class Attribute extends Resource implements IdentifierAwareInterface
{
    /**
     * field type text
     */
    const TYPE_TEXT = 0;

    /**
     * field type checkbox
     */
    const TYPE_CHECKBOX = 1;

    /**
     * field type select
     */
    const TYPE_SELECT = 2;

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'attributes';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierName(): string
    {
        return 'attribute_id';
    }
}