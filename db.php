<?php
//Menu server auth by Tustin
//January 2016
//NextGenUpdate
require('autoload.php');

$db = new PDO("mysql:host=" . $db_host . ";dbname=" . DATABASE_NAME, $db_user, $db_password);
//$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//function to check a elite key against NGU. does not require Auth object instantiation.
function validate_elite_key($key)
{
    if (file_get_contents("https://www.nextgenupdate.com/validator.php?KEYMD5=" . md5($key)) == "VALID")
        return true;
    
    return false;
}

class Auth
{
    private $db;
    
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    
    function fetch_key_data($key)
    {
        $query = $this->db->prepare("SELECT * FROM `users` WHERE `license` = :key");
        $query->bindParam(':key', $key);
        if($query->execute()){
            return $query->fetch();
        }else{
            //Error handling from the failure of the query should go here example 
            //trigger_error('Could not fetch key data ', E_USER_ERROR); or die('Could not fetch key data'); 
            //but i'd highly suggest exception handling I'll just deal with the errors using die, trigger & exception
        }
    }

    function is_key_present($key)
    {
        $query = $this->db->prepare("SELECT `license` FROM `users` WHERE `license` = :key");
        $query->bindParam(':key', $key);
        if($query->execute()){
            if ($query->rowCount() > 0){
                return true;
            }
        }
        return false;
    }

    function add_key_data($key, $ip)
    {
        $query = $this->db->prepare("INSERT INTO `users`(`license`, `ip`, `dateline`) VALUES (:key, :ip, UNIX_TIMESTAMP())");
        $query->bindParam(':key', $key);
        $query-.bindParam(':ip', $ip);
        if(!($query->execute()) === true){
            die('Could not add your key data to our database');
        }
    }
    
    function ban($key, $reason, $unban_dateline)
    {
        $query = $this->db->prepare("UPDATE `users` SET `ban_dateline` = UNIX_TIMESTAMP(), `banned` = 1, `ban_reason` = :message, `unban_dateline` = :udate, `times_banned` = times_banned + 1 WHERE `license` = :key");
        $query->bindParam(':message', $reason);
        $query->bindParam(':key', $unban_dateline);
        $query->bindParam(':key', $key);
        if(!($query->execute()) === true){
            trigger_error('Could not execute query ', E_USER_ERROR);
        }
    }
    
    function remove_from_log($key)
    {
        try{
            $query = $this->db->prepare("DELETE FROM `log` WHERE `license` = :key");
            $query->bindParam(':key', $key);
            $query->execute();
        }catch(Exception $e){
            echo 'Error: ' . $e->getMessage();
        }
    }

    function log($key, $ip)
    {
        $q = "SELECT * FROM `log` WHERE `license` = :key AND `ip` = :ip";
        $query = $this->db->prepare($q);
        $query->bindParam(':key', $key);
        $query->bindParam(':ip', $ip);
        if($query->execute()){
            if ($query->rowCount() == 0) {
                $q = "INSERT INTO `log`(`license`, `ip`, `dateline`) VALUES (:key, :ip, UNIX_TIMESTAMP())";
                $query = $this->db->prepare($q);
                $query->bindParam(':key', $key);
                $query->bindParam(':ip', $ip);
                $query->execute();
            }
        }else{
            trigger_error('Could not execute query', E_USER_ERROR); //it's fatal lol but ey handle the errors which best suites you.
        }
    }

    function ban_check($key)
    {
        $query = $this->db->prepare("SELECT l.license, u.times_banned FROM `log` AS l
        LEFT JOIN `users` AS u ON
        l.license = u.license
        AND l.license = :key WHERE u.banned <> 1 HAVING COUNT(l.license) >= 10");
        $query-.bindParam(':key', $key);
        if($query->execute()){
            if ($query->rowCount() > 0) {
                $data = $query->fetch();
                switch ($data['times_banned']) {
                    case 0:
                        //24 hr ban
                        $this->ban($key, "Autoban for sharing key.", strtotime("+1 day"));
                        $this->remove_from_log($key);
                        break;

                    case 1:
                        //1 week ban
                        $this->ban($key, "Autoban for sharing key.", strtotime("+1 week"));
                        $this->remove_from_log($key);
                        break;

                    case 2:
                        //perm ban
                        $this->ban($key, "Autoban for sharing key.", strtotime("2037-12-31"));
                        $this->remove_from_log($key);
                        break;
                }
            }
        }
    }
    
    function process_unbans()
    {
        $query = $this->db->prepare("SELECT `license` FROM `users` WHERE `unban_dateline` < NOW() AND `unban_dateline` <> 0 AND `banned` = 1");
        if($query->execute()){
            foreach ($query->fetchAll() as $unban) {
                $query = $this->db->prepare("UPDATE `users` SET `banned` = 0, `unban_dateline` = 0, `ban_reason` = NULL WHERE `license` = :key");
                $query->bindParam(':key', $unban['license']);
                $query->execute();
            }
        }else{
            trigger_error('could not execute query ', E_USER_ERROR); // Handle your errors accordingly lol not actually like this.
        }
    }
}
