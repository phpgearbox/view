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

class RoboFile extends Robo\Tasks
{
	/**
	 * Property: serverPort
	 * =========================================================================
	 * The port the built in PHP server will run on for our acceptance testing.
	 */
	public static $serverPort = 8000;

	/**
	 * Method: test
	 * =========================================================================
	 * This will run our unit / acceptance testing. All the *gears* within
	 * the **PhpGearBox** utlise PhpUnit as the basis for our testing with the
	 * addition of the built in PHP Web Server, making the acceptance tests
	 * almost as portable as standard unit tests.
	 *
	 * Just run: ```php ./vendor/bin/robo test```
	 *
	 * Parameters:
	 * -------------------------------------------------------------------------
	 * n/a
	 *
	 * Returns:
	 * -------------------------------------------------------------------------
	 * void
	 */
	public function test()
	{
		$this->taskServer(self::$serverPort)
			->dir('./tests/environment')
			->background(true)
		->run();

		exit($this->taskPHPUnit()->bootstrap('./tests/bootstrap.php')->arg('./tests')->run()->getExitCode());
	}
}