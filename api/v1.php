<?php

require '../vendor/autoload.php';

switch ($_GET['action']) {
    case "contact_add":

        $url = 'keos.api-us1.com';


        $params = [

            'api_key'      => 'eb9731e0b60fe3851e0a73b0a447d59d0f907cf0eeb0e0ed3d8f05e60910c037056adde0', /*Keos.co*/
            'api_action'   => 'contact_add',
            'api_output'   => 'serialize',

        ];

        $post = [
            'email'                    => 'prueba@correo.com',
            'first_name'               => 'Diego',
            'last_name'                => 'Leguizamón',
            'phone'                    => '+1 312 201 0300',
            'field[%AUTOMATIZACION%]'  => 'Etapa 1',
            'field[%HABEAS_DATA%]'     => 'Acepto los términos y condiciones',
            'field[%FECHA_CREACIN_CONTACTO%]'  => date("Ymd"),

            'p[20]'                   => 20,
        ];

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

        $request = curl_init($api); // initiate curl object
        curl_setopt($request, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
        curl_setopt($request, CURLOPT_POSTFIELDS, $data); // use HTTP POST to send form data
        //curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment if you get no gateway response and are using HTTPS
        curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);

        $response = (string) curl_exec($request); // execute curl post and store results in $response

        // additional options may be required depending upon your server configuration
        // you can find documentation on curl options at http://www.php.net/curl_setopt
        curl_close($request); // close curl object

        if (!$response) {
            die('Nothing was returned. Do you have a connection to Email Marketing server?');
        }

        // This line takes the response and breaks it into an array using:
        // JSON decoder
        //$result = json_decode($response);
        // unserializer
        $result = unserialize($response);
        // XML parser...
        // ...

        // Result info that is always returned
        echo 'Result: ' . ($result['result_code'] ? 'SUCCESS' : 'FAILED') . '<br />';
        echo 'Message: ' . $result['result_message'] . '<br />';

        // The entire result printed out
        echo 'The entire result printed out:<br />';
        echo '<pre>';
        print_r($result);
        echo '</pre>';

        // Raw response printed out
        echo 'Raw response printed out:<br />';
        echo '<pre>';
        print_r($response);
        echo '</pre>';

        // API URL that returned the result
        echo 'API URL that returned the result:<br />';
        echo $api;

        echo '<br /><br />POST params:<br />';
        echo '<pre>';
        print_r($post);
        echo '</pre>';

        break;

    case "contact_sync":

        $flat = false;

        $enc = $_POST['contact'];
        $email = $enc["email"];

        error_log(json_encode($_POST, true));

        //Consulta a la Base de datos Mongo//

        $conexion = new MongoDB\Driver\Manager("mongodb://operador:VW2Qc7dX@ds135968-a0.mlab.com:35968,ds135968-a1.mlab.com:35968/clipclap_prod?replicaSet=rs-ds135968");

        $filter = ['email' => $email];
        $query = new MongoDB\Driver\Query($filter);
        $datosUser = $conexion->executeQuery('clipclap_prod._User', $query);

        foreach ($datosUser as $contacto) {

            $flat = true;

            if ($contacto->email == $email) {

                if (isset($contacto->_p_personalInfo)) {

                    $datoPersonalInfo = strval($contacto->_p_personalInfo);
                    $idPersonalinfo = trim($datoPersonalInfo, "PersonalInfo$");

                    $filter = ['_id' => $idPersonalinfo];
                    $query = new MongoDB\Driver\Query($filter);
                    $datosPersonalInfo = $conexion->executeQuery('clipclap_prod.PersonalInfo', $query);

                    foreach ($datosPersonalInfo as $personalinfo) {

                        if (isset($personalinfo->hasBeenCCValidated) && $personalinfo->hasBeenCCValidated == "true") {
                            $estado = "Etapa 3";
                        } else {
                            if (isset($contacto->_p_business)) {
                                $estado = "Etapa 2";
                            } else {
                                $estado = "Etapa 1";
                            }
                        }
                    }
                }
                //Construcción de la respuesta en el API keos.api-us1.com/admin/api.php //

                $url = 'keos.api-us1.com/admin/api.php'; /*keos*/

                $params = [

                    'api_key'      => 'eb9731e0b60fe3851e0a73b0a447d59d0f907cf0eeb0e0ed3d8f05e60910c037056adde0', /*Keos.co*/
                    'api_action'   => 'contact_sync',
                    'api_output'   => 'serialize',

                ];

                $post = [
                    'email' => $email,
                    'field[%AUTOMATIZACION%]' => $estado

                ];

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

                echo $estado . "es el estado" . '<br />';

                echo 'Result: ' . ($result['result_code'] ? 'SUCCESS' : 'FAILED') . '<br />';
                echo 'Message: ' . $result['result_message'] . '<br />';

                echo '<pre>';
                print_r($result);
                echo '</pre>';
            }
        }

        break;


    case "":
        if (file_exists('error_log')) {

            $file = file_get_contents('error_log');
            echo "<pre>";
            print_r($file);
            echo "</pre>";
        } else {
            echo  "error con el formato del POST";
        }

        break;
}

//error_log("--->". json_encode($_POST['contact']['email']));
/*if ($_GET) {

    //Recibe el POST que envía Active Campaign//

    
} else if($_GET){

    

}else {
    
}*/
?>