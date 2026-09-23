<div class="herramientas-view">
  <form method="POST" action="/mercedes/herramientas" class="herramientas-selector">
    <label>
      <input type="checkbox" name="herramientas[cuaderno]" value="1" id="toggleCuaderno" <?= !empty($herramientasSeleccionadas['cuaderno']) ? 'checked' : '' ?>> Activar Cuaderno
    </label>
    <label>
      <input type="checkbox" name="herramientas[asistencias]" value="1" id="toggleAsistencias" <?= !empty($herramientasSeleccionadas['asistencias']) ? 'checked' : '' ?>> Activar Control de Asistencias
    </label>
    <button type="submit" id="btnGuardar" class="btn-guardar">Guardar selección</button>
  </form>

  <table class="tabla-herramientas">
    <thead>
      <tr>
        <th class="col-vistas">Vistas</th>
        <th class="col-cuaderno">Cuaderno</th>
        <th class="col-asistencias">Control de Asistencias</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td class="vista-item">Login</td>
        <td class="col-cuaderno"><input type="checkbox" checked disabled></td>
        <td class="col-asistencias"><input type="checkbox" checked disabled></td>
      </tr>
      <tr>
        <td class="vista-item">Dashboard</td>
        <td class="col-cuaderno"><input type="checkbox" checked disabled></td>
        <td class="col-asistencias"><input type="checkbox" disabled></td>
      </tr>
      <tr>
        <td class="vista-item">Producción</td>
        <td class="col-cuaderno"><input type="checkbox" checked disabled></td>
        <td class="col-asistencias"><input type="checkbox" disabled></td>
      </tr>
      <tr>
        <td class="vista-item">Llegadas</td>
        <td class="col-cuaderno"><input type="checkbox" disabled></td>
        <td class="col-asistencias"><input type="checkbox" checked disabled></td>
      </tr>
      <tr>
        <td class="vista-item">Riders (Empleados)</td>
        <td class="col-cuaderno"><input type="checkbox" checked disabled></td>
        <td class="col-asistencias"><input type="checkbox" checked disabled></td>
      </tr>
      <tr>
        <td class="vista-item">Punto de control</td>
        <td class="col-cuaderno"><input type="checkbox" disabled></td>
        <td class="col-asistencias"><input type="checkbox" checked disabled></td>
      </tr>
      <tr>
        <td class="vista-item">Empresas</td>
        <td class="col-cuaderno"><input type="checkbox" checked disabled></td>
        <td class="col-asistencias"><input type="checkbox" disabled></td>
      </tr>
      <tr>
        <td class="vista-item">Turnos</td>
        <td class="col-cuaderno"><input type="checkbox" checked disabled></td>
        <td class="col-asistencias"><input type="checkbox" disabled></td>
      </tr>
      <tr>
        <td class="vista-item">Tarifas</td>
        <td class="col-cuaderno"><input type="checkbox" checked disabled></td>
        <td class="col-asistencias"><input type="checkbox" disabled></td>
      </tr>
      <tr>
        <td class="vista-item">Usuarios</td>
        <td class="col-cuaderno"><input type="checkbox" checked disabled></td>
        <td class="col-asistencias"><input type="checkbox" checked disabled></td>
      </tr>
      <tr>
        <td class="vista-item">Herramientas</td>
        <td class="col-cuaderno"><input type="checkbox" checked disabled></td>
        <td class="col-asistencias"><input type="checkbox" checked disabled></td>
      </tr>
      <tr>
        <td class="vista-item">About Dev</td>
        <td class="col-cuaderno"><input type="checkbox" checked disabled></td>
        <td class="col-asistencias"><input type="checkbox" checked disabled></td>
      </tr>
    </tbody>
  </table>
</div>
