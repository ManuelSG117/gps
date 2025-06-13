<?php
/**
 * Vista parcial para mostrar las tarjetas de estadísticas de ruta
 * 
 * @var array $locations Datos de ubicaciones GPS
 */
?>

<!-- Panel de estadísticas de la ruta -->
<div id="route-stats-cards" class="mb-3" style="display:none;">
    <div class="row">
        <div class="col-lg-4 col-md-6 col-12 mb-3">
            <div class="cards">
            <script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>
            <dotlottie-player src="https://lottie.host/a4d8bcfe-4f96-46f7-8e9a-c51ff3161d86/A1EjjvJDpj.lottie" background="transparent" speed="1" style="width: 300px; height: 300px" loop autoplay></dotlottie-player>                <div class="card__content">
                    <p class="card__title">Distancia Total Recorrida</p>
                    <p class="card__description stat-value" id="stat-distance">-</p>
                    <p class="card__description">Suma de todos los tramos recorridos en la ruta seleccionada.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-12 mb-3">
            <div class="cards">
                <dotlottie-player src="https://lottie.host/f544da77-a18b-488d-87de-e401b849c7f6/G4GdIXpZMa.lottie" background="transparent" speed="1" style="width: 100%; height: 100%; position: absolute; top: 0; left: 0; z-index: 0;" loop autoplay></dotlottie-player>
                <div class="card__content">
                    <p class="card__title">Velocidad Promedio</p>
                    <p class="card__description stat-value" id="stat-avg-speed">-</p>
                    <p class="card__description">Promedio de velocidad durante toda la ruta.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-12 mb-3">
            <div class="cards">
            <script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>
            <dotlottie-player src="https://lottie.host/e2a36049-c014-4dd9-95b3-31c91131ee91/aMVfH9bxoS.lottie" background="transparent" speed="1" style="width: 300px; height: 300px" loop autoplay></dotlottie-player>            <div class="card__content">
                    <p class="card__title">Duración Total</p>
                    <p class="card__description stat-value" id="stat-duration">-</p>
                    <p class="card__description">Tiempo transcurrido desde el primer hasta el último punto de la ruta.</p>
                </div>
            </div>
        </div>
    </div>
</div>