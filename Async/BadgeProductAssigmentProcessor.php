<?php

namespace Summa\Bundle\BadgeBundle\Async;

use Doctrine\Common\Persistence\ManagerRegistry;
use Monolog\Logger;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use Summa\Bundle\BadgeBundle\Builder\ProductRelationsBuilder;

class BadgeProductAssigmentProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    /** @var ManagerRegistry */
    private $registry;

    /** @var ProductRelationsBuilder */
    private $builder;

    /** @var Logger */
    private $logger;

    /**
     * @param ManagerRegistry $registry
     * @param ProductRelationsBuilder $builder
     * @param Logger $logger
     */
    public function __construct(
        ManagerRegistry $registry,
        ProductRelationsBuilder $builder,
        Logger $logger
    )
    {
        $this->registry = $registry;
        $this->builder  = $builder;
        $this->logger   = $logger;
    }

    /**
     * @param MessageInterface $message
     * @param SessionInterface $session
     * @return string
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $body = JSON::decode($message->getBody());

        if (empty($body['badge_id'])) {
            return self::REJECT;
        }

        try {
            $badge = $this->registry
                ->getRepository('SummaBadgeBundle:Badge')
                ->findOneBy(['id' => $body['badge_id']]);

            if ($this->builder->needRebuild($badge)){
                $this->builder->builder($badge);
            }
            return self::ACK;
        }catch (\Exception $exception){
            $this->logger->error(
                sprintf('Cannot process related Product-Badge for Badge ("%s")', $body['badge_id']),
                ['exception' => $exception]
            );
            return self::REQUEUE;
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedTopics()
    {
        return [Topics::RESOLVE_BADGE_ASSIGNED_PRODUCTS];
    }
}