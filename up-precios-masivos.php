<?php
/**
 * Plugin Name:       Precios Masívos
 * Plugin URI:        https://gudfy.com
 * Description:       Actualización masiva de precios.
 * Version:           1.0
 * Author:            Ing Carlos Garzón
 */
// Define la función principal para cambiar precios
function cambiar_precio_variaciones($cambios_precios, $trm_personalizada, $comision_producto) {
    foreach ($cambios_precios as $cambio) {
        $product_id = $cambio['product_id'];
        $variation_id = $cambio['variation_id'];
        $nuevo_precio = $cambio['nuevo_precio'];

        if (post_type_exists('product') && get_post_type($product_id) === 'product' && get_post_type($variation_id) === 'product_variation') {
            $nuevo_precio /= $trm_personalizada;
            $nuevo_precio += ($nuevo_precio * $comision_producto) / 100;

            update_post_meta($variation_id, '_price', $nuevo_precio);
            update_post_meta($variation_id, '_regular_price', $nuevo_precio);

            wc_delete_product_transients($product_id);

            wp_update_post(array('ID' => $product_id, 'post_modified' => current_time('mysql')));
        }
    }
}

$cambios_precios = array(
    array('product_id' => 3431, 'variation_id' => 245517, 'nuevo_precio' => 4200), //Precio de producto sin puntos.  
    array('product_id' => 3431, 'variation_id' => 245519, 'nuevo_precio' => 12000),
    array('product_id' => 18342, 'variation_id' => 273747, 'nuevo_precio' => 2.16),
);

// Función para mostrar la página de configuración en el panel de administración
function custom_pricing_plugin_menu() {
    add_menu_page(
        'Configuración de Precios Personalizados',
        'Precios Personalizados',
        'manage_options',
        'custom-pricing-settings',
        'custom_pricing_settings_page'
    );
}

// Función para mostrar el contenido de la página de configuración
function custom_pricing_settings_page() {
    // Verificar si el formulario se ha enviado
    if (isset($_POST['guardar_cambios'])) {
        // Obtener los valores de las variables desde el formulario y sanitizarlos
        $trm_personalizada = floatval(sanitize_text_field($_POST['trm_personalizada']));
        $comision_producto = floatval(sanitize_text_field($_POST['comision_producto']));

        // Guardar los cambios en las opciones de WordPress
        update_option('trm_personalizada', $trm_personalizada);
        update_option('comision_producto', $comision_producto);

        echo '<div class="updated"><p>Configuración guardada correctamente.</p></div>';
    }

    // Obtener los valores actuales de las variables desde las opciones de WordPress
    $trm_personalizada = get_option('trm_personalizada', 0);
    $comision_producto = get_option('comision_producto', 0);

    // Mostrar el formulario de configuración
    ?>
    <div class="wrap">
        <h2>Configuración de Precios Personalizados</h2>
        <form method="post" action="">
            <label for="trm_personalizada">TRM Personalizada:</label>
            <input type="text" name="trm_personalizada" value="<?php echo esc_attr($trm_personalizada); ?>" required><br>

            <label for="comision_producto">Comisión del Producto (%):</label>
            <input type="text" name="comision_producto" value="<?php echo esc_attr($comision_producto); ?>" required><br>

            <input type="submit" name="guardar_cambios" class="button button-primary" value="Guardar Cambios">
        </form>
    </div>
    <?php
}

// Agregar acción para cargar el menú en el panel de administración
add_action('admin_menu', 'custom_pricing_plugin_menu');