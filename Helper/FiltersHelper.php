<?php
/**
 * Date: 12/06/14
 * Time: 14:49
 * Author: Jean-Christophe Cuvelier <jcc@symfonians.be>
 */

namespace Symfonians\AdminBundle\Helper;


use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RequestStack;

class FiltersHelper {

    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var FormFactory
     */
    private $formFactory;
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(RequestStack $requestStack, FormFactory $formFactory, EntityManager $entityManager)
    {
        $this->requestStack = $requestStack;
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
    }

    /**
     * @return null|\Symfony\Component\HttpFoundation\Request
     */
    private function getRequest()
    {
        return $this->requestStack->getCurrentRequest();
    }

    /**
     * @param AbstractType $type
     * @return \Symfony\Component\Form\Form
     */
    public function getFiltersForm($type)
    {
        if($this->getRequest()->request->get('reset'))
        {
            $this->getRequest()->getSession()->set($type->getName() . '.filters', array());
        }

        $filters = $this->getRequest()->getSession()->get($type->getName() . '.filters', array());

        if(is_array($filters))
        {
            foreach($filters as $key => $filter)
            {
                if(is_object($filter))
                {
                    $filters[$key] = $this->entityManager->merge($filter);
                }
            }
        }

        $filters_form = $this->formFactory->create($type, $filters);

        if(!$this->getRequest()->request->get('reset'))
        {
            $filters_form->handleRequest($this->getRequest());

            if($filters_form->isValid())
            {
                $filters = $filters_form->getData();

                $this->getRequest()->getSession()->set($type->getName() . '.filters', $filters);
            }
        }

        return $filters_form;
    }

} 