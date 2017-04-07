<?php

namespace QuickbooksBundle\Service;


use QuickbooksBundle\Helper\CustomerHelper;

class CustomerService extends QuickbooksEntitiesService
{

    /**
     * @param $customer
     * @return array
     */
    public function create($customer)
    {
        $customer = $this->data_service->Add($customer);
        $error = $this->data_service->getLastError();
        if ($error != null)
//            \Doctrine\Common\Util\Debug::dump("The Status code is: " . $error->getHttpStatusCode() . "\n".
//            "The Helper message is: " . $error->getOAuthHelperError() . "\n".
//            "The Response message is: " . $error->getResponseBody() . "\n");
        return [
            "customer"  => $customer,
            "errors"    => $this->data_service->getLastError() !== null
        ];
    }

    /**
     * @param string
     * @return array
     */
    public function query($sql_query)
    {
        /**
         * @var array $entities
         */
        return $this->data_service->Query($sql_query);
    }

    public function find()
    {
        return CustomerHelper::getCustomer($this->data_service);
    }
}