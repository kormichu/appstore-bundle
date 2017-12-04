<?php

declare(strict_types=1);

namespace DreamCommerce\Component\ShopAppstore\Billing\Resolver;

use DreamCommerce\Component\ShopAppstore\Billing\Payload\BillingInstall;
use DreamCommerce\Component\ShopAppstore\Billing\Payload\Message;
use DreamCommerce\Component\ShopAppstore\ShopBillingTransitions;
use SM\Factory\FactoryInterface;
use Webmozart\Assert\Assert;

final class BillingInstallResolver implements MessageResolverInterface
{
    /**
     * @var FactoryInterface
     */
    private $billingStateMachineFactory;

    /**
     * @param FactoryInterface $billingStateMachineFactory
     */
    public function __construct(FactoryInterface $billingStateMachineFactory)
    {
        $this->billingStateMachineFactory = $billingStateMachineFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Message $message): void
    {
        Assert::isInstanceOf($message, BillingInstall::class);

        $stateMachine = $this->billingStateMachineFactory->get($message->getShop(), ShopBillingTransitions::GRAPH);
        $stateMachine->apply(ShopBillingTransitions::TRANSITION_PAY);
    }
}