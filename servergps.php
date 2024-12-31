<?php
 
 include 'dbconnect.php';
 
 $ip_address = "192.168.1.169"; 
 $port = "7331";
$server = stream_socket_server("tcp://$ip_address:$port", $errno, $errorMessage);
 
if ($server === false) {
    die("stream_socket_server error: $errorMessage");
}
 
$client_sockets = array();
 
while (true) {
    // prepare readable sockets
    $read_sockets = $client_sockets;
    $read_sockets[] = $server;
 
    // start reading and use a large timeout
    if(!stream_select($read_sockets, $write, $except, 300000)) {
        die('stream_select error.');
    }
 
    // new client
    if(in_array($server, $read_sockets)) {
        $new_client = stream_socket_accept($server);
 
        if ($new_client) {
            //print remote client information, ip and port number
            echo 'new connection: ' . stream_socket_get_name($new_client, true) . "\n";
 
            $client_sockets[] = $new_client;
            echo "total clients: ". count($client_sockets) . "\n";
 
            // $output = "hello new client.\n";
            // fwrite($new_client, $output);
        }
 
        //delete the server socket from the read sockets
        unset($read_sockets[ array_search($server, $read_sockets) ]);
    }
 
    // message from existing client
    foreach ($read_sockets as $socket) {
        $data = fread($socket, 128);
        
        echo "data: " . $data . "\n";
 
        $tk103_data = explode( ',', $data);
        $response = "";		
 
        switch (count($tk103_data)) {
            case 1: // 359710049095095 -> heartbeat requires "ON" response
                $response = "ON";
                echo "sent ON to client\n";
                break;
            case 3: // ##,imei:359710049095095,A -> this requires a "LOAD" response
                if ($tk103_data[0] == "##") {
                    $response = "LOAD";
                    echo "sent LOAD to client\n";
                }
                break;
            case 19: // imei:359710049095095,tracker,151006012336,,F,172337.000,A,5105.9792,N,11404.9599,W,0.01,322.56,,0,0,,,  -> this is our gps data
                $imei = substr($tk103_data[0], 5);
                $alarm = $tk103_data[1];
                $gps_time = nmea_to_mysql_time($tk103_data[2]);
                $latitude = degree_to_decimal($tk103_data[7], $tk103_data[8]);				
                $longitude = degree_to_decimal($tk103_data[9], $tk103_data[10]);
                $speed_in_knots = $tk103_data[11];
                $speed_in_mph = 1.15078 * $speed_in_knots;
                $bearing = $tk103_data[12];			
 
                insert_location_into_db($pdo, $imei, $gps_time, $latitude, $longitude, $speed_in_mph, $bearing);
 
                if ($alarm == "help me") {
                    $response = "**,imei:" + $imei + ",E;";
                }
                break;
            }
 
            if (!$data) {
                unset($client_sockets[ array_search($socket, $client_sockets) ]);
                @fclose($socket);
                echo "client disconnected. total clients: ". count($client_sockets) . "\n";
                continue;
            }
 
            //send the message back to client
            if (!empty($response)) {
                fwrite($socket, $response);
            }
            
        }
} // end while loop
 
function haversine_distance($lat1, $lon1, $lat2, $lon2) {
    $earth_radius = 6371000; // Radio de la Tierra en metros

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);

    $c = 2 * asin(sqrt($a));

    return $earth_radius * $c;
}

function insert_location_into_db($pdo, $imei, $gps_time, $latitude, $longitude, $speed_in_mph, $bearing) {
    // Verificar si la latitud comienza con "0.0"
    if (strpos($latitude, '0.0') === 0) {
        echo "Latitud inválida, no se insertará en la base de datos.\n";
        return;
    }

    // Obtener la última ubicación insertada
    $stmt = $pdo->prepare('SELECT latitude, longitude FROM gpslocations ORDER BY GPSLocationID DESC LIMIT 1');
    $stmt->execute();
    $last_location = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($last_location) {
        $last_latitude = $last_location['latitude'];
        $last_longitude = $last_location['longitude'];

        // Calcular la distancia entre la última ubicación y la nueva ubicación
        $distance = haversine_distance($last_latitude, $last_longitude, $latitude, $longitude);
    echo "Distancia: $distance\n";
        // Verificar si la distancia es menor a 10 metros
        if ($distance < 10) {
            echo "Ubicación muy similar a la última insertada, no se insertará en la base de datos.\n";
            return;
        }
    }
exit();
    try {
        $stmt = $pdo->prepare('INSERT INTO gpslocations (
            latitude,
            longitude,
            phoneNumber,
            userName,
            sessionID,
            speed,
            direction,
            distance,
            gpsTime,
            locationMethod,
            accuracy,
            extraInfo,
            eventType
        ) VALUES (
            :latitude,
            :longitude,
            :phoneNumber,
            :userName,
            :sessionID,
            :speed,
            :direction,
            :distance,
            :gpsTime,
            :locationMethod,
            :accuracy,
            :extraInfo,
            :eventType
        )');

        $params = [
            ':latitude' => $latitude,
            ':longitude' => $longitude,
            ':phoneNumber' => $imei,
            ':userName' => "tk103",
            ':sessionID' => "1",
            ':speed' => $speed_in_mph,
            ':direction' => $bearing,
            ':distance' => "0", // Si deseas calcular distancia, deberás agregar lógica adicional.
            ':gpsTime' => $gps_time,
            ':locationMethod' => "",
            ':accuracy' => "0",
            ':extraInfo' => "",
            ':eventType' => "tk103",
        ];

        $stmt->execute($params);
        echo "Datos insertados correctamente en la base de datos.\n";

    } catch (PDOException $e) {
        echo "Error al insertar en la base de datos: " . $e->getMessage() . "\n";
    }
}

 
function nmea_to_mysql_time($date_time){
    $year = substr($date_time,0,2);
    $month = substr($date_time,2,2);
    $day = substr($date_time,4,2);
    $hour = substr($date_time,6,2);
    $minute = substr($date_time,8,2);
    $second = substr($date_time,10,2);
 
    return date("Y-m-d H:i:s", mktime($hour,$minute,$second,$month,$day,$year));
}
 
function degree_to_decimal($coordinates_in_degrees, $direction){
    $degrees = (int)($coordinates_in_degrees / 100); 
    $minutes = $coordinates_in_degrees - ($degrees * 100);
    $seconds = $minutes / 60;
    $coordinates_in_decimal = $degrees + $seconds;
 
    if (($direction == "S") || ($direction == "W")) {
        $coordinates_in_decimal = $coordinates_in_decimal * (-1);
    }
 
    return number_format($coordinates_in_decimal, 6,'.','');
}