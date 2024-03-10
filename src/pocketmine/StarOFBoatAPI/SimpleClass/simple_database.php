<?php

namespace pocketmine\StarOFBoatAPI\SimpleClass;

use PDO;
use PDOException;
use SQLite3;

class simple_database extends SQLite3
{
    /** This is use PDO */
    public function simpleMySql($serverIP, $serverPort, $servername, $username, $password)
    {
        try {
            new PDO("mysql:host=$serverIP;port=$serverPort;dbname=$servername", $username, $password);
        } catch (PDOException $e) {
            return $e->getMessage();
        }
        return $this;
    }

    public function simpleMysqlClose($conn)
    {
        $conn->close();
        return $conn;
    }

    public function simpleSQLite($server)
    {
        $this->open($server);
        if (!$this) {
            return $this->lastErrorMsg();
        } else {
            return $this;
        }
    }

    public function simpleSQLiteClose($db)
    {
        $db->close();
        return $this;
    }
}