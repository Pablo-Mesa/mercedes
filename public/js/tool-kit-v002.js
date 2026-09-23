/* cubo */
function drawCube(element, title, tamanho){    

    const donde_se_agrega = document.querySelector(element) || document.getElementById(element);
        
    const membrete = document.createElement('div');
    membrete.setAttribute("class", "header-title");
    
    const container_cube = document.createElement('div');
    container_cube.setAttribute("class", "container-cube");
    
    const cube = document.createElement('div');
    cube.setAttribute("class", "cube");
    cube.style.setProperty('--ancho', tamanho);
    cube.style.setProperty('--alto', tamanho);
    
    const cara_uno = document.createElement('div');
    cara_uno.setAttribute("id", "uno");
    cara_uno.setAttribute("class", "cara");
    
    const cara_dos = document.createElement('div');
    cara_dos.setAttribute("id", "dos");
    cara_dos.setAttribute("class", "cara");
    
    const cara_tres = document.createElement('div');
    cara_tres.setAttribute("id", "tres");
    cara_tres.setAttribute("class", "cara");

    const cara_cuatro = document.createElement('div');
    cara_cuatro.setAttribute("id", "cuatro");
    cara_cuatro.setAttribute("class", "cara");
    
    const cara_cinco = document.createElement('div');
    cara_cinco.setAttribute("id", "cinco");
    cara_cinco.setAttribute("class", "cara");
    
    const cara_seis = document.createElement('div');
    cara_seis.setAttribute("id", "seis");
    cara_seis.setAttribute("class", "cara");
        
    cube.appendChild(cara_uno);    
    cube.appendChild(cara_dos);
    cube.appendChild(cara_tres);
    cube.appendChild(cara_cuatro);
    cube.appendChild(cara_cinco);
    cube.appendChild(cara_seis);
    container_cube.appendChild(cube);
    
    const container_title = document.createElement('div');
    container_title.setAttribute("class", "header-h");
    
    const titleH1 = document.createElement('h1');
    titleH1.textContent = 'Solver';
    const titleH3 = document.createElement('h3');
    titleH3.textContent = 'Desarrollo de soluciones';
    titleH3.setAttribute("id", "idUsuarioActivo");
    
    container_title.appendChild(titleH1);
    container_title.appendChild(titleH3);
    
    membrete.appendChild(container_cube);
    
    if(title) membrete.appendChild(container_title);
    
    donde_se_agrega.appendChild(membrete);
    
}
//FIN cubo

/* showToast = mensaje temporal aparaece en la zona del footer */
function showToast(message, color){    
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.style.backgroundColor = color;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
      toast.remove();
    }, 3000);
}
//FIN showToast

/* card */
function card(donde, ruta_imagen, descripcion, precio, codigo){
    
    //obtener referencia del elemento contenedor de las tarjetas
    const tarjetero = document.getElementById(donde);
    //
    const tarjeta = document.createElement('div');
    tarjeta.className = 'menu-card-basic';      
    //contenedor img
    const contenedor_imagen = document.createElement('div');
    contenedor_imagen.className = 'item-imagen';
    //img
    const item_imagen = document.createElement('img');
    item_imagen.src = ruta_imagen;
    //descripcion
    const item_name = document.createElement('h3');
    item_name.textContent = descripcion;
    //contenedor controles
    const contenedor_control = document.createElement('div');
    contenedor_control.className = 'contenedor-control';    
    const contenedor_precio_2 = document.createElement('div');    
    const item_precio_2 = document.createElement('span');
    item_precio_2.textContent = 'Gs. ' + formatearNumero(precio);
    contenedor_precio_2.appendChild(item_precio_2);
    contenedor_control.appendChild(contenedor_precio_2);
    //btn agregar
    const boton_agregar = document.createElement('button');
    boton_agregar.textContent = 'Agregar';    
    boton_agregar.setAttribute('onclick', 'addToCart("' + codigo + '")');
    
    contenedor_control.appendChild(boton_agregar);    
    //agregar imagen al contenedor
    contenedor_imagen.appendChild(item_imagen);
    //agregar contenedor imagen a tarjeta
    tarjeta.appendChild(contenedor_imagen);
    //agregar item name
    tarjeta.appendChild(item_name);        
    //agregar contenedor control
    tarjeta.appendChild(contenedor_control);
    //agregar tarjeta al tarjetero
    tarjetero.appendChild(tarjeta);
    
}
// FIN card

/* item (into a cart) */
function item(donde){
    
    //obtener referencia del elemento contenedor de las tarjetas
    const tarjetero = document.getElementById(donde);
    
    /*<div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: ${config.surface_color}; border-radius: 8px; margin-bottom: 8px;">
        <div style="flex: 1;">
            <div style="font-weight: 600; color: ${config.text_color}; margin-bottom: 4px;">${item.emoji} ${item.name}</div>
            <div style="color:${config.secondary_action_color}; font-weight: 600;">Gs.${item.price.toLocaleString()}</div>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <button onclick="updateQuantity('${item.id}', -1)" style="width: 28px; height: 28px; background: ${config.secondary_action_color}; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">-</button>
            <span style="min-width: 24px; text-align: center; font-weight: 600; color:${config.text_color};">${item.quantity}</span>
            <button onclick="updateQuantity('${item.id}', 1)" style="width: 28px; height: 28px; background: ${config.primary_action_color}; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">+</button>
        </div>
    </div>*/
    
}
// FIN item

function atleast(){
    alert('-> sigue funcionando la salida...');
}