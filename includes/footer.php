        </main>
    </div>

    <!-- Overlay oscuro del Sidebar en móvil -->
    <div id="sidebarOverlay" class="sidebar-overlay"></div>

    <!-- Modal de Confirmación de Eliminación Premium -->

    <div id="deleteModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-icon">
                <i class="ph-bold ph-warning"></i>
            </div>
            <h3 class="modal-title">¿Estás seguro?</h3>
            <p class="modal-text">Esta acción es irreversible y eliminará el registro de forma permanente de la base de datos.</p>
            <div class="modal-buttons">
                <button type="button" id="btnCancelDelete" class="btn-secondary" style="background-color: var(--dark); padding: 12px 18px; border: none; font-size: 15px; border-radius: var(--border-radius);">Cancelar</button>
                <button type="button" id="btnConfirmDelete" class="btn-primary" style="background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-hover) 100%); box-shadow: 0 8px 20px rgba(221, 78, 40, 0.2); padding: 12px 18px; border: none; font-size: 15px;">Eliminar</button>
            </div>
        </div>
    </div>

    <!-- Scripts del Sistema con Cache Busting para desarrollo continuo -->
    <script src="js/app.js?v=<?php echo time(); ?>"></script>
</body>
</html>
