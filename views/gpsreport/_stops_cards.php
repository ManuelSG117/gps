<?php
/**
 * Partial view for displaying stop report cards
 * 
 * @var array $stops All stops data
 * @var array $stopsPerDay Stops grouped by day
 */

// Hacer disponible la variable stops para JavaScript
$this->registerJs("var stops = " . json_encode($stops) . ";", yii\web\View::POS_HEAD);
?>

<div id="cards-container" class="show">
    <div class="row">
        <div class="col-lg-4 col-md-6 col-12">
            <div class="cards">
                <dotlottie-player src="https://lottie.host/ed84083f-f28a-4a85-829b-b9c3e6d57d3d/9jQnEiULKi.lottie" background="transparent" speed="1" style="width: 350px; height: 250px" loop autoplay></dotlottie-player>
                <div class="card__content">
                    <p class="card__title">Duración Total de Paradas</p>
                    <p class="card__description">
                        <?php
                        $totalDuration = array_sum(array_column($stops, 'duration'));
                        if ($totalDuration >= 3600) {
                            $hours = floor($totalDuration / 3600);
                            $minutes = floor(($totalDuration % 3600) / 60);
                            $seconds = $totalDuration % 60;
                            echo sprintf('%d horas, %d minutos, %d segundos', $hours, $minutes, $seconds);
                        } else {
                            $minutes = floor($totalDuration / 60);
                            $seconds = $totalDuration % 60;
                            echo sprintf('%d minutos, %d segundos', $minutes, $seconds);
                        }
                        ?>
                    </p>
                    <p class="card__description">
                    <?php
                    $averageDuration = count($stops) > 0 ? ($totalDuration / count($stops)) : 0;
                    if ($averageDuration >= 3600) {
                        $hours = floor($averageDuration / 3600);
                        $minutes = floor(($averageDuration % 3600) / 60);
                        $seconds = $averageDuration % 60;
                        echo sprintf('Promedio de tiempo detenido: %d horas, %d minutos, %d segundos', $hours, $minutes, $seconds);
                    } else {
                        $minutes = floor($averageDuration / 60);
                        $seconds = $averageDuration % 60;
                        echo sprintf('Promedio de tiempo detenido: %d minutos, %d segundos', $minutes, $seconds);
                    }
                    ?>
                </p>
                    <div id="icon-container">
                    <dotlottie-player src="https://lottie.host/a9b6f0ca-e88b-4420-8575-7202b711f122/ohDvpoaorb.lottie" background="transparent" speed="1" style="width: 80px; height: 80px" loop autoplay></dotlottie-player>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 col-12">
            <div class="cards">
                <dotlottie-player src="https://lottie.host/06c57bb2-0963-4084-bcc9-05e6e5156d90/4U8QhsebpY.lottie" background="transparent" speed="1" style="width: 170px; height: 300px" loop autoplay></dotlottie-player>
                <div class="card__content">
                    <p class="card__title">Total de Paradas Registradas</p>
                    <p class="card__description">Total de paradas: <?= count($stops) ?></p>
                    <p class="card__description">
                        <?php
                        $totalDays = count($stopsPerDay);
                        $averageStops = $totalDays > 0 ? count($stops) / $totalDays : 0;
                        echo sprintf('Promedio de paradas registradas por día: %.2f', $averageStops);
                        ?>
                    </p>
                    <div id="icon-container">
                    <dotlottie-player src="https://lottie.host/ed2373fa-ca39-42e0-8da8-dbdabc4769b4/AuM0umjYJQ.lottie" background="transparent" speed="2" style="width: 100px; height: 76px" loop autoplay></dotlottie-player>
                     </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-12">
            <!-- Contenedor del gráfico con animación de carga superpuesta -->
            <div id="chart-wrapper">
                <!-- Div donde se renderiza la gráfica -->
                <div id="stops-chart" style="height: 200px;"></div>
                
                <!-- Animación de carga (overlay) -->
                <div id="loading-animation" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
                    display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.8);">
                    <dotlottie-player src="https://lottie.host/e0f85e03-ec5a-4435-a7ee-30dc93809080/92ftoDoc5w.lottie" background=" linear-gradient(to bottom, #1e3c72, #2a5298);" speed="1" style="width: 100%; height: 100%;" loop autoplay></dotlottie-player>
                </div>
            </div>
        </div>
    </div>
</div>