<?php
require_once('db.php');

$auth = new Auth($db);

//This example uses the NGU Elite validation process.
//What happens is, it checks against NGU's Elite auth to see if the key is valid.


if (isset($_GET['key']))
{
	$key = htmlspecialchars($_GET['key']);

	if (validate_elite_key($key))
	{
		//If the key is valid, it checks if we already stored it in our database (just to keep track of who uses the menu, plus banning).
		//Otherwise, it adds the key data to the database.
		if (!$auth->is_key_present($key))
				$auth->add_key_data($key, $_SERVER['REMOTE_ADDR']);

		//Next it does the ban/unban checking process (this could be run in a cron job, but if you have a fair number of people using your menu, this will work fine).
		$auth->ban_check($key);
		$auth->process_unbans();

		$user = $auth->fetch_key_data($key);

		//It then runs a check to see if they're banned. If not, log their action and output whatever data you want to send back.
		//If they are banned, it will output their ban message with a unban date. You can show this on your PS3 using a Dialog.
		if ($user['banned'] != 1 && $user['times_banned'] != 3)
		{
			//The log is used to catch key sharing. It only logs key logins from different IPs, and it will ban after 10 unique IPs in a short period of time.
			//You can setup a cron job to prune this log daily or bi-daily if you prefer.
			$auth->log($key, $_SERVER['REMOTE_ADDR']);
			die("Some useful information.");
		} else {
			die("You have been banned for: ".$user['ban_reason'] . "\nUnbanned on " . date("F d, Y", $user['unban_dateline']));
		}
	}
}
