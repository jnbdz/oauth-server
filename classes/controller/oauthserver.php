<?php
class Controller_Oauth extends Controller_App {

/**
 * App controller class.
 *
 * @author
 * - Mikito Takada
 * - Jean-Nicolas Boulay Desjardins (http://jean-nicolas.name/)
 * @package default
 * @version 0.1 Alpha
 */

   /**
    * Models
    * - Consumer (for keeping track of sites which have access to your oAuth provider)
    * - Token (request token (type 0) or an access token, (type 1))
    *
    */

   public function before() {
      // Execute parent::before first
      parent::before();
      // now initialize the oAuth provider here for use in the rest of this controller...
      try {
         $this->provider = new OAuthProvider();
         $this->provider->consumerHandler(array($this,'lookupConsumer'));
         $this->provider->timestampNonceHandler(array($this,'timestampNonceChecker'));
         $this->provider->tokenHandler(array($this,'tokenHandler'));
         $this->provider->setParam('kohana_uri', NULL);  // Ignore the kohana_uri parameter
         $this->provider->setRequestTokenPath('/oauth/request_token');  // No token needed for this end point -- e.g. should be the same as calling isRequestTokenEndpoint(true) for this URL (?)
         $this->provider->checkOAuthRequest();
      } catch (OAuthException $E) {
         echo OAuthProvider::reportProblem($E);
         $this->oauth_error = true;
      }
   }

   /**
    * Helper for oAuth provider.
    * Lookup the user (a.k.a Oauth consumer) from the database...
    *
    * @param OAuthProvider $provider
    * @return int
    */
   public function lookupConsumer($provider) {
      $consumer = ORM::factory('oauth_server_consumer')->where('consumer_key', '=', $provider->consumer_key);
      if($provider->consumer_key != $consumer->consumer_key) {
         return OAUTH_CONSUMER_KEY_UNKNOWN;
      } else if($consumer->key_status != 0) {
		return OAUTH_CONSUMER_KEY_REFUSED;
	}
      $provider->consumer_secret = $consumer->consumer_secret;
      return OAUTH_OK;
   }

   /**
    * Helper for oAuth provider.
    *
    * Check whether the timestamp of the request is sane and falls within the window of our Nonce checks.
    *
    * @param OAuthProvider $provider
    * @return int
    */
   public function timestampNonceChecker($provider) {
      if($provider->nonce=="bad") {
         return OAUTH_BAD_NONCE;
      } else if($provider->timestamp=="0") {
         return OAUTH_BAD_TIMESTAMP;
      }
      return OAUTH_OK;
   }

   /**
    * Helper for oAuth provider.
    *
    * @param OAuthProvider $provider
    * @return int
    */
   public function tokenHandler($provider) {
      if($provider->token=="rejected") {
         return OAUTH_TOKEN_REJECTED;
      } else if($provider->token=="revoked") {
         return OAUTH_TOKEN_REVOKED;
      }
      $provider->token_secret = "the_tokens_secret";
      return OAUTH_OK;
	}

   

   /**
    * The URL used to obtain an unauthorized Request Token, described in Section 6.1 of http://oauth.net/core/1.0a/.
    *
    * Request Token:
    * Used by the Consumer to ask the User to authorize access to the Protected Resources.
    * The User-authorized Request Token is exchanged for an Access Token, MUST only be used once, and MUST NOT be used for any other purpose.
    * It is RECOMMENDED that Request Tokens have a limited lifetime.
    */
   public function action_request_token() {
      $token = Token_Model::create($this->provider->consumer_key);
      $token->save();
      // Build response with the authorization URL users should be sent to
      echo 'login_url=https://'.Kohana::config('oauth-server.site_domain').
           '/oauth/authorize&oauth_token='.$token->tok.
           '&oauth_token_secret='.$token->secret.
           '&oauth_callback_confirmed=true';
   }

   /**
    * The URL used to obtain User authorization for Consumer access, described in Section 6.2.
    *
    * The Service Provider MUST first verify the User's identity before asking for consent. It MAY prompt the User to sign in if the User has not already done so.
    * The Service Provider presents to the User information about the Consumer requesting access (as registered by the Consumer Developer). The information includes the duration of the access and the Protected Resources provided. The information MAY include other details specific to the Service Provider.
    * The User MUST grant or deny permission for the Service Provider to give the Consumer access to the Protected Resources on behalf of the User. If the User denies the Consumer access, the Service Provider MUST NOT allow access to the Protected Resources.
    *
    * "Back to your regular web UI, you now need to add a landing page for authorizing request tokens.
    * Make sure the language on the page makes it clear to the user that they are authorizing a 3rd-party application to act on their behalf.
    * It is a good idea to make them re-enter their password and explicitly click a button to do this authorization. "
    */
   public function action_authorize() {
      $this->template->content = 'Ask user to authorize.... <form method="POST" action="/oauth/authorize"><input type="hidden" name="id" value="id-of-the-authorization request"><input type="radio"></form>';
   }

   /**
    * The URL used to exchange the User-authorized Request Token for an Access Token, described in Section 6.3.
    *
    * Access Token:
    * Used by the Consumer to access the Protected Resources on behalf of the User.
    * Access Tokens MAY limit access to certain Protected Resources, and MAY have a limited lifetime.
    * Service Providers SHOULD allow Users to revoke Access Tokens.
    * Only the Access Token SHALL be used to access the Protect Resources.
    *
    */
   public function action_access_token() {
      $access_token = Token_Model::create($this->provider->consumer_key, 1);
      $access_token->save();
      $this->token->state = 2;  // The request token is marked as 'used'
      $this->token->save();
      // Now we need to find the user who authorized this request token
      $utoken = ORM::factory('utoken', $this->token->tok);
      if(!$utoken->loaded) {
         echo "oauth error - token rejected";
         break;
      }
      // And swap out the authorized request token for the access token
      $new_utoken = Utoken_Model::create(
                            array('token'            => $access_token->tok,
                                     'user_id'         => $utoken->user_id,
                                     'application_id'=> $utoken->application_id,
                                     'access_type'   => $utoken->access_type));
      $new_utoken->save();
      $utoken->delete();
      echo "oauth_token={$access_token->tok}&oauth_token_secret={$access_token->secret}";
   }

}
