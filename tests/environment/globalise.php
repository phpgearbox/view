<?php
////////////////////////////////////////////////////////////////////////////////
// __________ __             ________                   __________              
// \______   \  |__ ______  /  _____/  ____ _____ ______\______   \ _______  ___
//  |     ___/  |  \\____ \/   \  ____/ __ \\__  \\_  __ \    |  _//  _ \  \/  /
//  |    |   |   Y  \  |_> >    \_\  \  ___/ / __ \|  | \/    |   (  <_> >    < 
//  |____|   |___|  /   __/ \______  /\___  >____  /__|  |______  /\____/__/\_ \
//                \/|__|           \/     \/     \/             \/            \/
// -----------------------------------------------------------------------------
//          Designed and Developed by Brad Jones <brad @="bjc.id.au" />         
// -----------------------------------------------------------------------------
////////////////////////////////////////////////////////////////////////////////

namespace FooBar
{
	function test()
	{
		// Create a new view object.
		// Note how we are inside another namespace.
		$views = new \Gears\View('./views');

		// Globalise the views object
		$views->globalise();
	}
}

namespace
{
	// Load the composer autoloader
	require('../../vendor/autoload.php');

	// Call the FooBar\test function to create the session
	FooBar\test();

	// Note how we have access to the view api globally
	echo View::make('globalise')->withGlobal('example');
}