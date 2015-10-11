<?php

class WP_Tests extends PHPUnit_Extensions_Selenium2TestCase
{
	protected function setUp()
	{
		global $argv, $argc;

		$host = 'clean.wp';
		$browser = 'firefox';

		if( $argc == 3 )
		{
			$host = $argv[ 2 ];
			$browser = $argv[ 3 ];
		}

		$this->setBrowser( "firefox" );
		$this->setBrowserUrl( "http://" . $host );
	}

	public function login()
	{
		$this->go_wp_admin();

		$this->byId( 'user_login' )->value( 'wagesve' );
		$this->byId( 'user_pass' )->value( 'tv7r66' );
		$this->byId( 'wp-submit' )->submit();
	}

	public function go_wp_admin()
	{
		$this->url( "/wp-admin/" );
	}
}