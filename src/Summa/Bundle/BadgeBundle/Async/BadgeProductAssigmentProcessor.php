<?php

namespace Summa\Bundle\BadgeBundle\Async;

use Doctrine\Common\Persistence\ManagerRegistry;
use Monolog\Logger;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;

class BadgeProductAssigmentProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(
        ManagerRegistry $registry
    )
    {
        $this->registry = $registry;
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

            // Remove invalid Assigment

            // Add new Assigment

            return self::ACK;

        }catch (\Exception $e){
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