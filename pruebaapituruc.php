<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Consulta de Contribuyente</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    #resultado { margin-top: 20px; padding: 10px; border: 1px solid #ccc; }
    table { border-collapse: collapse; width: 100%; margin-top: 10px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
  </style>
</head>
<body>
  <h2>Consulta de Contribuyente</h2>
  
  <label for="consulta">Ingrese RUC/CI o Nombre:</label>
  <input type="text" id="consulta" placeholder="Ej: 2175056-4 o MESA">
  <button id="btnConsultar">Consultar</button>
  
  <div id="resultado">
    <p id="datos">Aquí aparecerán los resultados...</p>
  </div>

  <script>
document.getElementById("btnConsultar").addEventListener("click", async () => {
  const valor = document.getElementById("consulta").value.trim();
  const datosEl = document.getElementById("datos");

  if (!valor) {
    datosEl.textContent = "Por favor ingrese un valor válido.";
    return;
  }

  try {

    console.log('valor: '+ valor);
    console.log('encode: '+ encodeURIComponent(valor));
    const resp = await fetch(`consulta.php?q=${encodeURIComponent(valor)}&page=0`);
    const data = await resp.json();

    if (data.data && data.data.razonSocial) {
      datosEl.textContent = `Nombre: ${data.data.razonSocial} | RUC: ${data.data.ruc} | Estado: ${data.data.estado}`;
    } else if (data.data && Array.isArray(data.data)) {
      if (data.data.length === 0) {
        datosEl.textContent = "No se encontraron resultados.";
      } else {
        datosEl.innerHTML = data.data.map(d =>
          `Nombre: ${d.razonSocial} | RUC: ${d.ruc} | Estado: ${d.estado}`
        ).join("<br>");
      }
    } else {
      datosEl.textContent = "No se encontró el contribuyente.";
    }
    console.log(data);
  } catch (error) {
    datosEl.textContent = "Error en la consulta: " + error.message;
  }
});
</script>


</body>
</html>

