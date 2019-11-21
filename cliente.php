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

function printHistorico($historico) {
    for ($i=0; $i < count($historico); $i++) { 
        if($historico[$i] != "\n") {
            echo $historico[$i];
        }
    }
}

function printArray($array){
    for ($i=0; $i < count($array) ; $i++) { 
        echo ($array[$i]);
    }
}

limparTela();

$ip = trim(readline("Insira o IP: "));
$port = trim(readline("Insira a porta: "));
$protocolo = protocolo();

start:
//============================ TCP ============================
if($protocolo == 1) {
    //criação do socket
    $sock = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if(!$sock)
        die("Não foi possível criar o socket");
    
    //ligação do socket
    if(!@socket_connect($sock, $ip, $port))
        die("Não foi possível ligar ao socket");

    limparTela();
    
    //lê a mensagem de boas vindas
    $output = socket_read($sock, 8192);
    echo textoCor("$output \n", "LIGHT_BLUE");

    //limparTela();
    while(true){
        $input = trim(readline(": "));
        limparTela();
        // /quit para sair do chat e fechar o socket
        if($input == "/quit") {
            socket_write($sock, $input, strlen($input));
            socket_shutdown($sock, 2);
            socket_close($sock);
            echo textoCor("Sessão terminada com sucesso.\n", "RED");
            break;
        } 
        else if ($input == "") {
            $input = " "; //ALT + 0160
        } // /h para ver o histórico de mensagens
        else if($input == "/h") {
            limparTela();
            socket_write($sock, $input, strlen($input));

            $historico = socket_read($sock, 8192);
            $historico = json_decode($historico, true);

            printHistorico($historico);
            
            echo("Histórico de mensagens\n");
            echo("Digite qualquer tecla para voltar\n");
            readline(": ");
        }
        socket_write($sock, $input, strlen($input));

        //O array em formato JSON é convertido novamente para array e é apresentado
        $output = socket_read($sock, 8192);
        $output = json_decode($output, true);
        printArray($output);
    }
    
} //============================ UDP ============================
else if ($protocolo == 2) {
    //criação do socket
    $sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    if(!$sock) 
        die("Não foi possível criar o socket");

    limparTela();

    while(true) {
        $input = readline(": ");
        limparTela();
        if($input == "/quit") {
            echo textoCor("A terminar sessão...\n", "RED");
            socket_shutdown($sock, 2);
            socket_close($sock);
            echo textoCor("Sessão terminada com sucesso.\n\n", "UNDERSCORE");
            break;
        } else if ($input == "") {
            $input = " ";
        }

        socket_sendto($sock, $input, strlen($input), 0, $ip, $port);

        socket_recv($sock, $output, 2048, 0);
        $output = json_decode($output, true);
        printArray($output);
    }
} else if ($protocolo == 3) {

} else {
    limparTela();
    $protocolo = protocolo();
    goto start;
}
?>