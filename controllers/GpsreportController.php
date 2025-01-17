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
    
        // Filtrar datos según el filtro seleccionado
        switch ($filter) {
            case 'today':
                if ($startDate && $endDate) {
                    $query->where(['between', 'DATE(lastUpdate)', $startDate, $endDate]);
                }
                break;
            case 'yesterday':
                $query->where(['DATE(lastUpdate)' => date('Y-m-d', strtotime('-1 day'))]);
                break;
            case 'current_week':
                $query->where(['>=', 'DATE(lastUpdate)', date('Y-m-d', strtotime('monday this week'))]);
                break;
            case 'last_week':
                $query->where(['between', 'DATE(lastUpdate)', date('Y-m-d', strtotime('monday last week')), date('Y-m-d', strtotime('sunday last week'))]);
                break;
            case 'current_month':
                $query->where(['>=', 'DATE(lastUpdate)', date('Y-m-01')]);
                break;
            case 'last_month':
                $query->where(['between', 'DATE(lastUpdate)', date('Y-m-d', strtotime('first day of last month')), date('Y-m-d', strtotime('last day of last month'))]);
                break;
            case 'custom':
                if ($startDate && $endDate) {
                    $query->where(['between', 'DATE(lastUpdate)', $startDate, $endDate]);
                }
                break;
            default:
                return $this->redirect(['index']);
        }
    
    
        $locations = $query->all();
    
        // Crear un archivo Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        // Definir encabezados
        $sheet->setCellValue('A1', 'Latitud');
        $sheet->setCellValue('B1', 'Longitud');
        $sheet->setCellValue('C1', 'Fecha');
        $sheet->setCellValue('D1', 'Velocidad');
        $sheet->setCellValue('E1', 'Dirección'); // Nueva columna para el enlace a Google Maps
    
        // Agregar los datos al archivo Excel
        $row = 2;
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
        $sheet->setCellValue('G1', 'Fecha Velocidad Media');
        $sheet->setCellValue('H1', 'Velocidad Media');
        $row = 2;
        foreach ($dailySpeeds as $date => $data) {
            $averageSpeed = $data['totalSpeed'] / $data['count'];
            $sheet->setCellValue('G' . $row, $date);
            $sheet->setCellValue('H' . $row, $averageSpeed);
            $row++;
        }
    
        if ($includeChart) {
            // Crear una serie de datos para la gráfica
            $dataSeriesLabels = [
                new DataSeriesValues('String', 'Worksheet!$H$1', null, 1), // Etiqueta de la serie
            ];
            $xAxisTickValues = [
                new DataSeriesValues('String', 'Worksheet!$G$2:$G$' . ($row - 1), null, 4), // Valores del eje X (Fechas)
            ];
            $dataSeriesValues = [
                new DataSeriesValues('Number', 'Worksheet!$H$2:$H$' . ($row - 1), null, 4), // Valores del eje Y (Velocidades Medias)
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
