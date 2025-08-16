document.addEventListener("DOMContentLoaded", () => {
  // Animaciones fade-in
  document.querySelectorAll(".fade-in").forEach(el => {
    el.style.opacity = "1";
    el.style.transform = "translateY(0)";
  });

  const cartCount = document.getElementById("cart-count");

  // Carga inicial del contador
  actualizarContador();

  document.querySelectorAll(".btn-comprar").forEach(btn => {
    btn.addEventListener("click", () => {
      const nombre = btn.getAttribute("data-nombre");
      const precio = parseFloat(btn.getAttribute("data-precio"));
      const imagen = btn.getAttribute("data-imagen");

      let carrito = JSON.parse(localStorage.getItem("carrito")) || [];

      const index = carrito.findIndex(p => p.nombre === nombre);
      if (index !== -1) {
        carrito[index].cantidad += 1;
      } else {
        carrito.push({
          nombre,
          precio,
          imagen,
          cantidad: 1
        });
      }

      localStorage.setItem("carrito", JSON.stringify(carrito));

      // Cambiar botón y actualizar contador
      btn.textContent = "Añadido";
      btn.disabled = true;

      actualizarContador();
    });
  });

  function actualizarContador() {
    const carrito = JSON.parse(localStorage.getItem("carrito")) || [];
    const totalCantidad = carrito.reduce((acc, item) => acc + item.cantidad, 0);
    if (cartCount) {
      cartCount.textContent = totalCantidad;
    }
  }
});
