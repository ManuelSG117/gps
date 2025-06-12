<?php
namespace app\controllers;


use app\models\Gpslocations;
use app\models\GpslocationsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\helpers\Json;
use Yii;
use yii\data\Pagination;
use yii\web\BadRequestHttpException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;

class GpsreportController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }
    
    /**
     * Endpoint para obtener todos los datos de ubicación sin paginación
     * Este método es llamado vía AJAX desde el frontend para mostrar la ruta completa en el mapa
     */
    public function actionGetAllLocations()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $filter = Yii::$app->request->get('filter', 'today');
        $gps = Yii::$app->request->get('gps', 'all');
        $startDate = Yii::$app->request->get('startDate', null);
        $endDate = Yii::$app->request->get('endDate', null);
    
        // Consulta principal
        $query = GpsLocations::find();
    
        // Solo aplicar filtro de dispositivo si no es 'all'
        if ($gps && $gps !== 'all') {
            $query->andWhere(['phoneNumber' => $gps]);
        }
    
        // Filtrado según el filtro seleccionado
        switch ($filter) {
            case 'today':
                $query->andWhere(['DATE(lastUpdate)' => date('Y-m-d')]);
                break;
            case 'yesterday':
                $query->andWhere(['DATE(lastUpdate)' => date('Y-m-d', strtotime('-1 day'))]);
                break;
            case 'current_week':
                $query->andWhere(['>=', 'DATE(lastUpdate)', date('Y-m-d', strtotime('monday this week'))]);
                break;
            case 'last_week':
                $query->andWhere(['between', 'DATE(lastUpdate)', date('Y-m-d', strtotime('monday last week')), date('Y-m-d', strtotime('sunday last week'))]);
                break;
            case 'current_month':
                $query->andWhere(['>=', 'DATE(lastUpdate)', date('Y-m-01')]);
                break;
            case 'last_month':
                $query->andWhere(['between', 'DATE(lastUpdate)', date('Y-m-d', strtotime('first day of last month')), date('Y-m-d', strtotime('last day of last month'))]);
                break;
            case 'custom':
                if ($startDate && $endDate) {
                    $query->andWhere(['between', 'DATE(lastUpdate)', $startDate, $endDate]);
                } else if ($startDate) {
                    // If only start date is provided
                    $query->andWhere(['>=', 'DATE(lastUpdate)', $startDate]);
                } else if ($endDate) {
                    // If only end date is provided
                    $query->andWhere(['<=', 'DATE(lastUpdate)' => $endDate]);
                }
                break;
        }
    
        // Order by timestamp to ensure proper route display
        $query->orderBy(['lastUpdate' => SORT_ASC]);
        
        // Obtener todos los datos sin paginación
        $locations = $query->all();
        
        return $locations;
    }
    
    public function actionIndex()
    {
        $filter = Yii::$app->request->get('filter', 'today');
        $gps = Yii::$app->request->get('gps', 'all');
        $startDate = Yii::$app->request->get('startDate', null);
        $endDate = Yii::$app->request->get('endDate', null);
    
        // Consulta principal
        $query = GpsLocations::find();
    
        // Solo aplicar filtro de dispositivo si no es 'all'
        if ($gps && $gps !== 'all') {
            $query->andWhere(['phoneNumber' => $gps]);
        }
    
        // Filtrado según el filtro seleccionado
        switch ($filter) {
            case 'today':
                $query->andWhere(['DATE(lastUpdate)' => date('Y-m-d')]);
                break;
            case 'yesterday':
                $query->andWhere(['DATE(lastUpdate)' => date('Y-m-d', strtotime('-1 day'))]);
                break;
            case 'current_week':
                $query->andWhere(['>=', 'DATE(lastUpdate)', date('Y-m-d', strtotime('monday this week'))]);
                break;
            case 'last_week':
                $query->andWhere(['between', 'DATE(lastUpdate)', date('Y-m-d', strtotime('monday last week')), date('Y-m-d', strtotime('sunday last week'))]);
                break;
            case 'current_month':
                $query->andWhere(['>=', 'DATE(lastUpdate)', date('Y-m-01')]);
                break;
            case 'last_month':
                $query->andWhere(['between', 'DATE(lastUpdate)', date('Y-m-d', strtotime('first day of last month')), date('Y-m-d', strtotime('last day of last month'))]);
                break;
            case 'custom':
                if ($startDate && $endDate) {
                    $query->andWhere(['between', 'DATE(lastUpdate)', $startDate, $endDate]);
                } else if ($startDate) {
                    // If only start date is provided
                    $query->andWhere(['>=', 'DATE(lastUpdate)', $startDate]);
                } else if ($endDate) {
                    // If only end date is provided
                    $query->andWhere(['<=', 'DATE(lastUpdate)' => $endDate]);
                }
                break;
        }
    
        // Order by timestamp to ensure proper route display
        $query->orderBy(['lastUpdate' => SORT_ASC]);
        
        $locations = $query->all();
    
        // If it's a PJAX request, render only the content
        if (Yii::$app->request->isPjax) {
            return $this->renderAjax('index', [
                'locations' => $locations,
            ]);
        }
        
        return $this->render('index', [
            'locations' => $locations,
        ]);
    }   

    public function actionDownloadReport($filter = 'today', $gps = 'all', $startDate = null, $endDate = null, $includeChart = true)
    {
        $query = GpsLocations::find();
        $period = '';
        
        // Solo aplicar filtro de dispositivo si no es 'all'
        if ($gps && $gps !== 'all') {
            $query->andWhere(['phoneNumber' => $gps]);
        }

        // Filtrar datos según el filtro seleccionado
        switch ($filter) {
            case 'today':
                if ($startDate && $endDate) {
                    $query->where(['between', 'DATE(lastUpdate)', $startDate, $endDate]);
                    $period = $startDate . ' - ' . $endDate;
                } else {
                    $today = date('Y-m-d');
                    $query->where(['DATE(lastUpdate)' => $today]);
                    $period = $today;
                }
                break;
            case 'yesterday':
                $yesterday = date('Y-m-d', strtotime('-1 day'));
                $query->where(['DATE(lastUpdate)' => $yesterday]);
                $period = $yesterday;
                break;
            case 'current_week':
                $startOfWeek = date('Y-m-d', strtotime('monday this week'));
                $endOfWeek = date('Y-m-d', strtotime('sunday this week'));
                $query->where(['between', 'DATE(lastUpdate)', $startOfWeek, $endOfWeek]);
                $period = $startOfWeek . ' - ' . $endOfWeek;
                break;
            case 'last_week':
                $startOfLastWeek = date('Y-m-d', strtotime('monday last week'));
                $endOfLastWeek = date('Y-m-d', strtotime('sunday last week'));
                $query->where(['between', 'DATE(lastUpdate)', $startOfLastWeek, $endOfLastWeek]);
                $period = $startOfLastWeek . ' - ' . $endOfLastWeek;
                break;
            case 'current_month':
                $startOfMonth = date('Y-m-01');
                $endOfMonth = date('Y-m-t');
                $query->where(['between', 'DATE(lastUpdate)', $startOfMonth, $endOfMonth]);
                $period = $startOfMonth . ' - ' . $endOfMonth;
                break;
            case 'last_month':
                $startOfLastMonth = date('Y-m-d', strtotime('first day of last month'));
                $endOfLastMonth = date('Y-m-d', strtotime('last day of last month'));
                $query->where(['between', 'DATE(lastUpdate)', $startOfLastMonth, $endOfLastMonth]);
                $period = $startOfLastMonth . ' - ' . $endOfLastMonth;
                break;
            case 'custom':
                if ($startDate && $endDate) {
                    $query->where(['between', 'DATE(lastUpdate)', $startDate, $endDate]);
                    $period = $startDate . ' - ' . $endDate;
                } else {
                    $period = 'Personalizado';
                }
                break;
            default:
                return $this->redirect(['index']);
        }

        $locations = $query->all();
    
        // Crear un archivo Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        // Agregar título y subtítulo
        $sheet->setCellValue('A1', 'Reporte');
        $sheet->setCellValue('A2', 'Periodo: ' . $period);
        $sheet->mergeCells('A1:E1');
        $sheet->mergeCells('A2:E2');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
    
        // Definir encabezados
        $sheet->setCellValue('A3', 'Latitud');
        $sheet->setCellValue('B3', 'Longitud');
        $sheet->setCellValue('C3', 'Fecha');
        $sheet->setCellValue('D3', 'Velocidad');
        $sheet->setCellValue('E3', 'Dirección'); 
    
        // Aplicar estilo a los encabezados
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF116DA5'],
            ],
        ];
        $sheet->getStyle('A3:E3')->applyFromArray($headerStyle);

        // Agregar los datos al archivo Excel
        $row = 4;
        $dailySpeeds = [];
        foreach ($locations as $location) {
            $sheet->setCellValue('A' . $row, $location->latitude);
            $sheet->setCellValue('B' . $row, $location->longitude);
            $sheet->setCellValue('C' . $row, $location->lastUpdate);
            $sheet->setCellValue('D' . $row, $location->speed);    
            // Crear un enlace a Google Maps usando las coordenadas
            $mapLink = 'https://www.google.com/maps?q=' . $location->latitude . ',' . $location->longitude;
            // Usar las coordenadas como texto del hipervínculo
            $locationText = $location->latitude . ', ' . $location->longitude;
        
            // Crear un hipervínculo en la celda de Excel con las coordenadas
            $sheet->setCellValue('E' . $row, '=HYPERLINK("' . $mapLink . '", "' . $locationText . '")');
        
            // Aplicar estilo de hipervínculo
            $sheet->getStyle('E' . $row)->applyFromArray([
                'font' => [
                    'color' => ['argb' => 'FF0000FF'],
                    'underline' => 'single'
                ]
            ]);
            
            // Establecer el tooltip del hipervínculo (revisar su funcionamiento en Excel)
            $sheet->getCell('E' . $row)->getHyperlink()->setTooltip('Abrir en Maps');

            // Calcular la velocidad media por día, omitiendo velocidades de 0
            if ($location->speed > 0) {
                $date = (new \DateTime($location->lastUpdate))->format('Y-m-d');
                if (!isset($dailySpeeds[$date])) {
                    $dailySpeeds[$date] = ['totalSpeed' => 0, 'count' => 0];
                }
                $dailySpeeds[$date]['totalSpeed'] += $location->speed;
                $dailySpeeds[$date]['count']++;
            }
        
            $row++;
        }
        
        // Agregar los datos de velocidad media por día a la hoja de cálculo
        $sheet->setCellValue('G3', 'Fecha Velocidad Media');
        $sheet->setCellValue('H3', 'Velocidad Media');
        $sheet->getStyle('G3:H3')->applyFromArray($headerStyle);
        $row = 4;
        foreach ($dailySpeeds as $date => $data) {
            $averageSpeed = $data['totalSpeed'] / $data['count'];
            $sheet->setCellValue('G' . $row, $date);
            $sheet->setCellValue('H' . $row, $averageSpeed);
            $row++;
        }
    
                // Ajustar el ancho de las columnas automáticamente
        foreach (range('A', 'H') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        if ($includeChart) {
            // Crear una serie de datos para la gráfica
            $dataSeriesLabels = [
                new DataSeriesValues('String', 'Worksheet!$H$3', null, 1), // Etiqueta de la serie
            ];
            $xAxisTickValues = [
                new DataSeriesValues('String', 'Worksheet!$G$4:$G$' . ($row - 1), null, 4), // Valores del eje X (Fechas)
            ];
            $dataSeriesValues = [
                new DataSeriesValues('Number', 'Worksheet!$H$4:$H$' . ($row - 1), null, 4), // Valores del eje Y (Velocidades Medias)
            ];
    
            // Crear la serie de datos
            $series = new DataSeries(
                DataSeries::TYPE_LINECHART, // Tipo de gráfica
                DataSeries::GROUPING_STANDARD, // Agrupamiento
                range(0, count($dataSeriesValues) - 1), // Orden de la serie
                $dataSeriesLabels, // Etiquetas de la serie
                $xAxisTickValues, // Valores del eje X
                $dataSeriesValues // Valores del eje Y
            );
    
            // Crear el área de la gráfica
            $plotArea = new PlotArea(null, [$series]);
    
            // Crear la leyenda de la gráfica
            $legend = new Legend(Legend::POSITION_RIGHT, null, false);
    
            // Crear el título de la gráfica
            $title = new Title('Velocidad Media por Día');
    
            // Crear la gráfica
            $chart = new Chart(
                'chart1', // Nombre de la gráfica
                $title, // Título de la gráfica
                $legend, // Leyenda de la gráfica
                $plotArea, // Área de la gráfica
                true, // Plot visible only
                0, // Display blanks as
                null, // Eje X
                null // Eje Y
            );
    
            // Establecer la posición de la gráfica en la hoja
            $chart->setTopLeftPosition('K1');
            $chart->setBottomRightPosition('R20');
    
            // Agregar la gráfica a la hoja
            $sheet->addChart($chart);
        }
    
        // Configurar el archivo para descarga
        $writer = new Xlsx($spreadsheet);
        $writer->setIncludeCharts($includeChart); // Incluir la gráfica en el archivo si se especifica
        $fileName = 'Reporte_' . date('Ymd_His') . '.xlsx';
        $tempFile = Yii::getAlias('@runtime') . '/' . $fileName;
        $writer->save($tempFile);
    
        return Yii::$app->response->sendFile($tempFile);
    }
    
    public function actionReportStops()
    {
        $filter = Yii::$app->request->get('filter', 'today');
        $gps = Yii::$app->request->get('gps', null);
        $startDate = Yii::$app->request->get('startDate', null);
        $endDate = Yii::$app->request->get('endDate', null);
    
        $query = GpsLocations::find()->orderBy(['lastUpdate' => SORT_ASC]);
    
        if ($gps) {
            $query->andWhere(['phoneNumber' => $gps]);
        }
    
        switch ($filter) {
            case 'today':
                $query->andWhere(['DATE(lastUpdate)' => date('Y-m-d')]);
                break;
            case 'yesterday':
                $query->andWhere(['DATE(lastUpdate)' => date('Y-m-d', strtotime('-1 day'))]);
                break;
            case 'current_week':
                $query->andWhere(['>=', 'DATE(lastUpdate)', date('Y-m-d', strtotime('monday this week'))]);
                break;
            case 'last_week':
                $query->andWhere(['between', 'DATE(lastUpdate)', date('Y-m-d', strtotime('monday last week')), date('Y-m-d', strtotime('sunday last week'))]);
                break;
            case 'current_month':
                $query->andWhere(['>=', 'DATE(lastUpdate)', date('Y-m-01')]);
                break;
            case 'last_month':
                $query->andWhere(['between', 'DATE(lastUpdate)', date('Y-m-d', strtotime('first day of last month')), date('Y-m-d', strtotime('last day of last month'))]);
                break;
            case 'custom':
                if ($startDate && $endDate) {
                    $query->andWhere(['between', 'DATE(lastUpdate)', $startDate, $endDate]);
                }
                break;
        }
    
        $locations = $query->all();
        $stops = [];
        $lastStop = null;
        $stopsPerDay = [];
    
        foreach ($locations as $location) {
            if ($location->speed == 0) {
                if (!$lastStop) {
                    $lastStop = [
                        'start_time' => $location->lastUpdate,
                        'latitude' => $location->latitude,
                        'longitude' => $location->longitude,
                    ];
                }
            } else {
                if ($lastStop) {
                    $duration = strtotime($location->lastUpdate) - strtotime($lastStop['start_time']);
                    if ($duration > 180) { // 3 minutes = 180 seconds
                        $lastStop['end_time'] = $location->lastUpdate;
                        $lastStop['duration'] = $duration;
                        $stops[] = $lastStop;
    
                        // Contar las paradas por día
                        $date = (new \DateTime($lastStop['start_time']))->format('Y-m-d');
                        if (!isset($stopsPerDay[$date])) {
                            $stopsPerDay[$date] = 0;
                        }
                        $stopsPerDay[$date]++;
    
                        $lastStop = null;
                    }
                }
            }
        }
    
        return $this->render('report_stops', [
            'stops' => $stops,
            'stopsPerDay' => $stopsPerDay,
        ]);
    }

    public function actionDownloadReportStops()
    {
        $filter = Yii::$app->request->get('filter', 'today');
        $gps = Yii::$app->request->get('gps', null);
        $startDate = Yii::$app->request->get('startDate', null);
        $endDate = Yii::$app->request->get('endDate', null);
        $includeChart = Yii::$app->request->get('includeChart', 'true') === 'true';
    
        $query = GpsLocations::find()->orderBy(['lastUpdate' => SORT_ASC]);
        $period = '';
    
        if ($gps) {
            $query->andWhere(['phoneNumber' => $gps]);
        }
    
        switch ($filter) {
            case 'today':
                if ($startDate && $endDate) {
                    $query->where(['between', 'DATE(lastUpdate)', $startDate, $endDate]);
                    $period = $startDate . ' - ' . $endDate;
                } else {
                    $today = date('Y-m-d');
                    $query->where(['DATE(lastUpdate)' => $today]);
                    $period = $today;
                }
                break;
            case 'yesterday':
                $yesterday = date('Y-m-d', strtotime('-1 day'));
                $query->where(['DATE(lastUpdate)' => $yesterday]);
                $period = $yesterday;
                break;
            case 'current_week':
                $startOfWeek = date('Y-m-d', strtotime('monday this week'));
                $endOfWeek = date('Y-m-d', strtotime('sunday this week'));
                $query->where(['between', 'DATE(lastUpdate)', $startOfWeek, $endOfWeek]);
                $period = $startOfWeek . ' - ' . $endOfWeek;
                break;
            case 'last_week':
                $startOfLastWeek = date('Y-m-d', strtotime('monday last week'));
                $endOfLastWeek = date('Y-m-d', strtotime('sunday last week'));
                $query->where(['between', 'DATE(lastUpdate)', $startOfLastWeek, $endOfLastWeek]);
                $period = $startOfLastWeek . ' - ' . $endOfLastWeek;
                break;
            case 'current_month':
                $startOfMonth = date('Y-m-01');
                $endOfMonth = date('Y-m-t');
                $query->where(['between', 'DATE(lastUpdate)', $startOfMonth, $endOfMonth]);
                $period = $startOfMonth . ' - ' . $endOfMonth;
                break;
            case 'last_month':
                $startOfLastMonth = date('Y-m-d', strtotime('first day of last month'));
                $endOfLastMonth = date('Y-m-d', strtotime('last day of last month'));
                $query->where(['between', 'DATE(lastUpdate)', $startOfLastMonth, $endOfLastMonth]);
                $period = $startOfLastMonth . ' - ' . $endOfLastMonth;
                break;
            case 'custom':
                if ($startDate && $endDate) {
                    $query->where(['between', 'DATE(lastUpdate)', $startDate, $endDate]);
                    $period = $startDate . ' - ' . $endDate;
                } else {
                    $period = 'Personalizado';
                }
                break;
            default:
        }
    
        $locations = $query->all();
        $stops = [];
        $lastStop = null;
        $stopsPerDay = [];
        $totalDuration = 0;
    
        foreach ($locations as $location) {
            if ($location->speed == 0) {
                if (!$lastStop) {
                    $lastStop = [
                        'start_time' => $location->lastUpdate,
                        'latitude' => $location->latitude,
                        'longitude' => $location->longitude,
                    ];
                }
            } else {
                if ($lastStop) {
                    $durationInSeconds = strtotime($location->lastUpdate) - strtotime($lastStop['start_time']);
                    if ($durationInSeconds > 180) { // Más de 3 minutos
                        $totalDuration += $durationInSeconds;
                        if ($durationInSeconds >= 3600) {
                            $hours = floor($durationInSeconds / 3600);
                            $minutes = floor(($durationInSeconds % 3600) / 60);
                            $seconds = $durationInSeconds % 60;
                            $lastStop['duration'] = sprintf('%d horas, %d minutos, %d segundos', $hours, $minutes, $seconds);
                        } else {
                            $minutes = floor($durationInSeconds / 60);
                            $seconds = $durationInSeconds % 60;
                            $lastStop['duration'] = sprintf('%d minutos, %d segundos', $minutes, $seconds);
                        }
                        $lastStop['end_time'] = $location->lastUpdate;
                        $stops[] = $lastStop;
    
                        // Contabilizar paradas por día
                        $date = (new \DateTime($lastStop['start_time']))->format('Y-m-d');
                        if (!isset($stopsPerDay[$date])) {
                            $stopsPerDay[$date] = 0;
                        }
                        $stopsPerDay[$date]++;
    
                        $lastStop = null;
                    }
                }
            }
        }
    
        $averageDuration = count($stops) > 0 ? ($totalDuration / count($stops)) : 0;
        $averageStopsPerDay = count($stopsPerDay) > 0 ? (count($stops) / count($stopsPerDay)) : 0;
    
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // Título y subtítulo
        $sheet->setCellValue('A1', 'Reporte de Paradas');
        $sheet->setCellValue('A2', 'Periodo: ' . $period);
        $sheet->mergeCells('A1:E1');
        $sheet->mergeCells('A2:E2');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
    
        // Encabezados de la tabla de paradas
        $sheet->setCellValue('A3', 'Latitud');
        $sheet->setCellValue('B3', 'Longitud');
        $sheet->setCellValue('C3', 'Inicio de Parada');
        $sheet->setCellValue('D3', 'Fin de Parada');
        $sheet->setCellValue('E3', 'Duración');
    
        $row = 4;
        foreach ($stops as $stop) {
            $sheet->setCellValue("A{$row}", $stop['latitude']);
            $sheet->setCellValue("B{$row}", $stop['longitude']);
            $sheet->setCellValue("C{$row}", $stop['start_time']);
            $sheet->setCellValue("D{$row}", $stop['end_time'] ?? 'En curso');
            $sheet->setCellValue("E{$row}", $stop['duration'] ?? 'N/A');
    
            // Aplicar estilo a los encabezados
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF116DA5'],
                ],
            ];
            $sheet->getStyle('A3:E3')->applyFromArray($headerStyle);
    
            $row++;
        }
    
        // Agregar datos de paradas por día
        $sheet->setCellValue('G3', 'Fecha');
        $sheet->setCellValue('H3', 'Número de Paradas');
        $sheet->getStyle('G3:H3')->applyFromArray($headerStyle);
        $row = 4;
        foreach ($stopsPerDay as $date => $count) {
            $sheet->setCellValue('G' . $row, $date);
            $sheet->setCellValue('H' . $row, $count);
            $row++;
        }
    
        // Formatear y agregar duración total y promedio usando la función auxiliar
        $sheet->setCellValue('G' . ($row + 2), 'Duración Total de Paradas');
        $sheet->setCellValue('H' . ($row + 2), $this->formatDuration($totalDuration));
        $sheet->setCellValue('G' . ($row + 3), 'Promedio de Tiempo entre Paradas');
        $sheet->setCellValue('H' . ($row + 3), $this->formatDuration($averageDuration));
        $sheet->setCellValue('G' . ($row + 4), 'Promedio de Paradas por Día');
        $sheet->setCellValue('H' . ($row + 4), number_format($averageStopsPerDay, 2));
        $sheet->getStyle('G' . ($row + 2) . ':G' . ($row + 4))->applyFromArray($headerStyle);
    
        // Ajustar ancho de columnas
        foreach (range('A', 'H') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    
        if ($includeChart) {
            $dataSeriesLabels = [
                new DataSeriesValues('String', 'Worksheet!$H$3', null, 1),
            ];
            $xAxisTickValues = [
                new DataSeriesValues('String', 'Worksheet!$G$4:$G$' . ($row - 1), null, 4),
            ];
            $dataSeriesValues = [
                new DataSeriesValues('Number', 'Worksheet!$H$4:$H$' . ($row - 1), null, 4),
            ];
    
            $series = new DataSeries(
                DataSeries::TYPE_LINECHART,
                DataSeries::GROUPING_STANDARD,
                range(0, count($dataSeriesValues) - 1),
                $dataSeriesLabels,
                $xAxisTickValues,
                $dataSeriesValues
            );
    
            $plotArea = new PlotArea(null, [$series]);
            $legend = new Legend(Legend::POSITION_RIGHT, null, false);
            $title = new Title('Número de Paradas por Día');
            $chart = new Chart(
                'chart1',
                $title,
                $legend,
                $plotArea,
                true,
                0,
                null,
                null
            );
    
            $chart->setTopLeftPosition('K1');
            $chart->setBottomRightPosition('R20');
    
            $sheet->addChart($chart);
        }
    
        $writer = new Xlsx($spreadsheet);
        $writer->setIncludeCharts($includeChart);
        $fileName = 'report_stops.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);
    
        return Yii::$app->response->sendFile($tempFile, $fileName, [
            'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'inline' => false
        ]);
    }
    
    /**
     * Función auxiliar para formatear la duración en segundos al formato deseado.
     *
     * @param int $seconds
     * @return string
     */
    private function formatDuration($seconds)
    {
        if ($seconds >= 3600) {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $seconds = $seconds % 60;
            return sprintf('%d horas, %d minutos, %d segundos', $hours, $minutes, $seconds);
        } else {
            $minutes = floor($seconds / 60);
            $seconds = $seconds % 60;
            return sprintf('%d minutos, %d segundos', $minutes, $seconds);
        }
    }
    
    // Nuevo endpoint para obtener todas las paradas sin paginación
    public function actionGetAllStops()
    {
        $filter = Yii::$app->request->get('filter', 'today');
        $gps = Yii::$app->request->get('gps', null);
        $startDate = Yii::$app->request->get('startDate', null);
        $endDate = Yii::$app->request->get('endDate', null);
    
        $query = GpsLocations::find()->orderBy(['lastUpdate' => SORT_ASC]);
    
        if ($gps) {
            $query->andWhere(['phoneNumber' => $gps]);
        }
    
        switch ($filter) {
            case 'today':
                $query->andWhere(['DATE(lastUpdate)' => date('Y-m-d')]);
                break;
            case 'yesterday':
                $query->andWhere(['DATE(lastUpdate)' => date('Y-m-d', strtotime('-1 day'))]);
                break;
            case 'current_week':
                $query->andWhere(['>=', 'DATE(lastUpdate)', date('Y-m-d', strtotime('monday this week'))]);
                break;
            case 'last_week':
                $query->andWhere(['between', 'DATE(lastUpdate)', date('Y-m-d', strtotime('monday last week')), date('Y-m-d', strtotime('sunday last week'))]);
                break;
            case 'current_month':
                $query->andWhere(['>=', 'DATE(lastUpdate)', date('Y-m-01')]);
                break;
            case 'last_month':
                $query->andWhere(['between', 'DATE(lastUpdate)', date('Y-m-d', strtotime('first day of last month')), date('Y-m-d', strtotime('last day of last month'))]);
                break;
            case 'custom':
                if ($startDate && $endDate) {
                    $query->andWhere(['between', 'DATE(lastUpdate)', $startDate, $endDate]);
                }
                break;
        }
    
        $locations = $query->all();
        $stops = [];
        $lastStop = null;
    
        foreach ($locations as $location) {
            if ($location->speed == 0) {
                if (!$lastStop) {
                    $lastStop = [
                        'start_time' => $location->lastUpdate,
                        'latitude' => $location->latitude,
                        'longitude' => $location->longitude,
                    ];
                }
            } else {
                if ($lastStop) {
                    $duration = strtotime($location->lastUpdate) - strtotime($lastStop['start_time']);
                    if ($duration > 180) { // 3 minutes = 180 seconds
                        $lastStop['end_time'] = $location->lastUpdate;
                        $lastStop['duration'] = $duration;
                        $stops[] = $lastStop;
                        $lastStop = null;
                    }
                }
            }
        }
    
        return $this->asJson($stops);
    }
    
    // Helper para saber si un punto está dentro de la geocerca "capasu"
  private function isInsideCapasu($lat, $lng)
{
    // Obtener la geocerca "capasu" de la base de datos
    $geocerca = \app\models\Geocerca::find()->where(['name' => 'capasu'])->one();
    if (!$geocerca) return false;

    // Si coordinates es un string tipo "lat,lng|lat,lng|lat,lng"
    if (strpos($geocerca->coordinates, '|') !== false) {
        $polygon = [];
        $pairs = explode('|', $geocerca->coordinates);
        foreach ($pairs as $pair) {
            $coords = explode(',', $pair);
            if (count($coords) == 2) {
                $polygon[] = [floatval($coords[0]), floatval($coords[1])];
            }
        }
    } else {
        // Si está en formato JSON
        $polygon = json_decode($geocerca->coordinates, true);
    }
    if (!$polygon || !is_array($polygon)) return false;

    // Algoritmo de punto en polígono (ray-casting)
    $inside = false;
    $j = count($polygon) - 1;
    for ($i = 0; $i < count($polygon); $i++) {
        $xi = $polygon[$i][0];
        $yi = $polygon[$i][1];
        $xj = $polygon[$j][0];
        $yj = $polygon[$j][1];

        if ((($yi > $lng) != ($yj > $lng)) &&
            ($lat < ($xj - $xi) * ($lng - $yi) / (($yj - $yi) ?: 1e-10) + $xi)) {
            $inside = !$inside;
        }
        $j = $i;
    }
    return $inside;
}

    // Nueva función para obtener hora de entrada y salida de la geocerca "capasu"
    public function actionCapasuTimes()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $filter = Yii::$app->request->get('filter', 'today');
        $gps = Yii::$app->request->get('gps', null);
        $startDate = Yii::$app->request->get('startDate', null);
        $endDate = Yii::$app->request->get('endDate', null);

        $query = GpsLocations::find()->orderBy(['lastUpdate' => SORT_ASC]);
        if ($gps) {
            $query->andWhere(['phoneNumber' => $gps]);
        }
        switch ($filter) {
            case 'today':
                $query->andWhere(['DATE(lastUpdate)' => date('Y-m-d')]);
                break;
            case 'yesterday':
                $query->andWhere(['DATE(lastUpdate)' => date('Y-m-d', strtotime('-1 day'))]);
                break;
            case 'current_week':
                $query->andWhere(['>=', 'DATE(lastUpdate)', date('Y-m-d', strtotime('monday this week'))]);
                break;
            case 'last_week':
                $query->andWhere(['between', 'DATE(lastUpdate)', date('Y-m-d', strtotime('monday last week')), date('Y-m-d', strtotime('sunday last week'))]);
                break;
            case 'current_month':
                $query->andWhere(['>=', 'DATE(lastUpdate)', date('Y-m-01')]);
                break;
            case 'last_month':
                $query->andWhere(['between', 'DATE(lastUpdate)', date('Y-m-d', strtotime('first day of last month')), date('Y-m-d', strtotime('last day of last month'))]);
                break;
            case 'custom':
                if ($startDate && $endDate) {
                    $query->andWhere(['between', 'DATE(lastUpdate)', $startDate, $endDate]);
                }
                break;
        }

        $locations = $query->all();
        $result = [];
        $inside = false;
        $entryTime = null;
        $exitTime = null;

        $coordenadasRevisadas = 0; // Contador de coordenadas revisadas

        foreach ($locations as $loc) {
            $coordenadasRevisadas++;
            $isIn = $this->isInsideCapasu($loc->latitude, $loc->longitude);
            if ($isIn && !$inside) {
                // Entró a la geocerca
                $entryTime = $loc->lastUpdate;
                $inside = true;
            } elseif (!$isIn && $inside) {
                // Salió de la geocerca
                $exitTime = $loc->lastUpdate;
                $result[] = [
                    'entrada' => $entryTime,
                    'salida' => $exitTime,
                ];
                $entryTime = null;
                $exitTime = null;
                $inside = false;
            }
        }
        // Si terminó dentro de la geocerca y nunca salió
        if ($inside && $entryTime) {
            $result[] = [
                'entrada' => $entryTime,
                'salida' => null,
            ];
        }

        // Devolver también el número de coordenadas revisadas
        return [
            'capasu_times' => $result,
            'coordenadas_revisadas' => $coordenadasRevisadas,
        ];
    }

    public function actionCombinedReport()
    {
        $filter = Yii::$app->request->get('filter', null);
        $gps = Yii::$app->request->get('gps', null);
        $startDate = Yii::$app->request->get('startDate', null);
        $endDate = Yii::$app->request->get('endDate', null);
        $minStopDuration = Yii::$app->request->get('minStopDuration', 3); // en minutos
        $minStopDurationSeconds = intval($minStopDuration) * 60;

        // Si no hay filtro, renderiza la vista sin datos
        if ($filter === null && $gps === null && $startDate === null && $endDate === null) {
            return $this->render('combined_report');
        }

        // Obtener ubicaciones (ruta)
        $query = GpsLocations::find();
        if ($gps && $gps !== 'all') {
            $query->andWhere(['phoneNumber' => $gps]);
        }
        switch ($filter) {
            case 'today':
                $query->andWhere(['DATE(lastUpdate)' => date('Y-m-d')]);
                break;
            case 'yesterday':
                $query->andWhere(['DATE(lastUpdate)' => date('Y-m-d', strtotime('-1 day'))]);
                break;
            case 'current_week':
                $query->andWhere(['>=', 'DATE(lastUpdate)', date('Y-m-d', strtotime('monday this week'))]);
                break;
            case 'last_week':
                $query->andWhere(['between', 'DATE(lastUpdate)', date('Y-m-d', strtotime('monday last week')), date('Y-m-d', strtotime('sunday last week'))]);
                break;
            case 'current_month':
                $query->andWhere(['>=', 'DATE(lastUpdate)', date('Y-m-01')]);
                break;
            case 'last_month':
                $query->andWhere(['between', 'DATE(lastUpdate)', date('Y-m-d', strtotime('first day of last month')), date('Y-m-d', strtotime('last day of last month'))]);
                break;
            case 'custom':
                if ($startDate && $endDate) {
                    $query->andWhere(['between', 'DATE(lastUpdate)', $startDate, $endDate]);
                } else if ($startDate) {
                    $query->andWhere(['>=', 'DATE(lastUpdate)', $startDate]);
                } else if ($endDate) {
                    $query->andWhere(['<=', 'DATE(lastUpdate)', $endDate]);
                }
                break;
        }
        $query->orderBy(['lastUpdate' => SORT_ASC]);
        $locations = $query->all();

        // Convertir a array plano para JS
        $locationsArr = [];
        foreach ($locations as $loc) {
            $locationsArr[] = [
                'latitude' => $loc->latitude,
                'longitude' => $loc->longitude,
                'lastUpdate' => $loc->lastUpdate,
                'speed' => $loc->speed,
            ];
        }

        // Obtener paradas (igual que en actionGetAllStops, pero como array)
        $stops = [];
        $lastStop = null;
        foreach ($locations as $location) {
            if ($location->speed == 0) {
                if (!$lastStop) {
                    $lastStop = [
                        'start_time' => $location->lastUpdate,
                        'latitude' => $location->latitude,
                        'longitude' => $location->longitude,
                    ];
                }
            } else {
                if ($lastStop) {
                    $duration = strtotime($location->lastUpdate) - strtotime($lastStop['start_time']);
                    if ($duration >= $minStopDurationSeconds) {
                        $lastStop['end_time'] = $location->lastUpdate;
                        $lastStop['duration'] = $duration;
                        $stops[] = $lastStop;
                    }
                    $lastStop = null;
                }
            }
        }

        return $this->render('combined_report', [
            'locations' => $locationsArr,
            'stops' => $stops,
        ]);
    }
}
