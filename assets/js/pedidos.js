let carrito = [];

function agregar(id, nombre, precio) {

    let item = carrito.find(p => p.id == id);

    if (item) {
        item.cantidad++;
    } else {
        carrito.push({id, nombre, precio, cantidad: 1});
    }

    render();
}

function render() {
    let tabla = document.querySelector("#tabla tbody");
    tabla.innerHTML = "";

    let total = 0;

    carrito.forEach((p, i) => {
        let subtotal = p.precio * p.cantidad;
        total += subtotal;

        tabla.innerHTML += `
            <tr>
                <td>${p.nombre}</td>
                <td>${p.precio}</td>
                <td>${p.cantidad}</td>
                <td>${subtotal}</td>
                <td><button onclick="eliminar(${i})">X</button></td>
            </tr>
        `;
    });

    document.getElementById("total").innerText = total.toFixed(2);
}

function eliminar(i) {
    carrito.splice(i, 1);
    render();
}

function guardarPedido() {

    let sala = document.getElementById("sala").value;
    let mesa = document.getElementById("mesa").value;

    fetch("../controllers/pedidosController.php", {
        method: "POST",
        body: JSON.stringify({
            sala,
            mesa,
            carrito
        })
    })
    .then(res => res.text())
    .then(res => {
        alert("Pedido guardado");
        carrito = [];
        render();
    });
}