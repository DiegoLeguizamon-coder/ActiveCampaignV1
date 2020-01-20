<?php

require '../vendor/autoload.php'; 

if($_POST){

    $email = "alejandrohuertas1992@gmail.com";

    error_log(json_encode($_POST, true));

    $conexion = new MongoDB\Client("mongodb://operador:VW2Qc7dX@ds135968-a0.mlab.com:35968,ds135968-a1.mlab.com:35968/clipclap_prod?replicaSet=rs-ds135968");
    $coleccion = $conexion->clipclap_prod->_User;

    $resultado = $coleccion->find(['email' => $email]);

    foreach($resultado as $entrada){

        if($entrada['email'] == $email){
            echo "<p> El usuario ya se ha registrado</p>";
            echo $entrada['_id'] , ' : ', $entrada['email'];
        }
    }

}
else{
    if( file_exists( 'error_log') ){

        $file = file_get_contents( 'error_log' );
        echo "<pre>";print_r ($file);echo "</pre>";

    } 
}

?>