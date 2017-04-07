<?php

namespace AppBundle\Controller;

use QuickbooksBundle\Helper\CustomerHelper;
use QuickbooksBundle\Service\CustomerService;
use QuickBooksOnline\API\Data\IPPCustomer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CustomerController
 * @package AppBundle\Controller
 * @Route("/customers")
 */
class CustomerController extends Controller
{
    /**
     * @Route("/", name="customers_create")
     */
    public function createAction()
    {
        /**
         * @var CustomerService $customer_service
         */
        $customer_service = $this->get("qb.customers");

        $customer = $customer_service->create(CustomerHelper::getCustomerFields());


        return new Response("Not Created...");
    }

    /**
     * @Route("/query/", name="customers_query")
     */
    public function queryAction()
    {
        /**
         * @var CustomerService $customer_service
         */
        $customer_service = $this->get("qb.customers");

        $customers = $customer_service->query("SELECT * FROM Customer");

        \Doctrine\Common\Util\Debug::dump($customers);die;
    }
}