<?php

namespace QuickbooksBundle\Service;

use Doctrine\ORM\EntityManager;
use QuickbooksBundle\Entity\OAuthInfo;
use QuickbooksBundle\Helper\InvoiceHelper;
use QuickbooksBundle\Repository\OAuthInfoRepository;
use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Security\OAuthRequestValidator;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class InvoiceService {

    private $em;
    private $consumer_key;
    private $consumer_secret;

    private $service_type = IntuitServicesType::QBO;
    private $request_validator;
    private $service_context;
    private $data_service;

    /**
     * InvoiceService constructor.
     *
     * @param OAuthInfoRepository $repository
     * @param string $consumer_key
     * @param string $consumer_secret
     */
    public function __construct($repository, $consumer_key, $consumer_secret)
    {
        /**
         * @var OAuthInfo $oauth_info
         */
        $oauth_info = $repository->get();

        if ($oauth_info === null)
        {
            throw new AuthenticationException("Not authenticated to quickbooks");
        }

        $this->request_validator = new OAuthRequestValidator($oauth_info->getAccessToken(), $oauth_info->getAccessTokenSecret(), $consumer_key, $consumer_secret);

        $this->service_context = new ServiceContext($oauth_info->getCompanyId(), $this->service_type, $this->request_validator);
        if (!$this->service_context)
            throw new Exception("Problem while initializing ServiceContext.");

        $this->data_service = new DataService($this->service_context);
        if (!$this->data_service)
            throw new Exception("Problem while initializing DataService.");
    }

    public function createInvoice()
    {
        return $this->data_service->Add(InvoiceHelper::getInvoiceFields($this->data_service));
    }
}