<?php

namespace Summa\Bundle\BadgeBundle\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use http\Exception;
use Oro\Bundle\LayoutBundle\Annotation\Layout;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\FilterBundle\Grid\Extension\AbstractFilterExtension;
use Summa\Bundle\BadgeBundle\Entity\Badge;
use Summa\Bundle\BadgeBundle\Form\Type\BadgeType;

class ProductBadgeFrontendController extends BaseController
{

    /**
     * @Layout
     * @Route("/", name="badge_front_crud")
     *
     * @Method({"GET", "POST"})
     * @param Request $request
     */
    public function badgesAction(Request $request)
    {

        $badge = new Badge();
        $form = $this->createForm( BadgeType::class, $badge);

        try {
            $form->handleRequest($request);
            if ($form->isSubmitted() ) {
                if ($form->isValid()) {
                    $this->getDoctrine()->getManager()->persist($badge);
                    $this->getDoctrine()->getManager()->flush();
                    $this->get('session')->getFlashBag()->add('success', 'Badge guardada correctamente.');
                } else {
                    $error = $form->getErrors(true)->current();
                    $message = $error->getMessage() ;
                    throw new \Exception($message);
                }
            }
        } catch (\Exception $e) {
            $this->get('session')->getFlashBag()->add('error', $e->getMessage());
        }

        return [
            'data' => [
                'form' => $form->createView(),
            ],
        ];
    }

}