<?php

include 'dbconnect.php';

// Configuración: valores parametrizables
define('USER_NAME', 'tk103-user');
define('SESSION_ID', '1');
define('LOCATION_METHOD', '');  // Se puede actualizar según la lógica de negocio
define('EXTRA_INFO', '');
define('EVENT_TYPE', 'tk103');

$ip_address = "0.0.0.0";
$port = "7331";

    // Abrir un servidor TCP en el puerto 7331
    $server = stream_socket_server("tcp://$ip_address:$port", $errno, $errorMessage);
    if ($server === false) {
        error_log("stream_socket_server error: $errorMessage");
        die("Error al iniciar el servidor: $errorMessage\n");
    }
    
    $client_sockets = [];

    while (true) {
        // Preparar los sockets para lectura: clientes activos + socket del servidor
        $read_sockets = $client_sockets;
        $read_sockets[] = $server;

        // Declarar variables para parámetros de salida
        $write = null;
        $except = null;

        // Usamos stream_select para monitorear la actividad en los sockets (timeout de 300 segundos)
        $num_changed_sockets = stream_select($read_sockets, $write, $except, 300);
        if ($num_changed_sockets === false) {
            error_log('stream_select error.');
            break;
        }

    // Manejo de nuevas conexiones
    if (in_array($server, $read_sockets)) {
        $new_client = @stream_socket_accept($server, 0);
        if ($new_client) {
            $client_info = stream_socket_get_name($new_client, true);
            echo "Nueva conexión: " . $client_info . "\n";
            $client_sockets[] = $new_client;
        }
        // Remover el socket del servidor del array de lectura
        $server_key = array_search($server, $read_sockets);
        if ($server_key !== false) {
            unset($read_sockets[$server_key]);
        }
    }

    // Procesar mensajes de clientes ya conectados
    foreach ($read_sockets as $socket) {
        $data = '';
        // Leer en bloques hasta encontrar un salto de línea o fin de mensaje
        while (!feof($socket)) {
            $chunk = fread($socket, 128);
            if ($chunk === false) {
                break;
            }
            $data .= $chunk;
            if (strpos($chunk, "\n") !== false) {
                break;
            }
        }
        $data = trim($data);

        // Si no se recibe ningún dato, se cierra la conexión
        if (empty($data)) {
            unset($client_sockets[array_search($socket, $client_sockets)]);
            fclose($socket);
            echo "Cliente desconectado. Total clientes: " . count($client_sockets) . "\n";
            continue;
        }

        echo "Datos recibidos: " . $data . "\n";

        // Separar el mensaje en campos
        $tk103_data = explode(',', $data);
        $response = "";

        // Validar según la cantidad de campos recibidos
        switch (count($tk103_data)) {
            case 1:
                // Heartbeat: se espera solo el IMEI en formato numérico
                if (preg_match('/^\d+$/', $tk103_data[0])) {
                    $response = "ON";
                    echo "Enviado 'ON' al cliente\n";
                } else {
                    error_log("Formato de heartbeat inválido: $data");
                }
                break;
            case 3:
                // Solicitud de datos: formato "##,imei:XXXXXXXXXXXXXX,A"
                if ($tk103_data[0] === "##" && strpos($tk103_data[1], "imei:") === 0) {
                    $response = "LOAD";
                    echo "Enviado 'LOAD' al cliente\n";
                } else {
                    error_log("Formato de solicitud LOAD inválido: $data");
                }
                break;
            case 19:
                // Datos GPS completos
                if (strpos($tk103_data[0], "imei:") === 0) {
                    $imei = substr($tk103_data[0], 5);
                    $alarm = trim($tk103_data[1]);
                    $gps_time_raw = trim($tk103_data[2]);

                    try {
                        $gps_time = nmea_to_mysql_time($gps_time_raw);
                    } catch (Exception $e) {
                        error_log("Error en conversión de fecha/hora: " . $e->getMessage());
                        break;
                    }
                    
                    // Conversión y validación de coordenadas
                    try {
                        $latitude = degree_to_decimal(trim($tk103_data[7]), trim($tk103_data[8]));
                        $longitude = degree_to_decimal(trim($tk103_data[9]), trim($tk103_data[10]));
                    } catch (Exception $e) {
                        error_log("Error en conversión de coordenadas: " . $e->getMessage());
                        break;
                    }
                    
                    // Validar y convertir velocidad y dirección
                    $speed_in_knots = floatval($tk103_data[11]);
                    $speed_in_mph = 1.15078 * $speed_in_knots;
                    $bearing = intval($tk103_data[12]);

                    // Insertar los datos en la base de datos
                    try {
                        insert_location_into_db($pdo, $imei, $gps_time, $latitude, $longitude, $speed_in_mph, $bearing);
                    } catch (Exception $e) {
                        error_log("Error al insertar en DB: " . $e->getMessage());
                    }
                    
                    // En caso de alarma "help me", se envía respuesta específica
                    if (strtolower($alarm) === "help me") {
                        $response = "**,imei:" . $imei . ",E;";
                    }
                } else {
                    error_log("Formato de datos GPS inválido: $data");
                }
                break;
            default:
                error_log("Mensaje con formato desconocido: $data");
                break;
        }

        // Enviar respuesta al cliente si corresponde
        if (!empty($response)) {
            fwrite($socket, $response . "\n");
        }
    } // Fin de foreach para clientes
} // Fin del while principal

// Cerrar el servidor al finalizar (nunca se alcanzará si el bucle es infinito)
fclose($server);

/**
 * Inserta la ubicación GPS en la base de datos usando el procedimiento almacenado prcSaveGPSLocation.
 *
 * @param PDO    $pdo
 * @param string $imei       Número IMEI del dispositivo.
 * @param string $gps_time   Fecha y hora en formato "Y-m-d H:i:s".
 * @param float  $latitude   Latitud en formato decimal.
 * @param float  $longitude  Longitud en formato decimal.
 * @param float  $speed_in_mph Velocidad en millas por hora.
 * @param int    $bearing    Dirección (bearing) en grados.
 * @throws Exception         Si ocurre algún error en la ejecución de la consulta.
 */
function insert_location_into_db($pdo, $imei, $gps_time, $latitude, $longitude, $speed_in_mph, $bearing) {
    // Preparar los parámetros para el procedimiento almacenado
    $params = array(
        ':_latitude'       => $latitude,
        ':_longitude'      => $longitude,
        ':_speed'          => (int)$speed_in_mph,
        ':_direction'      => $bearing,
        ':_distance'       => 0, // Se puede ajustar según la lógica de negocio
        ':_date'           => $gps_time,
        ':_locationMethod' => LOCATION_METHOD,
        ':_userName'       => USER_NAME,
        ':_phoneNumber'    => $imei,
        ':_sessionID'      => SESSION_ID,
        ':_accuracy'       => 0, // Se puede ajustar si se dispone de información de precisión
        ':_extraInfo'      => EXTRA_INFO,
        ':_eventType'      => EVENT_TYPE
    );

    $stmt = $pdo->prepare('CALL prcSaveGPSLocation(
        :_latitude,
        :_longitude,
        :_speed,
        :_direction,
        :_distance,
        :_date,
        :_locationMethod,
        :_userName,
        :_phoneNumber,
        :_sessionID,
        :_accuracy,
        :_extraInfo,
        :_eventType
    )');

    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . implode(" - ", $pdo->errorInfo()));
    }

    if (!$stmt->execute($params)) {
        throw new Exception("Error al ejecutar la consulta: " . implode(" - ", $stmt->errorInfo()));
    }
    
    // Opcional: recuperar la marca de tiempo devuelta por el procedimiento
    $timestamp = $stmt->fetchColumn();
    echo "Datos guardados en DB con timestamp: " . $timestamp . "\n";
}

/**
 * Convierte una cadena en formato NMEA a formato MySQL datetime.
 *
 * @param string $date_time Cadena en formato NMEA (ej. "151006012336").
 * @return string          Fecha y hora en formato "Y-m-d H:i:s".
 * @throws Exception       Si la cadena no tiene el formato esperado.
 */
function nmea_to_mysql_time($date_time) {
    if (strlen($date_time) < 12) {
        throw new Exception("Formato de fecha/hora NMEA inválido: $date_time");
    }
    $year   = substr($date_time, 0, 2);
    $month  = substr($date_time, 2, 2);
    $day    = substr($date_time, 4, 2);
    $hour   = substr($date_time, 6, 2);
    $minute = substr($date_time, 8, 2);
    $second = substr($date_time, 10, 2);
    
    // Ajustar el año asumiendo que valores menores a 70 corresponden a 20XX y mayores a 69 a 19XX
    $year = ($year < 70) ? '20' . $year : '19' . $year;
    
    return date("Y-m-d H:i:s", mktime((int)$hour, (int)$minute, (int)$second, (int)$month, (int)$day, (int)$year));
}

/**
 * Convierte coordenadas en formato grados y minutos a decimal.
 *
 * @param string|float $coordinates_in_degrees Coordenadas en formato grados y minutos (ej. "5105.9792").
 * @param string       $direction               Dirección ("N", "S", "E", "W").
 * @return float        Coordenada en formato decimal.
 * @throws Exception  Si las coordenadas no son numéricas.
 */
function degree_to_decimal($coordinates_in_degrees, $direction) {
    if (!is_numeric($coordinates_in_degrees)) {
        throw new Exception("Coordenadas inválidas: $coordinates_in_degrees");
    }
    $coordinates_in_degrees = floatval($coordinates_in_degrees);
    $degrees = floor($coordinates_in_degrees / 100);
    $minutes = $coordinates_in_degrees - ($degrees * 100);
    $decimal = $degrees + ($minutes / 60);
    
    if (in_array(strtoupper($direction), ['S', 'W'])) {
        $decimal *= -1;
    }
    
    return round($decimal, 6);
}
?>
