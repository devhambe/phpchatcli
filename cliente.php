<?php
error_reporting(E_ALL);

function limparTela() {
    echo "\e[H\e[J";
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

//função para a escolha do protocolo
function protocolo() {
    echo("Escolha um protocolo:");
    echo("\nTCP     - 1");
    echo("\nUDP     - 2");
    echo("\nSair    - 3\n");
    $opcao = readline(": ");
    return($opcao);
}

limparTela();

$ip = "127.0.0.1";
$port = 44000;
//$ip = readline("Insira o IP: ");
//$port = readline("Insira a porta: ");
$protocolo = protocolo();

start:
//============================ TCP ============================
if($protocolo == 1) {
    $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("Não foi possível criar socket\n");
    echo "A ligar ao servidor '$ip' na porta '$port'...\n";

    $result = socket_connect($sock, $ip, $port) or die("Não foi possível ligar ao socket\n");
    echo "Ligação estabelecida com sucesso\n";

    limparTela();
    
    $output = socket_read($sock, 8192);
    echo "$output \n";

    limparTela();
    while(true){
        $input = trim(readline(": "));
        limparTela();
        if($input == "/quit") {
            socket_write($sock, $input, strlen($input));
            echo textoCor("A terminar sessão...\n", "RED");
            socket_shutdown($sock, 2);
            socket_close($sock);
            echo textoCor("Sessão terminada com sucesso.\n\n", "UNDERSCORE");
            break;
        }
        if ($input == "") {
            $input = " "; //ALT + 0160
        }
        socket_write($sock, $input, strlen($input));

        $output = socket_read($sock, 8192);
        $output = json_decode($output, true);
        for ($i=0; $i < count($output) ; $i++) { 
            echo $output[$i];
        }
        
    }
    
} //============================ UDP ============================
else if ($protocolo == 2) {
    $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    while(true) {
        $input = readline(": ");
        limparTela();
        if($input == "/quit") {
            echo textoCor("A terminar sessão...\n", "RED");
            socket_shutdown($sock, 2);
            socket_close($sock);
            echo textoCor("Sessão terminada com sucesso.\n\n", "UNDERSCORE");
            break;
        }
        socket_sendto($sock, $input, strlen($input), 0, $ip, $port);
        socket_recv($sock, $output, 2048, 0);
        $output = json_decode($output, true);
        for ($i=0; $i < count($output) ; $i++) { 
            echo $output[$i];
        }
    }
} else if ($protocolo == 3) {

} else {
    limparTela();
    $protocolo = protocolo();
    goto start;
}
?>