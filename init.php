<?php

$init = function($bootstrap) {

	$base = Core_Model_Directory::getBasePathTo("/app/local/modules/WalletRbkPS");
	# Register assets
	Nwicode_Assets::registerAssets("WalletRbkPS", "/app/local/modules/WalletRbkPS/resources/var/apps/");
	Nwicode_Assets::addJavascripts(array(
		"modules/walletrbkps/controllers/walletrbkps.js",
		"modules/walletrbkps/factories/walletrbkps.js",
	));	

};