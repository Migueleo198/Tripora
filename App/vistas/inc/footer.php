<footer class="container py-4 text-center text-muted">
    <small>&copy; <?php echo date('Y'); ?> Tripora</small>
</footer>

<!-- Modal de confirmación compartido (lo usa js/comun/confirmar.js con <form data-confirmar="...">) -->
<div class="modal fade" id="modalConfirmar" tabindex="-1" aria-labelledby="modalConfirmarTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConfirmarTitulo">Confirmar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" data-modal-mensaje></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" data-modal-aceptar>Aceptar</button>
            </div>
        </div>
    </div>
</div>
<script src="<?php echo RUTA_URL; ?>/js/comun/confirmar.js"></script>

<?php // Cada vista indica sus módulos JS en $scripts antes de incluir el footer ?>
<?php foreach ($scripts ?? [] as $script): ?>
    <script type="module" src="<?php echo RUTA_URL; ?>/js/<?php echo $script; ?>"></script>
<?php endforeach; ?>

</body>
</html>
