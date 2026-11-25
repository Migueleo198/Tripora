<?php
// Barra de búsqueda para las páginas con elementos filtrables (data-search-item).
// Al escribir filtra la página (viajes/ui/searchBar.js, que la vista añade a $scripts);
// sin JS se envía a la misma página con ?q= (la página principal filtra en el servidor).
// Opcional: $placeholderBusqueda con el texto del campo
?>
<form class="tripora-search mb-4" role="search" method="get" data-trip-search>
    <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input class="form-control"
               type="search"
               name="q"
               value="<?php echo e($_GET['q'] ?? ''); ?>"
               placeholder="<?php echo e($placeholderBusqueda ?? 'Buscar...'); ?>"
               aria-label="<?php echo e($placeholderBusqueda ?? 'Buscar'); ?>"
               autocomplete="off"
               data-trip-search-input>
        <button class="btn btn-primary" type="submit">Buscar</button>
    </div>
</form>
