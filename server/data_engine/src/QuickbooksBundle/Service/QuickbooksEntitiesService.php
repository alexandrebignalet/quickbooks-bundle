<?php

namespace QuickbooksBundle\Service;

use QuickbooksBundle\Entity\OAuthInfo;
use QuickbooksBundle\Repository\OAuthInfoRepository;
use QuickBooksOnline\API\Core\CoreConstants;
use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\Data\IPPNameBase;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Security\OAuthRequestValidator;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

abstract class QuickbooksEntitiesService
{
    /**
     * @var OAuthInfo $oauth_info
     */
    private $oauth_info;

    /**
     * @var string $consumer_key
     */
    private $consumer_key;

    /**
     * @var string $consumer_secret
     */
    private $consumer_secret;

    /**
     * @var DataService $data_service
     */
    protected $data_service;

    /**
     * InvoiceService constructor.
     *
     * @param OAuthInfoRepository $repository
     * @param string $consumer_key
     * @param string $consumer_secret
     */
    public function __construct($repository, $consumer_key, $consumer_secret)
    {
        $this->oauth_info = $repository->get();

        if ($this->oauth_info === null)
        {
            throw new AuthenticationException("Not authenticated to quickbooks");
        }

        $this->consumer_key = $consumer_key;
        $this->consumer_secret = $consumer_secret;

//        $request_validator = new OAuthRequestValidator($this->oauth_info->getAccessToken(),
//            $this->oauth_info->getAccessTokenSecret(),
//            $this->consumer_key,
//            $this->consumer_secret);
//
//        $service_context = new ServiceContext($this->oauth_info->getCompanyId(), $service_type, $request_validator);
//        if (!$service_context)
//            throw new AuthenticationException("Problem while initializing ServiceContext.");
//
//        $data_service = new DataService($service_context);
//        if (!$data_service)
//            throw new AuthenticationException("Problem while initializing DataService.");

        $this->data_service = DataService::Configure(array(
            'auth_mode' => 'oauth1',
            'consumerKey' => $this->consumer_key,
            'consumerSecret' => $this->consumer_secret,
            'accessTokenKey' => $this->oauth_info->getAccessToken(),
            'accessTokenSecret' => $this->oauth_info->getAccessTokenSecret(),
            'QBORealmID' => $this->oauth_info->getCompanyId()
        ));
    }

    /**
     * @param $ipp_entity
     * @return bool
     */
    abstract public function create($ipp_entity);

    /**
     * @param string
     * @return array
     */
    abstract public function query($sql_query);
}