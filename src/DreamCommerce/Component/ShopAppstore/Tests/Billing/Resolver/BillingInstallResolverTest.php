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

namespace DreamCommerce\Component\ShopAppstore\Tests\Billing;

use DreamCommerce\Component\ShopAppstore\Billing\Payload\BillingInstall;
use DreamCommerce\Component\ShopAppstore\Billing\Payload\Message;
use DreamCommerce\Component\ShopAppstore\Billing\Resolver\BillingInstallResolver;
use DreamCommerce\Component\ShopAppstore\Billing\Resolver\MessageResolverInterface;
use DreamCommerce\Component\ShopAppstore\Model\ApplicationInterface;
use DreamCommerce\Component\ShopAppstore\Model\OAuthShopInterface;
use DreamCommerce\Component\ShopAppstore\ShopBillingTransitions;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SM\Factory\FactoryInterface;
use SM\StateMachine\StateMachineInterface;

class BillingInstallResolverTest extends TestCase
{
    /**
     * @var BillingInstallResolver
     */
    protected $resolver;

    /**
     * @var FactoryInterface|MockObject
     */
    protected $billingStateMachineFactory;

    public function setUp()
    {
        $this->billingStateMachineFactory = $this->getMockBuilder(FactoryInterface::class)->getMock();
        $this->resolver = new BillingInstallResolver($this->billingStateMachineFactory);
    }

    public function testShouldImplements()
    {
        $this->assertInstanceOf(MessageResolverInterface::class, $this->resolver);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidArgumentWhileResolve()
    {
        $message = $this->getMockBuilder(Message::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->resolver->resolve($message);
    }

    public function testValidResolve()
    {
        /** @var ApplicationInterface $application */
        $application = $this->getMockBuilder(ApplicationInterface::class)->getMock();
        /** @var OAuthShopInterface $shop */
        $shop = $this->getMockBuilder(OAuthShopInterface::class)->getMock();

        $message = new BillingInstall($application, $shop);
        $this->billingStateMachineFactory
            ->expects($this->once())
            ->method('get')
            ->will($this->returnCallback(function ($fShop, $graph) use ($shop) {
                $this->assertEquals($shop, $fShop);
                $this->assertEquals(ShopBillingTransitions::GRAPH, $graph);

                $stateMachine = $this->getMockBuilder(StateMachineInterface::class)->getMock();
                $stateMachine
                    ->expects($this->once())
                    ->method('apply')
                    ->will($this->returnCallback(function ($transition) {
                        $this->assertEquals(ShopBillingTransitions::TRANSITION_PAY, $transition);
                    }));

                return $stateMachine;
            }));

        $this->resolver->resolve($message);
    }
}