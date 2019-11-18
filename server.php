<?php

/*TODO
- Historico
- Error Handling
- UDP ✓
- Grafismo ✓
- Username
- Comentários
*/
error_reporting(E_ALL);
set_time_limit(0);

function limparTela() {
    echo "\e[H\e[J";
}

//função para a escolha do protocolo
function protocolo() {
    echo("Escolha um protocolo:");
    echo("\nTCP     - 1");
    echo("\nUDP     - 2");
    echo("\nSair    - 3\n");
    $opcao = readline(": ");
    return($opcao);
}

//Função para transformar determinadas palavras em emojis
function textoEmoji($data)
{
    global $data;
    $emojis = array(
        ":smile:"   =>  ":-)",
        ":sad:"     =>  ":-(",
        ":lenny:"   =>  "( ͡° ͜ʖ ͡°)",
        ":happy:"   =>  "^_^",
        ":tableflip:"   =>  "(╯°□°）╯︵ ┻━┻",
        );
    $words = preg_split("/[\s,]+/", $data);

    foreach ($emojis as $key => $value)
    {
        if(in_array($key, $words))
        {
            $data = str_replace($key, $value, $data);
        }
    }
}

//array de cores para a próxima função
$_cores = array(
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

//função para atribuir uma cor ao texto
function textoCor($texto, $cor="NORMAL", $back=1){
    global $_cores;
    $out = $_cores["$cor"];
    if($out == ""){ $out = "[0m"; }
    if($back){
        return chr(27)."$out$texto".chr(27).chr(27)."[0m";
    }else{
        echo chr(27)."$out$texto".chr(27).chr(27)."[0m";
    }
}

$ip = "127.0.0.1";
$port = 44000;
$protocolo = protocolo();

//array de todas as mensagens
$talkback = array();

//============================ TCP ============================
if($protocolo == 1) {

    //criação do socket
    $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if(!$sock)
        die("Não foi possível criar o socket");

    //bind do socket
    if(!socket_bind($sock, $ip, $port))
        die("Não foi possível fazer o bind do socket");

    //socket_listen com um backlog de 10 conexões
    if(!socket_listen($sock, 10))
        die("Não foi possível pôr o socket à escuta");

    //array de todos os clientes que se vão conectar ao socket
    $clientes = array($sock);

    limparTela();
    echo "
     ___              _    _           _____ ___ ___ 
    / __| ___ _ ___ _(_)__| |___ _ _  |_   _/ __| _ \
    \__ \/ -_) '_\ V / / _` / _ \ '_|   | || (__|  _/
    |___/\___|_|  \_/|_\__,_\___/_|     |_| \___|_|  
                                                     
    ";
    // echo $text = "╔" . str_repeat("=", 50) . "╗\n"; //TOPO
    // array_push($talkback, $text);
    while(true) {
        $read = $clientes;
        $write = array();
        $except = array();
        if(socket_select($read, $write, $except, 0) < 1)
            continue;

        //verifica se há um cliente a estabelecer conexão
        if(in_array($sock, $read))
        {
            //aceita o cliente e adiciona-o ao array $clientes
            $clientes[] = $newsock = socket_accept($sock);

            //mensagem para o cliente quando entra
            socket_write($newsock, "Bem-vindo à sala de chat! \nHá ". (count($clientes)-1)." cliente(s) conectados ao servidor\n");

            //mensagem de entrada do cliente
            socket_getpeername($newsock, $ip);
            limparTela();
            echo $text = textoCor("Novo cliente conectado: {$ip}\n", "LIGHT_BLUE");

            array_push($talkback, $text);
            
            $key = array_search($sock, $read);
            unset($read[$key]);
        }

        foreach ($read as $read_sock) {
            $data = @socket_read($read_sock, 1024);

            if($data === false or $data == "/quit")
            {
                $key = array_search($read_sock, $clientes);
                unset($clientes[$key]);
                echo $text = textoCor("Cliente {$ip} desconectado.\n", "RED");
                array_push($talkback, $text);
                continue;
            }

            //Eliminação dos espaços em branco (trim)
            $data = trim($data); 

            if(!empty($data))
            {
                //Função Texto -> Emoji
                textoEmoji($data);
                $hora = date('H:i:s');
                $text = "<$hora> | {$ip}: $data\n";

                array_push($talkback, $text);

                limparTela();

                for ($i=0; $i < count($talkback) ; $i++) { 
                    echo ($talkback[$i]);
                }

                $json = json_encode($talkback);
                socket_write($read_sock, $json);
            }
        }
    }
    socket_close($sock);

} //============================ UDP ============================
else if ($protocolo == 2) {

    //Criação do socket
    $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    if(!$sock)
        die("Não foi possível criar o socket");

    //Bind do socket
    if(!socket_bind($sock, $ip, $port))
        die("Não foi possível fazer bind do socket");
    
    limparTela();
    echo "
     ___              _    _           _   _ ___  ___ 
    / __| ___ _ ___ _(_)__| |___ _ _  | | | |   \| _ \
    \__ \/ -_) '_\ V / / _` / _ \ '_| | |_| | |) |  _/
    |___/\___|_|  \_/|_\__,_\___/_|    \___/|___/|_|  
                                                      
    ";

    //Loop de mensagens
    while(true) {
        $hora = date('H:i:s');
        socket_recvfrom($sock, $data, 1024, 0, $ip_cliente, $porta_cliente);
        limparTela();
        $text = "<$hora> | {$ip_cliente}: $data\n";
        array_push($talkback, $text);
        for ($i=0; $i < count($talkback) ; $i++) { 
            echo ($talkback[$i]);
        }
        $json = json_encode($talkback);
        socket_sendto($sock, $json, strlen($json), 0, $ip_cliente, $porta_cliente);
    }
    socket_close($sock);
} else if ($protocolo == 3) {
    
} else {

}
?>