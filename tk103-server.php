<?php
 
include 'dbconnect.php';

// Configuración
$ip_address = "192.168.1.169"; 
$port = "7331";
$log_file = "gps_log_" . date("Y-m-d") . ".log";
$min_distance_meters = 1; // Distancia mínima para registrar nueva posición
$max_speed_kmh = 180; // Velocidad máxima razonable
$connection_timeout = 600; // Tiempo en segundos para considerar una conexión inactiva (6 minutos)

// Iniciar registro
function log_message($message) {
    global $log_file;
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents($log_file, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
    echo "$message\n";
}

log_message("Iniciando servidor GPS en $ip_address:$port");

// Iniciar servidor con manejo de errores mejorado
$server = @stream_socket_server("tcp://$ip_address:$port", $errno, $errorMessage);
 
if ($server === false) {
    log_message("ERROR: No se pudo iniciar el servidor: $errorMessage ($errno)");
    die("stream_socket_server error: $errorMessage");
}

log_message("Servidor iniciado correctamente");
 
$client_sockets = array();
$buffer = array(); // Buffer para almacenar datos temporalmente
$connected_devices = array(); // Dispositivos conectados
$imei_to_socket = array(); // Mapeo de IMEI a socket
function validate_gps_data($latitude, $longitude, $speed) {
    global $max_speed_kmh;

    // Validar coordenadas
    if (empty($latitude) || empty($longitude)) {
        return false;
    }

    // Validar rango de coordenadas
    if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
        return false;
    }

    // Validar velocidad
    if ($speed < 0 || $speed > $max_speed_kmh) {
        return false;
    }

    // Validar que las coordenadas no sean 0,0 (punto nulo común)
    if (abs($latitude) < 0.0001 && abs($longitude) < 0.0001) {
        return false;
    }

    return true;
}



// Bucle principal con manejo de errores
while (true) {
    try {
        // Limpiar conexiones inactivas
        cleanup_inactive_connections();
        
        // prepare readable sockets
        $read_sockets = $client_sockets;
        $read_sockets[] = $server;
     
        // start reading and use a large timeout
        if(!@stream_select($read_sockets, $write, $except, 300000)) {
            log_message("ERROR: Error en stream_select, reintentando...");
            continue; // En lugar de morir, continuamos
        }
     
        // new client
        if(in_array($server, $read_sockets)) {
            $new_client = @stream_socket_accept($server);
     
            if ($new_client) {
                $client_info = stream_socket_get_name($new_client, true);
                $client_ip = explode(':', $client_info)[0];
                
                // Registrar nueva conexión con información temporal
                $socket_id = (int)$new_client;
                $connected_devices[$socket_id] = [
                    'imei' => null, // Se actualizará cuando recibamos el IMEI
                    'last_active' => time(),
                    'ip' => $client_ip,
                    'connection_time' => date('Y-m-d H:i:s')
                ];
                
                log_message("Nueva conexión desde IP: $client_ip (Socket ID: $socket_id)");
     
                $client_sockets[] = $new_client;
                log_message("Total clientes: " . count($client_sockets));
            }
     
            //delete the server socket from the read sockets
            unset($read_sockets[array_search($server, $read_sockets)]);
        }
     
        // message from existing client
        foreach ($read_sockets as $socket) {
            $socket_id = (int)$socket;
            $data = @fread($socket, 128);
            
            if (!$data) {
                // Cliente desconectado
                handle_client_disconnect($socket);
                continue;
            }
            
            // Actualizar timestamp de última actividad
            if (isset($connected_devices[$socket_id])) {
                $connected_devices[$socket_id]['last_active'] = time();
            }
            
            log_message("Datos recibidos: " . trim($data));
     
            $tk103_data = explode(',', $data);
            $response = "";		
     
            switch (count($tk103_data)) {
                case 1: // 359710049095095 -> heartbeat requires "ON" response
                    $possible_imei = trim($tk103_data[0]);
                    if (is_numeric($possible_imei) && strlen($possible_imei) > 10) {
                        // Es probablemente un IMEI, actualizar la información del dispositivo
                        update_device_info($socket, $possible_imei);
                    }
                    $response = "ON";
                    log_message("Enviado ON al cliente (heartbeat)");
                    break;
                case 3: // ##,imei:359710049095095,A -> this requires a "LOAD" response
                    if ($tk103_data[0] == "##") {
                        // Extraer IMEI del formato "imei:359710049095095"
                        $imei_part = $tk103_data[1];
                        if (strpos($imei_part, 'imei:') === 0) {
                            $imei = substr($imei_part, 5);
                            update_device_info($socket, $imei);
                            log_message("Dispositivo identificado con IMEI: $imei");
                        }
                        $response = "LOAD";
                        log_message("Enviado LOAD al cliente (inicio)");
                    }
                    break;
                case 19: // imei:359710049095095,tracker,151006012336,,F,172337.000,A,5105.9792,N,11404.9599,W,0.01,322.56,,0,0,,, 
                    try {
                        $imei_part = $tk103_data[0];
                        $imei = "";
                        if (strpos($imei_part, 'imei:') === 0) {
                            $imei = substr($imei_part, 5);
                            update_device_info($socket, $imei);
                        }
                        
                        $alarm = $tk103_data[1];
                        $gps_time = nmea_to_mysql_time($tk103_data[2]);
                        $gps_status = $tk103_data[6]; // A=válido, V=inválido
                        $latitude = degree_to_decimal($tk103_data[7], $tk103_data[8]);				
                        $longitude = degree_to_decimal($tk103_data[9], $tk103_data[10]);
                        $speed_in_knots = floatval($tk103_data[11]);
                        $speed_in_kmh = 1.852 * $speed_in_knots; 
                        $bearing = $tk103_data[12];
                        
                        // Validar datos GPS
                        if ($gps_status == 'A' && validate_gps_data($latitude, $longitude, $speed_in_kmh)) {
                            $result = insert_location_into_db($pdo, $imei, $gps_time, $latitude, $longitude, $speed_in_kmh, $bearing);
                            if ($result === 'IMEI_NOT_REGISTERED') {
                                $response = "IMEI_NOT_REGISTERED";
                                log_message("IMEI $imei no está registrado en la tabla dispositivos. No se insertará la ubicación.");
                            }
                        } else {
                            log_message("Datos GPS inválidos, no se insertarán en la base de datos");
                        }
     
                        if ($alarm == "help me") {
                            $response = "**,imei:" . $imei . ",E;";
                            log_message("Alarma de ayuda recibida del dispositivo $imei");
                        }
                    } catch (Exception $e) {
                        log_message("ERROR al procesar datos GPS: " . $e->getMessage());
                    }
                    break;
            }
     
            //send the message back to client
            if (!empty($response)) {
                @fwrite($socket, $response);
            }
        }
    } catch (Exception $e) {
        log_message("ERROR en el bucle principal: " . $e->getMessage());
        // Esperar un poco antes de continuar
        sleep(5);
    }
} // end while loop

// Función para manejar la desconexión de un cliente
function handle_client_disconnect($socket) {
    global $client_sockets, $connected_devices, $imei_to_socket;
    
    $socket_id = (int)$socket;
    
    // Registrar la desconexión
    if (isset($connected_devices[$socket_id])) {
        $device_info = $connected_devices[$socket_id];
        $imei = $device_info['imei'];
        $ip = $device_info['ip'];
        
        if ($imei) {
            log_message("Dispositivo con IMEI $imei desconectado desde IP $ip");
            // Eliminar del mapeo IMEI a socket
            if (isset($imei_to_socket[$imei]) && $imei_to_socket[$imei] == $socket_id) {
                unset($imei_to_socket[$imei]);
            }
        } else {
            log_message("Cliente desconectado desde IP $ip (sin IMEI identificado)");
        }
        
        // Eliminar de la lista de dispositivos conectados
        unset($connected_devices[$socket_id]);
    }
    
    // Eliminar de la lista de sockets
    unset($client_sockets[array_search($socket, $client_sockets)]);
    @fclose($socket);
    log_message("Total clientes: " . count($client_sockets));
}

// Función para actualizar la información del dispositivo
function update_device_info($socket, $imei) {
    global $connected_devices, $imei_to_socket;
    
    $socket_id = (int)$socket;
    
    // Verificar si ya teníamos este IMEI conectado a otro socket
    if (isset($imei_to_socket[$imei]) && $imei_to_socket[$imei] != $socket_id) {
        $old_socket_id = $imei_to_socket[$imei];
        if (isset($connected_devices[$old_socket_id])) {
            $old_ip = $connected_devices[$old_socket_id]['ip'];
            log_message("Reconexión detectada para IMEI $imei. Anterior: IP $old_ip, Nuevo: IP {$connected_devices[$socket_id]['ip']}");
        }
    }
    
    // Actualizar la información del dispositivo
    if (isset($connected_devices[$socket_id])) {
        $connected_devices[$socket_id]['imei'] = $imei;
        $connected_devices[$socket_id]['last_active'] = time();
    }
    
    // Actualizar el mapeo de IMEI a socket
    $imei_to_socket[$imei] = $socket_id;
}

// Función para limpiar conexiones inactivas
function cleanup_inactive_connections() {
    global $client_sockets, $connected_devices, $imei_to_socket, $connection_timeout;
    
    $current_time = time();
    $inactive_sockets = [];
    
    foreach ($connected_devices as $socket_id => $device_info) {
        if (($current_time - $device_info['last_active']) > $connection_timeout) {
            // Esta conexión ha estado inactiva por demasiado tiempo
            $inactive_sockets[] = $socket_id;
        }
    }
    
    // Cerrar y eliminar las conexiones inactivas
    foreach ($inactive_sockets as $socket_id) {
        if (isset($connected_devices[$socket_id])) {
            $device_info = $connected_devices[$socket_id];
            $imei = $device_info['imei'];
            $ip = $device_info['ip'];
            $inactive_time = $current_time - $device_info['last_active'];
            
            log_message("Cerrando conexión inactiva: " . 
                        ($imei ? "IMEI $imei" : "Sin IMEI") . 
                        " desde IP $ip (inactivo por $inactive_time segundos)");
            
            // Buscar el socket real para cerrarlo
            foreach ($client_sockets as $index => $socket) {
                if ((int)$socket === $socket_id) {
                    @fclose($socket);
                    unset($client_sockets[$index]);
                    break;
                }
            }
            
            // Eliminar de las estructuras de seguimiento
            unset($connected_devices[$socket_id]);
            if ($imei && isset($imei_to_socket[$imei]) && $imei_to_socket[$imei] == $socket_id) {
                unset($imei_to_socket[$imei]);
            }
        }
    }
    
    if (count($inactive_sockets) > 0) {
        log_message("Se cerraron " . count($inactive_sockets) . " conexiones inactivas. Total clientes: " . count($client_sockets));
    }
}

function haversine_distance($lat1, $lon1, $lat2, $lon2)
{
    $earthRadius = 6371000; // Radius of the earth in meters

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
        sin($dLon / 2) * sin($dLon / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $distance = $earthRadius * $c; // Distance in meters

    return $distance;
}


function insert_location_into_db($pdo, $imei, $gps_time, $latitude, $longitude, $speed_in_kmh, $bearing, $userName = null, $sessionID = null, $locationMethod = null, $accuracy = null, $extraInfo = null, $eventType = null) {
    global $min_distance_meters;
    
    // Verificar si el IMEI está registrado en la tabla dispositivos
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM dispositivos WHERE imei = :imei');
    $stmt->execute([':imei' => $imei]);
    $imei_exists = $stmt->fetchColumn();
    if (!$imei_exists) {
        log_message("IMEI $imei no está registrado en la tabla dispositivos. No se insertará la ubicación.");
        return 'IMEI_NOT_REGISTERED';
    }

    // Verificar si la latitud comienza con "0.0"
    if (strpos($latitude, '0.0') === 0) {
        log_message("Latitud inválida ($latitude), no se insertará en la base de datos");
        return;
    }

    // Obtener la última ubicación insertada
    $stmt = $pdo->prepare('SELECT latitude, longitude, gpsTime FROM gpslocations WHERE phoneNumber = :imei ORDER BY GPSLocationID DESC LIMIT 1');
    $stmt->execute([':imei' => $imei]);
    $last_location = $stmt->fetch(PDO::FETCH_ASSOC);

    $distance = 0;
    $should_insert = true;
    
    if ($last_location) {
        $last_latitude = $last_location['latitude'];
        $last_longitude = $last_location['longitude'];
        $last_time = strtotime($last_location['gpsTime']);
        $current_time = strtotime($gps_time);
        $time_diff = $current_time - $last_time;

        // Calcular la distancia entre la última ubicación y la nueva ubicación
        $distance = haversine_distance($last_latitude, $last_longitude, $latitude, $longitude);
        log_message("Distancia desde última posición: $distance metros");
        
        // Verificar si la distancia es menor al mínimo y el tiempo es cercano
        if ($distance < $min_distance_meters && $time_diff < 1) { // 1 segundo es prueba momentanaeamente 
            log_message("Ubicación muy similar a la última insertada, no se insertará en la base de datos");
            $should_insert = false;
        }
    }

    if ($should_insert) {
        try {
            // Asignar valores predeterminados si no se reciben
            $userName = $userName ?? "tk103";
            $sessionID = $sessionID ?? "1";
            $locationMethod = $locationMethod ?? "GPS";
            $accuracy = $accuracy ?? "10";
            $extraInfo = $extraInfo ?? "";
            $eventType = $eventType ?? "tk103";

            // Preparar la consulta para insertar
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
                ':userName' => $userName,
                ':sessionID' => $sessionID,
                ':speed' => $speed_in_kmh, 
                ':direction' => $bearing,
                ':distance' => $distance, 
                ':gpsTime' => $gps_time,
                ':locationMethod' => $locationMethod,
                ':accuracy' => $accuracy,
                ':extraInfo' => $extraInfo,
                ':eventType' => $eventType,
            ];

            $stmt->execute($params);
            log_message("Datos insertados correctamente en la base de datos para IMEI: $imei");

        } catch (PDOException $e) {
            log_message("ERROR al insertar en la base de datos: " . $e->getMessage());
            // Registrar más detalles del error
            log_message("Detalles de la consulta: " . json_encode($params));
        }
    }
}

function nmea_to_mysql_time($date_time){
    if (strlen($date_time) < 12) {
        return date("Y-m-d H:i:s"); // Usar fecha actual si el formato es inválido
    }
    
    $year = substr($date_time,0,2);
    $month = substr($date_time,2,2);
    $day = substr($date_time,4,2);
    $hour = substr($date_time,6,2);
    $minute = substr($date_time,8,2);
    $second = substr($date_time,10,2);
    
    // Ajustar año (asumiendo 20xx para años menores a 80, 19xx para el resto)
    $full_year = (intval($year) < 80) ? "20$year" : "19$year";
    
    return date("Y-m-d H:i:s", mktime($hour,$minute,$second,$month,$day,$full_year));
}
 
function degree_to_decimal($coordinates_in_degrees, $direction){
    if (empty($coordinates_in_degrees) || !is_numeric($coordinates_in_degrees)) {
        return 0;
    }
    
    $degrees = (int)($coordinates_in_degrees / 100); 
    $minutes = $coordinates_in_degrees - ($degrees * 100);
    $seconds = $minutes / 60;
    $coordinates_in_decimal = $degrees + $seconds;
 
    if (($direction == "S") || ($direction == "W")) {
        $coordinates_in_decimal = $coordinates_in_decimal * (-1);
    }
 
    return number_format($coordinates_in_decimal, 6, '.', '');
    // Limpiar memoria periódicamente
    if (time() % 3600 == 0) { // Cada hora
    gc_collect_cycles();
    log_message("Limpieza de memoria realizada");
    // 9. Registro de estadísticas
    

    
    // Registrar estadísticas cada hora
    if (time() % 3600 < 10) { // Cada hora (con margen de 10 segundos)
    $total_devices = count($imei_to_socket);
    $total_connections = count($client_sockets);
    log_message("Estadísticas: $total_devices dispositivos activos, $total_connections conexiones");
    }
}
}