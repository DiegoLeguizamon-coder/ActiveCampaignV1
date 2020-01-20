<?php

require '../vendor/autoload.php';


if ($_POST) {

    //Recibe el POST que envía Active Campaign//

    $data = $_POST;
    $email = "alejandrohuertas1992@gmail.com";

    error_log(json_encode($_POST, true));

    //Consulta a la Base de datos Mongo//

    $conexion = new MongoDB\Client("mongodb://operador:VW2Qc7dX@ds135968-a0.mlab.com:35968,ds135968-a1.mlab.com:35968/clipclap_prod?replicaSet=rs-ds135968");
    $coleccion = $conexion->clipclap_prod->_User;

    $datos = $coleccion->find(['email' => $email]);

    foreach ($datos as $entrada) {

        if ($entrada['email'] == $email) {

            //Construcción de la respuesta al API keos.api-us1.com/admin/api.php //

            $url = 'keos.api-us1.com/admin/api.php';

            $params = array(

                'api_key'      => 'eb9731e0b60fe3851e0a73b0a447d59d0f907cf0eeb0e0ed3d8f05e60910c037056adde0',
                'api_action'   => 'contact_sync',
                'api_output'   => 'serialize',

            );

            $post = array(
                'email' => 'jpaillacho@anyway.com.ec',
                'field[%AUTOMATIZACION%]' => 'Validado'

            );

            $query = "";
            foreach ($params as $key => $value) $query .= urlencode($key) . '=' . urlencode($value) . '&';
            $query = rtrim($query, '& ');


            $data = "";
            foreach ($post as $key => $value) $data .= urlencode($key) . '=' . urlencode($value) . '&';
            $data = rtrim($data, '& ');

            $url = rtrim($url, '/ ');


            if (!function_exists('curl_init')) die('CURL not supported. (introduced in PHP 4.0.2)');

            if ($params['api_output'] == 'json' && !function_exists('json_decode')) {
                die('JSON not supported. (introduced in PHP 5.2.0)');
            }

            $api = $url . '/admin/api.php?' . $query;

            $request = curl_init($api);
            curl_setopt($request, CURLOPT_HEADER, 0);
            curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($request, CURLOPT_POSTFIELDS, $data);
            curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);

            $response = (string) curl_exec($request);


            curl_close($request);

            if (!$response) {
                die('Error en conexión.');
            }

            $result = unserialize($response);

            echo 'Result: ' . ($result['result_code'] ? 'SUCCESS' : 'FAILED') . '<br />';
            echo 'Message: ' . $result['result_message'] . '<br />';

            echo '<pre>';
            print_r($result);
            echo '</pre>';
        }
    }
} else {
    if (file_exists('error_log')) {

        $file = file_get_contents('error_log');
        echo "<pre>";
        print_r($file);
        echo "</pre>";
    } else {
        echo  "error con el formato del POST";
    }
}