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
    
    public function actionIndex()
    {
        $filter = Yii::$app->request->get('filter', 'today');
        $gps = Yii::$app->request->get('gps', null);
        $startDate = Yii::$app->request->get('startDate', null);
        $endDate = Yii::$app->request->get('endDate', null);
    
        // Consulta principal
        $query = GpsLocations::find();
    
        if ($gps) {
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
                }
                break;
        }
    
        $locations = $query->all();
    
        return $this->render('index', [
            'locations' => $locations,
        ]);
    }   

    public function actionDownloadReport($filter = 'today', $startDate = null, $endDate = null, $includeChart = true)
    {
        $query = GpsLocations::find();
        $period = '';

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
        $sheet->setCellValue('E3', 'Dirección'); // Nueva columna para el enlace a Google Maps
    
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
    
        return Yii::$app->response->sendFile($tempFile)->on(Response::EVENT_AFTER_SEND, function () use ($tempFile) {
            unlink($tempFile); // Eliminar el archivo temporal después de enviarlo
        });
    }
    

}
