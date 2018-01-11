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

final class OptionValue extends Resource implements IdentifierAwareInterface
{
    /**
     * This option type doesn't support values management
     */
    const HTTP_ERROR_OPTION_CHILDREN_NOT_SUPPORTED = 'option_children_not_supported';

    /**
     * option value decreases prices
     */
    const PRICE_TYPE_DECREASE = -1;

    /**
     * option value keeps price unchanged
     */
    const PRICE_TYPE_KEEP = 0;

    /**
     * option value increases price
     */
    const PRICE_TYPE_INCREASE = 1;

    /**
     * price changed by percent
     */
    const PRICE_PERCENT = 0;

    /**
     * price changed by value
     */
    const PRICE_AMOUNT = 1;

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'option-values';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierName(): string
    {
        return 'ovalue_id';
    }
}