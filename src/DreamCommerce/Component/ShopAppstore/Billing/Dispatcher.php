<?php

declare(strict_types=1);

namespace DreamCommerce\Component\ShopAppstore\Billing;

use Doctrine\Common\Persistence\ObjectManager;
use DreamCommerce\Component\Common\Exception\NotDefinedException;
use DreamCommerce\Component\Common\Factory\UriFactoryInterface;
use DreamCommerce\Component\ShopAppstore\Billing\Payload;
use DreamCommerce\Component\ShopAppstore\Billing\Resolver\MessageResolverInterface;
use DreamCommerce\Component\ShopAppstore\Exception\Billing\UnableDispatchException;
use DreamCommerce\Component\ShopAppstore\Factory\ShopFactoryInterface;
use DreamCommerce\Component\ShopAppstore\Model\ApplicationInterface;
use DreamCommerce\Component\ShopAppstore\Model\ShopInterface;
use DreamCommerce\Component\ShopAppstore\Repository\ShopRepositoryInterface;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Sylius\Component\Registry\NonExistingServiceException;
use Sylius\Component\Registry\ServiceRegistry;
use Sylius\Component\Registry\ServiceRegistryInterface;

final class Dispatcher extends ServiceRegistry implements DispatcherInterface
{
    /**
     * @var array
     */
    private $mapActionToClass = [
        self::ACTION_BILLING_INSTALL        => Payload\BillingInstall::class,
        self::ACTION_BILLING_SUBSCRIPTION   => Payload\BillingSubscription::class,
        self::ACTION_INSTALL                => Payload\Install::class,
        self::ACTION_UNINSTALL              => Payload\Uninstall::class,
        self::ACTION_UPGRADE                => Payload\Upgrade::class
    ];

    /**
     * @var ServiceRegistryInterface
     */
    private $applicationRegistry;

    /**
     * @var ShopRepositoryInterface
     */
    private $shopRepository;

    /**
     * @var ShopFactoryInterface
     */
    private $shopFactory;

    /**
     * @var ObjectManager
     */
    private $shopObjectManager;

    /**
     * @var UriFactoryInterface
     */
    private $uriFactory;

    /**
     * @param ServiceRegistryInterface $applicationRegistry
     * @param ShopRepositoryInterface $shopRepository
     * @param ShopFactoryInterface $shopFactory
     * @param ObjectManager $shopObjectManager
     * @param UriFactoryInterface $uriFactory
     */
    public function __construct(ServiceRegistryInterface $applicationRegistry, ShopRepositoryInterface $shopRepository,
                                ShopFactoryInterface $shopFactory, ObjectManager $shopObjectManager,
                                UriFactoryInterface $uriFactory)
    {
        $this->applicationRegistry = $applicationRegistry;
        $this->shopRepository = $shopRepository;
        $this->shopFactory = $shopFactory;
        $this->shopObjectManager = $shopObjectManager;
        $this->uriFactory = $uriFactory;

        parent::__construct(MessageResolverInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(ServerRequestInterface $serverRequest): void
    {
        if($serverRequest->getMethod() !== 'POST') {
            throw UnableDispatchException::forInvalidRequestMethod($serverRequest);
        }

        $params = $serverRequest->getParsedBody();

        try {
            $this->verifyRequirements($params);
        } catch(NotDefinedException $exception) {
            throw UnableDispatchException::forUnfulfilledRequirements($serverRequest, $exception);
        }

        try {
            /** @var ApplicationInterface $application */
            $application = $this->applicationRegistry->get($params['application_code']);
        } catch(NonExistingServiceException $exception) {
            throw UnableDispatchException::forNotExistApplication($serverRequest, $exception);
        }

        $this->verifyPayload($serverRequest, $application, $params);

        /** @var UriInterface $shopUri */
        $shopUri = $this->uriFactory->createNewByUriString($params['shop_url']);

        $shop = $this->shopRepository->findOneByNameAndApplication($params['shop'], $application);
        if($shop === null) {
            $shop = $this->shopFactory->createNewByApplicationAndUri($application, $shopUri);
        } else {
            $shop->setUri($shopUri);
        }

        try {
            /** @var MessageResolverInterface $resolver */
            $resolver = $this->get($params['action']);
        } catch(NonExistingServiceException $exception) {
            throw UnableDispatchException::forNotSupportedAction($serverRequest, $exception);
        }

        $resolver->resolve($this->getPayload($application, $shop, $params));

        $this->shopObjectManager->persist($shop);
        $this->shopObjectManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $identifier): bool
    {
        if(!in_array($identifier, array_keys($this->mapActionToClass))) {
            throw new InvalidArgumentException('Action "' . $identifier . '" is not supported');
        }

        return parent::has($identifier);
    }

    /**
     * @param array $params
     * @throws NotDefinedException
     */
    private function verifyRequirements(array $params = array()): void
    {
        $requiredParams = [ 'action', 'shop', 'shop_url', 'hash', 'timestamp', 'application_code' ];

        if(isset($params['action']) && !empty($params['action'])) {
            switch ($params['action']) {
                case self::ACTION_BILLING_SUBSCRIPTION:
                    $requiredParams[] = 'subscription_end_time';
                    break;
                case self::ACTION_INSTALL:
                    $requiredParams[] = 'application_version';
                    $requiredParams[] = 'auth_code';
                    break;
                case self::ACTION_UPGRADE:
                    $requiredParams[] = 'application_version';
                    break;
            }
        } else {
            throw NotDefinedException::forParameter('action');
        }

        foreach($requiredParams as $requiredParam) {
            if (!isset($params[$requiredParam]) || empty($params[$requiredParam])) {
                throw NotDefinedException::forParameter($requiredParam);
            }
        }
    }

    private function verifyPayload(ServerRequestInterface $serverRequest, ApplicationInterface $application, array $params): void
    {
        $providedHash = $params['hash'];
        unset($params['hash']);

        // sort params
        ksort($params);

        $processedPayload = "";
        foreach($params as $k => $v){
            $processedPayload .= '&'.$k.'='.$v;
        }
        $processedPayload = substr($processedPayload, 1);

        $computedHash = hash_hmac('sha512', $processedPayload, $application->getAppstoreSecret());
        if((string)$computedHash !== (string)$providedHash) {
            throw UnableDispatchException::forInvalidPayloadHash($serverRequest, $application);
        }
    }

    /**
     * @param ApplicationInterface $application
     * @param ShopInterface $shop
     * @param array $params
     * @return Payload\Message
     */
    private function getPayload(ApplicationInterface $application, ShopInterface $shop, array $params): Payload\Message
    {
        $messageClass = $this->mapActionToClass[$params['action']];

        unset($params['action']);
        unset($params['shop']);
        unset($params['shop_url']);
        unset($params['application_code']);

        return new $messageClass($application, $shop, $params);
    }
}