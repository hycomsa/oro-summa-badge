<?php

namespace Summa\Bundle\BadgeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\FormBundle\Model\UpdateHandler;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Summa\Bundle\BadgeBundle\Form\Handler\BadgeHandler;
use Summa\Bundle\BadgeBundle\Form\Type\BadgeType;
use Summa\Bundle\BadgeBundle\Entity\Badge;

class BadgeController extends AbstractController
{

    /**
     * @Route("/", name="summa_badge_index")
     * @Template("SummaBadgeBundle:Badge:index.html.twig")
     * @AclAncestor("summa_badge_view")
     *
     * @return array
     */
    public function indexAction()
    {
        return [
            'entity_class' => Badge::class
        ];
    }

    /**
     * @Route("/create", name="summa_badge_create")
     * @Template("SummaBadgeBundle:Badge:update.html.twig")
     * @AclAncestor("summa_badge_create")
     *
     * @param Request $request
     * @return array
     */
    public function createAction(Request $request)
    {
        return $this->update(new Badge(), $request);
    }

    /**
     * @Route("/update/{id}", name="summa_badge_update", requirements={"id"="\d+"})
     * @Template("SummaBadgeBundle:Badge:update.html.twig")
     * @AclAncestor("summa_badge_update")
     *
     * @param Badge $badge
     * @param Request $request
     * @return array
     */
    public function updateAction(Badge $badge, Request $request)
    {
        return $this->update($badge, $request);
    }

    /**
     * @Route("/view/{id}", name="summa_badge_view", requirements={"id"="\d+"})
     * @AclAncestor("summa_badge_view")
     * @Template("SummaBadgeBundle:Badge:view.html.twig")
     *
     * @param Badge $badge
     * @return array
     */
    public function viewAction(Badge $badge)
    {
        return [
            'entity'    =>  $badge
        ];
    }

    /**
     * @Route("/info/{id}", name="summa_badge_info", requirements={"id"="\d+"})
     * @AclAncestor("summa_badge_view")
     * @Template("SummaBadgeBundle:Badge/widget:info.html.twig")
     *
     * @param Badge $badge
     * @return array
     */
    public function infoAction(Badge $badge)
    {
        return [
            'entity' => $badge
        ];

    }

    /**
     * @Route("/delete/{id}", name="summa_badge_delete", requirements={"id"="\d+"})
     *
     * @AclAncestor("summa_badge_delete")
     * @Template()
     *
     * @param Badge $entity
     * @return array
     */
    public function deleteAction(int $id)
    {
        return true;
    }

    /**
     * @param Badge $badge
     * @param Request $request
     * @return array|RedirectResponse
     */
    protected function update(Badge $badge, Request $request)
    {
        return $this->get(UpdateHandler::class)->handleUpdate(
            $badge,
            $this->createForm(BadgeType::class, $badge),
            function (Badge $badge) {
                return [
                    'route' => 'summa_badge_view',
                    'parameters' => ['id' => $badge->getId()]
                ];
            },
            function (Badge $badge) {
                return [
                    'route' => 'summa_badge_index',
                    'parameters' => []
                ];
            },
            $this->get(TranslatorInterface::class)->trans('summa.badge.controller.saved.message')
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                TranslatorInterface::class,
                UpdateHandler::class
            ]
        );
    }
}
