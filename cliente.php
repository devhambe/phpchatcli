<?php
error_reporting(E_ALL);

function limparTela() {
    echo "\e[H\e[J";
}

function inputBottom() {
    //echo "\e[10;0H";
    echo "\e[10B";
}

limparTela();

$ip = "127.0.0.1";
$port = 44000;
//$ip = readline("Insira o IP: ");
//$port = readline("Insira a porta: ");
$protocolo = readline("Insira o protocolo (TCP/UDP): ");

if(strtolower($protocolo) == "tcp") {
    $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("Não foi possível criar socket\n");
    echo "A ligar ao servidor '$ip' na porta '$port'...\n";

    $result = socket_connect($sock, $ip, $port) or die("Não foi possível ligar ao socket\n");
    echo "Ligação estabelecida com sucesso\n";

    limparTela();
    
    $output = socket_read($sock, 8192);
    echo "$output \n";
    //sleep
    limparTela();
    while(true){
        inputBottom();
        $input = trim(readline(": "));
        limparTela();
        if($input == "/quit") {
            echo "A terminar sessão...\n";
            socket_write($sock, $input, strlen($input));
            socket_shutdown($sock, 2);
            socket_close($sock);
            echo "Sessão terminada com sucesso.\n\n";
            break;
        }
        if ($input == "") {
            $input = " "; //ALT + 0160
        }
        socket_write($sock, $input, strlen($input));

        $output = socket_read($sock, 8192);
        $output = json_decode($output);
        for ($i=0; $i < count($output) ; $i++) { 
            echo $output[$i];
        }
        
    }
    
} else if (strtolower($protocolo) == "udp") {
    $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    while(true) {
        $input = readline(": ");
        limparTela();
        socket_sendto($sock, $input, strlen($input), 0, $ip, $port);
        socket_recv($sock, $output, 2048, 0);
        echo($output);
    }
    socket_close($sock);
}
?>
