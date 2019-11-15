<?php

/*TODO
- Historico
- Error Handling
- UDP
- Grafismo 
*/
error_reporting(E_ALL);
set_time_limit(0);

function textoEmoji($data)
{
    global $data;
    $emojis = array(":smile:"=>":-)", ":sad:"=>":-(", ":lenny:"=>"( ͡° ͜ʖ ͡°)");
    $words = preg_split("/[\s,]+/", $data);

    foreach ($emojis as $key => $value)
    {
        if(in_array($key, $words))
        {
            $data = str_replace($key, $value, $data);
        }
    }
}

$_colors = array(
        'LIGHT_RED'      => "[1;31m",
        'LIGHT_GREEN'     => "[1;32m",
        'YELLOW'     => "[1;33m",
        'LIGHT_BLUE'     => "[1;34m",
        'MAGENTA'     => "[1;35m",
        'LIGHT_CYAN'     => "[1;36m",
        'WHITE'     => "[1;37m",
        'NORMAL'     => "[0m",
        'BLACK'     => "[0;30m",
        'RED'         => "[0;31m",
        'GREEN'     => "[0;32m",
        'BROWN'     => "[0;33m",
        'BLUE'         => "[0;34m",
        'CYAN'         => "[0;36m",
        'BOLD'         => "[1m",
        'UNDERSCORE'     => "[4m",
        'REVERSE'     => "[7m",

);

function textcolored($text, $color="NORMAL", $back=1){
    global $_colors;
    $out = $_colors["$color"];
    if($out == ""){ $out = "[0m"; }
    if($back){
        return chr(27)."$out$text".chr(27).chr(27)."[0m";
    }else{
        echo chr(27)."$out$text".chr(27).chr(27)."[0m";
    }
}

$ip = "127.0.0.1";
$port = 44000;
//$protocolo = readline("Insira o protocolo (TCP/UDP): ");
$protocolo = "tcp";


$talkback = array();
$hora = date('H:i:s');

try {
    $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

    socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1);

    $result = socket_bind($sock, $ip, $port);

    $result = socket_listen($sock, 10);
}
catch (ErrorException $ex) {
    echo "Ocorreu um erro na criação do socket";
}

$clientes = array($sock);

echo "\e[H\e[J";
// echo $text = "╔" . str_repeat("=", 50) . "╗\n"; //TOPO
// array_push($talkback, $text);
while(true) {
    $read = $clientes;
    $write = array();
    $except = array();
    if(socket_select($read, $write, $except, 0) < 1)
        continue;

    if(in_array($sock, $read))
    {
        $clientes[] = $newsock = socket_accept($sock);

        socket_write($newsock, "Bem-vindo à sala de chat! \nHá ". (count($clientes)-1)." cliente(s) conectados ao servidor\n"); //Mensagem de boas vindas enviado quando o cliente conectar

        socket_getpeername($newsock, $ip); //ip do cliente
        echo $text = textcolored("Novo cliente conectado: {$ip}\n", "LIGHT_BLUE");
        array_push($talkback, $text);
        $key = array_search($sock, $read);
        unset($read[$key]);
    }

    foreach ($read as $read_sock) {
        $data = @socket_read($read_sock, 1024, PHP_BINARY_READ);

        if($data === false or $data == "/quit")
        {
            $key = array_search($read_sock, $clientes);
            unset($clientes[$key]);
            echo $text = textcolored("Cliente {$ip} desconectado.\n", "RED");
            array_push($talkback, $text);
            continue;
        }

        $data = trim($data);

        if(!empty($data))
        {
            textoEmoji($data); //funcao emoji
            
            $text = "{$hora} | {$ip}: ".$data."\n";

            array_push($talkback, $text);

            echo "\e[H\e[J";

            for ($i=0; $i < count($talkback) ; $i++) { 
                echo ($talkback[$i]);
            }

            $json = json_encode($talkback);
            socket_write($read_sock, $json);
        }
    }
}

socket_close($sock);
?>