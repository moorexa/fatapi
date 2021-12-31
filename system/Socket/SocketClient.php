<?php
namespace Lightroom\Socket;

/**
 * Class SocketClient
 * develope by psinetron (slybeaver), modified by Amadi Ifeanyi <amadiify.com>
 * Git: https://github.com/psinetron
 * web-site: http://slybeaver.ru
 *
 */
class SocketClient
{
    /**
     * @var string $host
     */
    private $host;

    /**
     * @var string $port
     */
    private $port;

    /**
     * @var string $address
     */
    private $address;

    /**
     * @var string $transport
     */
    private $transport;

    /**
     * @var bool $handshaked
     */
    private $handshaked = false;

    /**
     * @var array $cachedQueues
     */
    private array $cachedQueues = [];

    /**
     * @method SocketClient __construct
     * @param null $host - $host of socket server
     * @param null $port - port of socket server
     * @param string $address - addres of socket.io on socket server
     * @param string $transport - transport type
     * @return bool
     */
    public function __construct($host = null, $port = null, $address = "/socket.io/?EIO=2", $transport = 'websocket')
    {
        $this->host = $host;
        $this->port = $port;
        $this->address = $address;
        $this->transport = $transport;
    }

    /**
     * @method SocketClient connect
     * @return array
     */
    public function connect() : SocketClient
    {
        $fd = fsockopen($this->host, $this->port, $errno, $errstr);
        if (!$fd) {
            return false;
        } //Can't connect tot server
        $key = $this->generateKey();
        $out = "GET {$this->address}&transport={$this->transport} HTTP/1.1\r\n";
        $out.= "Host: http://{$this->host}:{$this->port}\r\n";
        $out.= "Upgrade: WebSocket\r\n";
        $out.= "Connection: Upgrade\r\n";
        $out.= "Sec-WebSocket-Key: $key\r\n";
        $out.= "Sec-WebSocket-Version: 13\r\n";
        $out.= "Origin: *\r\n\r\n";

        fwrite($fd, $out);

        // 101 switching protocols, see if echoes key
        $result= fread($fd,10000);

        preg_match('#Sec-WebSocket-Accept:\s(.*)$#mU', $result, $matches);
        $keyAccept = trim($matches[1]);
        $expectedResonse = base64_encode(pack('H*', sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
        $this->handshaked = ($keyAccept === $expectedResonse) ? true : false;

        // set data
        $this->fd = $fd;

        // return class
        return $this;
    }

    /**
     * @method SocketClient queue
     * @param string $action - action to execute in sockt server
     * @param string $data - message to socket server
     * @return SocketClient
     */
    public function queue(string $action, string $data) : SocketClient
    {
        $this->cachedQueues[$action] = $data;

        // return SocketClient
        return $this;
    }

    /**
     * @method SocketClient send
     * @param string $action - action to execute in sockt server
     * @param null $data - message to socket server
     * @return bool
     */
    public function send($action= null,  $data = null) : bool
    {
        // create connection
        $this->connect();

        // all good ?
        if ($this->handshaked){

            // send bulk
            if ($action == null && $data == null && count($this->cachedQueues) > 0) :

                // run loop
                foreach ($this->cachedQueues as $action => $data) :

                    // write data
                    fwrite($this->fd, $this->hybi10Encode('42["' . $action . '", "' . addslashes($data) . '"]')); 

                endforeach;

            else:

                // write data
                fwrite($this->fd, $this->hybi10Encode('42["' . $action . '", "' . addslashes($data) . '"]'));    
            
            endif;

            //fread($this->fd,1000000);
            return true;

        } else {return false;}
    }

    /**
     * @method SocketClient generateKey
     * @param int $length
     * @return string
     */
    private function generateKey($length = 16) : string
    {
        $c = 0;
        $tmp = '';
        while ($c++ * 16 < $length) { $tmp .= md5(mt_rand(), true); }
        return base64_encode(substr($tmp, 0, $length));
    }

    /**
     * @method SocketClient hybi10Encode
     * @param string $payload
     * @param string $type
     * @param bool $masked
     * @return mixed
     */
    private function hybi10Encode($payload, $type = 'text', $masked = true)
    {
        $frameHead = array();
        $payloadLength = strlen($payload);
        switch ($type) {
            case 'text':
                $frameHead[0] = 129;
                break;
            case 'close':
                $frameHead[0] = 136;
                break;
            case 'ping':
                $frameHead[0] = 137;
                break;
            case 'pong':
                $frameHead[0] = 138;
                break;
        }
        if ($payloadLength > 65535) {
            $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 255 : 127;
            for ($i = 0; $i < 8; $i++) {
                $frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
            }
            if ($frameHead[2] > 127) {
                $this->close(1004);
                return false;
            }
        } elseif ($payloadLength > 125) {
            $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 254 : 126;
            $frameHead[2] = bindec($payloadLengthBin[0]);
            $frameHead[3] = bindec($payloadLengthBin[1]);
        } else {
            $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
        }
        foreach (array_keys($frameHead) as $i) {
            $frameHead[$i] = chr($frameHead[$i]);
        }
        if ($masked === true) {
            $mask = array();
            for ($i = 0; $i < 4; $i++) {
                $mask[$i] = chr(rand(0, 255));
            }
            $frameHead = array_merge($frameHead, $mask);
        }
        $frame = implode('', $frameHead);
        for ($i = 0; $i < $payloadLength; $i++) {
            $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
        }
        return $frame;
    }
}
