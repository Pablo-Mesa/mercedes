<?php
$cssPath = __DIR__ . '/../../public/css/pdf_produccion.css';
$css = file_exists($cssPath) ? file_get_contents($cssPath) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Producción - <?= htmlspecialchars($fecha) ?></title>
    <style><?= $css ?></style>
</head>
<body>

    <div class="header">
        <table>
            <tr>
                <td>
                    <?php if (!empty($logoBase64)): ?>
                        <img src="<?= $logoBase64 ?>" class="logo" alt="Logo">                        
                    <?php else: ?>
                        <strong style="font-size: 13px; color: #2c3e50;">DETALLE DE PRODUCCIÓN</strong>
                    <?php endif; ?>
                </td>
                <td class="titulo">
                    <h1>Detalle de Producción por Rider</h1>
                    <p>
                        Fecha: <strong><?= date('d/m/Y', strtotime($fecha)) ?></strong>
                        <?php if ($turno): ?>
                            | Turno: <strong><?= $turno === 1 ? 'Diurno' : 'Nocturno' ?></strong>
                        <?php endif; ?>
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <?php if (!empty($produccionesResumen)): ?>
        <?php foreach ($produccionesResumen as $grupo): ?>
            <?php if ($_SESSION['user_role'] === 'admin' || $grupo['grupo_id'] == $_SESSION['grupo_id']): ?>
                <?php
                    $sumaTotalTarifaGrupo = 0;
                    $totalEnviosGrupo = 0;
                    $filasRiders = array_chunk($grupo['riders'], 3);
                ?>

                <div class="grupo-bloque">
                    <div class="grupo-header">
                        <?php if (!empty($grupo['grupo_icono_base64'])): ?>
                            <img src="<?= $grupo['grupo_icono_base64'] ?>" class="grupo-icono" alt="">
                        <?php endif; ?>
                        <?= htmlspecialchars($grupo['grupo_nombre']) ?>
                    </div>

                    <table class="riders-grid">
                        <?php foreach ($filasRiders as $fila): ?>
                            <tr>
                                <?php foreach ($fila as $p): ?>
                                    <?php
                                        $sumaTotalTarifaGrupo += (float)$p['total_tarifa'];
                                        $totalEnviosGrupo += count($p['tarifas']);
                                    ?>
                                    <td class="rider-card">
                                        <div class="rider-nombre"><?= htmlspecialchars($p['rider_nombre']) ?></div>

                                        <div class="seccion-label">Facturas (<?= count($p['facturas']) ?>)</div>
                                        <table class="mini-table">
                                            <thead>
                                                <tr>
                                                    <th class="text-right" style="width: 25%;">Monto</th>                                                    
                                                    <th class="text-center" style="width: 25%;">Rend.</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($p['facturas'])): ?>
                                                    <?php foreach ($p['facturas'] as $f): ?>
                                                        <tr>
                                                            <td class="text-right">Gs. <?= number_format((float)$f['monto'], 0, ',', '.') ?></td>                                                            
                                                            <td class="text-center">
                                                                <?php if (!empty($f['rendicion'])): ?>
                                                                    <span class="estado-ok">OK</span>
                                                                <?php else: ?>
                                                                    <span class="estado-pend">Pend.</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr><td colspan="3" class="text-center">Sin facturas</td></tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>

                                        <div class="seccion-label">Tarifas (<?= count($p['tarifas']) ?>)</div>
                                        <table class="mini-table">
                                            <thead>
                                                <tr>
                                                    <th class="text-right" style="width: 25%;">#</th>
                                                    <th class="text-center" style="width: 25%;">Costo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($p['tarifas'])): ?>
                                                    <?php $i = 1; ?>
                                                    <?php foreach ($p['tarifas'] as $t): ?>
                                                        <tr>
                                                            <td class="text-right"><?= $i++ ?></td>
                                                            <td class="text-center">Gs. <?= number_format((float)$t['costo'], 0, ',', '.') ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr><td colspan="2" class="text-center">Sin tarifas</td></tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>

                                        <div class="rider-total">
                                            Producción: <strong>Gs. <?= number_format((float)$p['total_tarifa'], 0, ',', '.') ?></strong>
                                        </div>
                                    </td>
                                <?php endforeach; ?>

                                <?php for ($v = count($fila); $v < 3; $v++): ?>
                                    <td class="rider-card-empty"></td>
                                <?php endfor; ?>
                            </tr>
                        <?php endforeach; ?>
                    </table>

                    <div class="grupo-resumen">
                        Resumen <?= htmlspecialchars($grupo['grupo_nombre']) ?> &mdash;
                        Envíos: <?= $totalEnviosGrupo ?> &nbsp;|&nbsp;
                        Total Producción: Gs. <?= number_format($sumaTotalTarifaGrupo, 0, ',', '.') ?>
                    </div>
                </div>

            <?php endif; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align: center; padding: 20px; color: #666;">
            No existen registros de producción para la fecha seleccionada.
        </p>
    <?php endif; ?>

    <div class="footer">
        Generado el <?= date('d/m/Y H:i:s') ?>
    </div>

</body>
</html>
