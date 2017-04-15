<?php

namespace QuickbooksBundle\Controller;


use QuickbooksBundle\Entity\OAuthInfo;
use QuickbooksBundle\Repository\OAuthInfoRepository;
use OAuth;
use OAuthException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;


class OAuthController extends Controller
{
    const OAUTH_REQUEST_URL = 'https://oauth.intuit.com/oauth/v1/get_request_token';
    const OAUTH_ACCESS_URL = 'https://oauth.intuit.com/oauth/v1/get_access_token';
    const OAUTH_AUTHORISE_URL = 'https://appcenter.intuit.com/Connect/Begin';


    /**
     * @Route("/disconnect", name="disconnect")
     */
    public function oAuthDisconnectAction()
    {
        $this->removeOAuthInfo();
        return $this->redirect( $this->generateUrl('oauth_connection'));
    }

    /**
     * @Route("/oauth_connection", name="oauth_connection")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function oAuthConnectionAction(Request $request)
    {
        $base_url = $request->getScheme() . '://' . $request->getHttpHost();
        $callback_url = $base_url.$this->generateUrl('oauth_callback_url');

        /**
         * @var OAuthInfoRepository $oauth_info_repository
         */
        $oauth_info_repository = $this->getDoctrine()->getRepository('QuickbooksBundle:OAuthInfo');

        /**
         * @var OAuthInfo $oauth_info
         */
        $oauth_info = $oauth_info_repository->get();

        /**
         * Unique object does not exist -> not authenticated -> go authentication
         */
        if ($oauth_info === null)
        {
            return $this->render('QuickbooksBundle::oauth_connection.html.twig', [
                'isAuthenticated' => false,
                'callback_url'    => $callback_url
            ]);

        }

        /**
         * Unique object exists -> is token still valid -> no? -> remove the unique object -> go authentication
         */
        if (!$oauth_info->isTokenValid())
        {
            $em = $this->getDoctrine()->getManager();
            $em->remove($oauth_info);
            $em->flush();

            return $this->render('QuickbooksBundle::oauth_connection.html.twig', [
                'isAuthenticated' => false,
                'callback_url'    => $callback_url
            ]);
        }

        $url_disconnect = $base_url.$this->generateUrl('disconnect');

        return $this->render('QuickbooksBundle::oauth_connection.html.twig', [
            'isAuthenticated' => $oauth_info->isAuthenticated(),
            'oauth_token'     => $oauth_info->getAccessToken(),
            'oauth_secret'    => $oauth_info->getAccessTokenSecret(),
            'realm_id'        => $oauth_info->getCompanyId(),
            'callback_url'    => $callback_url,
            'url_disconnect'  => $url_disconnect
        ]);

    }



    /**
     * @Route("/oauth_callback", name="oauth_callback_url")
     */
    public function oAuthCallbackUrlAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /**
         * @var OAuthInfo $oauth_info
         */
        $oauth_info = $em->getRepository('QuickbooksBundle:OAuthInfo')->get();

        if ($oauth_info !== null && $oauth_info->isTokenValid())
        {
            return $this->redirect( $this->generateUrl('oauth_connection'));
        }

        try {
            $base_url = $request->getScheme() . '://' . $request->getHttpHost();
            $callback_url = $base_url.$this->generateUrl('oauth_callback_url');


            $oauth = new OAuth(
                $this->getParameter('quickbooks.oauth.consumer_key'),
                $this->getParameter('quickbooks.oauth.consumer_secret'),
                OAUTH_SIG_METHOD_HMACSHA1,
                OAUTH_AUTH_TYPE_URI
            );
            $oauth->enableDebug();
            $oauth->disableSSLChecks(); //To avoid the error: (Peer certificate cannot be authenticated with given CA certificates)

            $session = $request->getSession();

            $oauth_token = $request->query->get('oauth_token');
            /**
             * First callback from Quickbooks server containing the request token
             */

            $is_request_call_from_quickbooks = !$session->has('token') && $oauth_token === null;

            if ( $is_request_call_from_quickbooks )
            {
                // step 1: get request token from Quickbooks

                $request_token = $oauth->getRequestToken( OAuthController::OAUTH_REQUEST_URL, $callback_url );

                $session->set('secret', $request_token['oauth_token_secret']);

                return $this->redirect(OAuthController::OAUTH_AUTHORISE_URL .'?oauth_token='.$request_token['oauth_token']);
            }


            $oauth_verifier = $request->query->get('oauth_verifier');

            $is_request_authorization_from_quickbooks = $oauth_token !== null && $oauth_verifier !== null;

            if ( $is_request_authorization_from_quickbooks )
            {
                // step 3: request a access token from Intuit
                $secret = $session->get('secret');

                $oauth->setToken($oauth_token, $secret);
                $access_tokens = $oauth->getAccessToken( OAuthController::OAUTH_ACCESS_URL );

                $access_token = $access_tokens['oauth_token'];
                $access_token_secret = $access_tokens['oauth_token_secret'];

                $company_id = $request->get('realmId');
                $data_source = $request->get('dataSource');

                $new_oauth_info = new OAuthInfo($access_token, $access_token_secret, $company_id, $data_source);

                $em->persist($new_oauth_info);
                $em->flush();

                $session->remove('secret');
                $session->remove('token');

                // write JS to pup up to refresh parent and close popup
                echo '<script type="text/javascript">
                        window.opener.location.href = window.opener.location.href;
                        window.close();
                      </script>';
            }

            return $this->redirect( $this->generateUrl('oauth_connection'));

        } catch(OAuthException $e) {

            return new Response( new HttpException($e) );
        }
    }

    /**
     * @return bool
     */
    private function removeOAuthInfo()
    {
        $em = $this->getDoctrine()->getManager();

        $oauth_info = $em->getRepository('QuickbooksBundle:OAuthInfo')->get();

        if ($oauth_info !== null) {
            $em->remove($oauth_info);
            $em->flush();
            return true;
        }

        return false;
    }
}

