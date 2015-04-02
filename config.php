<?php
// DEBUG
$config['debug'] = true;						// Disable this in production

// MySQL
$config['mysql']['host'] = 'mysqlhost';			// Hôte MySQL
$config['mysql']['port'] = 3306;				// Port
$config['mysql']['user'] = 'mysqlusername';				// Utilisateur MySQL
$config['mysql']['pass'] = 'mysqlpassword';		// Mot de passe
$config['mysql']['base'] = 'mysqldatabase';  	// Base de donnée

// MongoDB
$config['mongodb']['base'] = 'cesi_alternance';

// Autenticate
$config['auth']['token_name'] = 'X-APP-TOKEN';
$config['auth']['mysql_table'] = 'user';
$config['auth']['mysql_user'] = 'email';
$config['auth']['mysql_pass'] = 'password';
$config['auth']['mysql_token'] = 'token';
$config['auth']['mysql_expire'] = 'expire';

// Exclude MySQL Tables/Fields 					# NOT IMPLEMENTED AT THIS TIME #
$config['excludes']['note'] = true;
$config['excludes']['user']['password'] = true;
