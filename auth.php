<?php
require_once('db.php');

$auth = new Auth($db);

//This example uses your own key verification.
//This assumes all your user's keys are inside the 'users' table.

if (isset($_GET['key']))
{
    $key = htmlspecialchars($_GET['key']);

    //Checks if our user exists in the 'users' table.
    if ($auth->is_key_present($key))
    {
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

            //If you're using an app other than SPRX, you could use json_encode to create a JSON object for your C# app to parse. Otherwise you can return info delimited by a character (like ':') to be parsed by your SPRX (because good luck finding something to parse JSON for C++ that works on PS3).

            //Example of JSON:
            //$data = ['code' => 200, 'data' => ['0x1337'] ]; 
            //die(json_encode($data));

            //Example of SPRX:
            //$data = ['0x1337', '0x6969'];
            //die(implode(':', $data));

            die("Some useful information.");
        } else {
            //SPRX parses this message fine in a PS3 dialog.
            die("You have been banned for: ".$user['ban_reason'] . "\nUnbanned on " . date("F d, Y", $user['unban_dateline']));
        }
    }
}
