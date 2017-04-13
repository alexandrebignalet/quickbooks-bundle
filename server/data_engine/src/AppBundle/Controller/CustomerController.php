<?php

namespace AppBundle\Controller;

use QuickbooksBundle\Helper\CustomerHelper;
use QuickbooksBundle\Service\CustomerService;
use QuickbooksBundle\Service\QuickbooksEntityService;
use QuickBooksOnline\API\Data\IPPCustomer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CustomerController
 * @package AppBundle\Controller
 * @Route("/customers")
 *
 * ****
 *  <!!!!>
 *      DELETE a Customer is not allowed by Quickbooks API
 *  <!!!!>
 * ****
 */
class CustomerController extends Controller
{
    /**
     * @Route("/", name="customers_create")
     */
    public function createAction()
    {
        /**
         * @var QuickbooksEntityService $qb_entity_manager
         */
        $qb_entity_manager = $this->get("qb.entity_manager");

        $customerObj = new IPPCustomer();
        $customerObj->Name = "Alexandra";
        $customerObj->CompanyName = "Self";
        $customerObj->GivenName = "Jiddy";
        $customerObj->DisplayName = "Jean";

        try {
            $customer = $qb_entity_manager->create($customerObj);
            \Doctrine\Common\Util\Debug::dump($customer);die;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @Route("/query/", name="customers_query")
     */
    public function queryAction()
    {
        /**
         * @var QuickbooksEntityService $qb_entity_manager
         */
        $qb_entity_manager = $this->get("qb.entity_manager");

        $customers = $qb_entity_manager->query("SELECT * FROM Customer");

        \Doctrine\Common\Util\Debug::dump($customers);die;
    }

    /**
     * @Route("/{id}", name="customers_find")
     * @param $id
     */
    public function findAction($id)
    {
        /**
         * @var QuickbooksEntityService $qb_entity_manager
         */
        $qb_entity_manager = $this->get("qb.entity_manager");

        $query_customer = new IPPCustomer();
        $query_customer->Id = $id;

        $customer = $qb_entity_manager->find($query_customer);

        \Doctrine\Common\Util\Debug::dump($customer);die;
    }

    /**
     * @Route("/update/{id}", name="customers_update")
     * @param $id
     */
    public function updateAction($id)
    {
        /**
         * @var QuickbooksEntityService $qb_entity_manager
         */
        $qb_entity_manager = $this->get("qb.entity_manager");

        $query_customer = new IPPCustomer();
        $query_customer->Id = $id;

        $customer = $qb_entity_manager->find($query_customer);

        $customer->GivenName = "TOTOJeanMichGwel";

        $customer_updated = $qb_entity_manager->update($customer);

        \Doctrine\Common\Util\Debug::dump($customer_updated);die;
    }
}