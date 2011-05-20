<?php

class Model_Token extends Model {

	public function create() {

		DB::insert('oauth_server_token', array('token', 'token_secret'))->values(array());

	}

} // End of Token
