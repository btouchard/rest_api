<?php
// DEBUG
$config['debug'] = true;						// Disable this in production

// MySQL
$config['mysql']['host'] = 'localhost';			// Hôte MySQL
$config['mysql']['port'] = 3306;				// Port
$config['mysql']['user'] = 'root';				// Utilisateur MySQL
$config['mysql']['pass'] = 'lapsikopass';		// Mot de passe
$config['mysql']['base'] = 'cesi_alternance';  	// Base de donnée

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