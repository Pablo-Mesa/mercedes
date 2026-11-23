<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Producción - <?= htmlspecialchars($fecha) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 15px;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header table {
            width: 100%;
            border: none;
            margin: 0;
        }
        .header td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        .logo {
            max-width: 110px;
            max-height: 60px;
        }
        .titulo {
            text-align: right;
        }
        .titulo h1 {
            margin: 0;
            font-size: 18px;
            color: #2c3e50;
        }
        .titulo p {
            margin: 4px 0 0 0;
            font-size: 12px;
            color: #666;
        }
        .grupo-bloque {
            margin-bottom: 22px;
            page-break-inside: avoid;
        }
        .grupo-titulo {
            background-color: #2c3e50;
            color: #ffffff;
            padding: 7px 10px;
            font-weight: bold;
            font-size: 13px;
        }
        table.tabla-datos {
            width: 100%;
            border-collapse: collapse;
        }
        table.tabla-datos th,
        table.tabla-datos td {
            border: 1px solid #d0d7de;
            padding: 7px 10px;
        }
        table.tabla-datos th {
            background-color: #f4f6f8;
            text-align: left;
            font-weight: bold;
            color: #2c3e50;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .subtotal-row {
            background-color: #eef2f6;
            font-weight: bold;
        }
        .total-general {
            margin-top: 20px;
            padding: 12px;
            background-color: #f8f9fa;
            border: 1px solid #2c3e50;
            text-align: right;
            font-size: 14px;
            font-weight: bold;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #888;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    <div class="header">
        <table>
            <tr>
                <td>
                    <?php if (!empty($logoBase64)): ?>
                        <img src="<?= $logoBase64 ?>" class="logo" alt="Logo">
                    <?php else: ?>
                        <strong>SISTEMA DE PRODUCCIÓN</strong>
                    <?php endif; ?>
                </td>
                <td class="titulo">
                    <h1>Reporte Diario de Producción</h1>
                    <p>Fecha del reporte: <strong><?= date('d/m/Y', strtotime($fecha)) ?></strong></p>
                </td>
            </tr>
        </table>
    </div>

    <?php 
        $totalServiciosGeneral = 0;
        $totalImporteGeneral = 0;
    ?>

    <?php if (!empty($resumenGrupos)): ?>
        <?php foreach ($resumenGrupos as $grupo): ?>
            <?php
                $totalServiciosGeneral += (int)$grupo['servicios'];
                $totalImporteGeneral += (float)$grupo['importe'];
            ?>
            <div class="grupo-bloque">
                <div class="grupo-titulo">
                    Grupo: <?= htmlspecialchars($grupo['grupo_nombre']) ?>
                </div>
                <table class="tabla-datos">
                    <thead>
                        <tr>
                            <th>Rider</th>
                            <th class="text-center" style="width: 25%;">Cantidad de Servicios</th>
                            <th class="text-right" style="width: 30%;">Importe Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grupo['riders'] as $rider): ?>
                        <tr>
                            <td><?= htmlspecialchars($rider['nombre']) ?></td>
                            <td class="text-center"><?= (int)$rider['servicios'] ?></td>
                            <td class="text-right">Gs. <?= number_format((float)$rider['importe'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="subtotal-row">
                            <td>Subtotal <?= htmlspecialchars($grupo['grupo_nombre']) ?></td>
                            <td class="text-center"><?= (int)$grupo['servicios'] ?></td>
                            <td class="text-right">Gs. <?= number_format((float)$grupo['importe'], 0, ',', '.') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>

        <div class="total-general">
            Total Servicios: <?= $totalServiciosGeneral ?> &nbsp;|&nbsp; 
            Total General: Gs. <?= number_format($totalImporteGeneral, 0, ',', '.') ?>
        </div>

    <?php else: ?>
        <p>No existen registros de producción para la fecha seleccionada.</p>
    <?php endif; ?>

    <div class="footer">
        Generado automáticamente el <?= date('d/m/Y H:i') ?>
    </div>

</body>
</html>
