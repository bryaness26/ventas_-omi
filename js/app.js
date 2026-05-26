// Lógica interactiva para la aplicación Ñomi
document.addEventListener('DOMContentLoaded', function() {
    
    // --- 1. MENÚ RESPONSIVO MÓVIL ---
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('active');
        });
        
        // Cerrar menú al hacer click fuera del sidebar en móvil
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768 && sidebar.classList.contains('active')) {
                if (!sidebar.contains(e.target) && e.target !== menuToggle) {
                    sidebar.classList.remove('active');
                }
            }
        });
    }

    // --- 2. CÁLCULO DINÁMICO DE VENTAS ---
    const productoSelect = document.getElementById('producto');
    const cantidadInput = document.getElementById('cantidad');
    const montoTotalInput = document.getElementById('monto_total');
    
    if (productoSelect && cantidadInput && montoTotalInput) {
        function calcularTotalVenta() {
            const producto = productoSelect.value;
            const cantidad = parseInt(cantidadInput.value) || 0;
            let precioUnitario = 0;
            
            if (producto === 'Pequeña') {
                precioUnitario = 2.00;
            } else if (producto === 'Grande') {
                precioUnitario = 5.00;
            }
            
            const total = precioUnitario * cantidad;
            montoTotalInput.value = total.toFixed(2);
        }
        
        productoSelect.addEventListener('change', calcularTotalVenta);
        cantidadInput.addEventListener('input', calcularTotalVenta);
    }

    // --- 3. CALCULADORA DE DIVISAS (BCV) ---
    const calcUsd = document.getElementById('calcUsd');
    const calcVes = document.getElementById('calcVes');
    const tasaBcvInput = document.getElementById('tasaBcv');
    
    if (calcUsd && calcVes && tasaBcvInput) {
        const tasa = parseFloat(tasaBcvInput.value) || 0;
        
        if (tasa > 0) {
            // Conversión de USD a VES
            calcUsd.addEventListener('input', function() {
                const usdVal = parseFloat(calcUsd.value);
                if (!isNaN(usdVal)) {
                    calcVes.value = (usdVal * tasa).toFixed(2);
                } else {
                    calcVes.value = '';
                }
            });
            
            // Conversión de VES a USD
            calcVes.addEventListener('input', function() {
                const vesVal = parseFloat(calcVes.value);
                if (!isNaN(vesVal)) {
                    calcUsd.value = (vesVal / tasa).toFixed(2);
                } else {
                    calcUsd.value = '';
                }
            });
        }
    }

    // --- 4. MODAL DE CONFIRMACIÓN DE ELIMINACIÓN PERSONALIZADO ---
    const deleteModal = document.getElementById('deleteModal');
    const btnCancelDelete = document.getElementById('btnCancelDelete');
    const btnConfirmDelete = document.getElementById('btnConfirmDelete');
    let activeDeleteForm = null;

    // Escuchar los envíos de formularios de eliminación con clase 'delete-form'
    document.addEventListener('submit', function(e) {
        if (e.target && e.target.classList.contains('delete-form')) {
            e.preventDefault(); // Detener envío inmediato del formulario
            activeDeleteForm = e.target; // Registrar formulario que gatilló la acción
            deleteModal.classList.add('active'); // Mostrar modal con transiciones
        }
    });

    if (btnCancelDelete && deleteModal) {
        btnCancelDelete.addEventListener('click', function() {
            deleteModal.classList.remove('active');
            activeDeleteForm = null;
        });
    }

    if (btnConfirmDelete && deleteModal) {
        btnConfirmDelete.addEventListener('click', function() {
            if (activeDeleteForm) {
                activeDeleteForm.submit(); // Realizar el envío seguro
            }
            deleteModal.classList.remove('active');
        });
    }
});
