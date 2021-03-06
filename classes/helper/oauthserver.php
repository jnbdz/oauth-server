<?php

class Helper_Oauthserver {

/**
 * OAuthServer Helper class.
 *
 * @author
 * - Jean-Nicolas Boulay Desjardins (http://jean-nicolas.name/)
 * @package default
 * @version 0.1 Alpha
 */

	public static function new_consumer_key() {

		$fp = fopen('/dev/urandom','rb');
                $entropy = fread($fp, 32);
                fclose($fp);
                // in case /dev/urandom is reusing entropy from its pool, let's add a bit more entropy
                $entropy .= uniqid(mt_rand(), true);
                $hash = sha1($entropy); //hash('sha256', $entropy);  // sha1 gives us a 40-byte hash
                // The first 30 bytes should be plenty for the consumer_key
                // We use the last 10 for the shared secret
                return array(substr($hash,0,30),substr($hash,30,10));

	}

}
