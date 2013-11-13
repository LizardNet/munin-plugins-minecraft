<?php
/*
GITHUB IVKOS/MINECRAFT-QUERY-FOR-PHP <https://github.com/ivkos/Minecraft-Query-for-PHP>

This class was originally written by xPaw. Modifications and additions by ivkos.

This work is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 3.0 Unported License.
To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-sa/3.0/
*/

class minecraftRconException extends Exception
{
    // Exception thrown by MinecraftRcon class
}

class minecraftRcon
{
    /*
     * Originally written by xPaw
     * Modifications and additions by ivkos
     *
     * GitHub: https://github.com/ivkos/Minecraft-Query-for-PHP
     * Protocol: https://developer.valvesoftware.com/wiki/Source_RCON_Protocol
     */

    // Sending
    const SERVERDATA_EXECCOMMAND = 2;
    const SERVERDATA_AUTH = 3;

    // Receiving
    const SERVERDATA_RESPONSE_VALUE = 0;
    const SERVERDATA_AUTH_RESPONSE = 2;

    private $socket;
    private $requestID;

    public function __destruct()
    {
        $this->disconnect();
    }

    public function connect($IP, $port = 25575, $password, $timeout = 3)
    {
        $this->requestID = 0;

        if ($this->socket = fsockopen($IP, (int) $port)) {
            socket_set_timeout($this->socket, $timeout);

            if (!$this->auth($password)) {
                $this->disconnect();

                throw new minecraftRconException("Authorization failed.");
            }
        } else {
            throw new MinecraftRconException("Can't open socket.");
        }
    }

    public function disconnect()
    {
        if ($this->socket) {
            fclose($this->socket);

            $this->socket = null;
        }
    }

    public function command($String)
    {
        if (!$this->writeData(self::SERVERDATA_EXECCOMMAND, $String)) {
            return false;
        }

        $data = $this->readData();

        if ($data['RequestId'] < 1 || $data['Response'] != self::SERVERDATA_RESPONSE_VALUE) {
            return false;
        }

        return $data['String'];
    }

    private function auth($password)
    {
        if (!$this->writeData(self::SERVERDATA_AUTH, $password)) {
            return false;
        }

        $data = $this->readData();

        return $data['RequestId'] > -1 && $data['Response'] == self::SERVERDATA_AUTH_RESPONSE;
    }

    private function readData()
    {
        $packet = array();

        $size = fread($this->socket, 4);
        $size = unpack('V1Size', $size);
        $size = $size['Size'];

        // TODO: Add multiple packets (Source)

        $packet = fread($this->socket, $size);
        $packet = unpack('V1RequestId/V1Response/a*String/a*String2', $packet);

        return $packet;
    }

    private function writeData($command, $string = "")
    {
        // Pack the packet together
        $data = pack('VV', $this->requestID++, $command) . $string . "\x00\x00\x00";

        // Prepend packet length
        $data = pack('V', strlen($data)) . $data;

        $length = strlen($data);

        return $length === fwrite($this->socket, $data, $length);
    }
}
