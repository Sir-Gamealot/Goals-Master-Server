<?php

define("API_KEY", "THE KEY");

function api_key_check($key) {
	return hash_equals($key, API_KEY);
}

?>