<?php
/*
Plugin Name: Mon Formulaire de Contact
Description: Un plugin de formulaire de contact personnalisable.
Version: 1.0
Author: Votre Nom
*/

// Enqueue CSS styles for the front end.
function mfc_enqueue_styles() {
    wp_enqueue_style('mfc-style', plugins_url('/style.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'mfc_enqueue_styles');

// Adds a new menu item to the WordPress admin menu.
function mfc_ajouter_menu_admin() {
    add_menu_page(
        'Mon Formulaire de Contact', // Title of the page
        'Formulaire Contact', // Title of the menu
        'manage_options', // Capability required to see this option
        'mon-formulaire-contact', // Menu slug
        'mfc_page_admin', // Function to display the page content
        'dashicons-email' // Icon displayed in the menu
    );
}
add_action('admin_menu', 'mfc_ajouter_menu_admin');

// Renders the admin page for the plugin.
function mfc_page_admin() {
    ?>
    <div class="wrap">
        <h2>Ajouter un nouveau formulaire de contact</h2>
        <form method="post">
            <input type="text" name="mfc_new_title" placeholder="Titre du formulaire" required>
            <input type="email" name="mfc_new_email" placeholder="Email d'envoi" required>
            <input type="submit" name="mfc_add_form" value="Ajouter un Shortcode">
        </form>
        <h2>Liste des formulaires</h2>
        <?php
        $forms = get_option('mfc_forms');
        if (!empty($forms)) {
            echo '<ul>';
            foreach ($forms as $index => $form) {
                echo '<li id="form-item-' . $index . '">';
                echo 'Shortcode: [mon_formulaire_contact id="' . $index . '"] - Titre: ' . esc_html($form['title']) . ' - Email: ' . esc_html($form['email']);
                echo ' <a href="#" onclick="toggleEditForm(' . $index . '); return false;">Éditer</a>';
                echo ' | <a href="?page=mon-formulaire-contact&delete=' . $index . '">Supprimer</a>';
                echo '<div id="edit-form-' . $index . '" style="display:none;">';
                echo '<form method="post">';
                echo '<input type="hidden" name="mfc_form_index" value="' . $index . '">';
                echo '<input type="text" name="mfc_new_title" value="' . esc_attr($form['title']) . '" required>';
                echo '<input type="email" name="mfc_new_email" value="' . esc_attr($form['email']) . '" required>';
                echo '<input type="submit" name="mfc_update_form" value="Mettre à jour le formulaire">';
                echo '</form>';
                echo '</div>';
                echo '</li>';
            }
            echo '</ul>';
        }
        ?>
    </div>
    <?php
}


// Handles actions and redirects for form submissions in the admin.
function mfc_handle_actions_and_redirects() {
    // Ajout d'un nouveau formulaire
    if (isset($_POST['mfc_add_form'])) {
        $forms = get_option('mfc_forms', []);
        $forms[] = [
            'title' => sanitize_text_field($_POST['mfc_new_title']),
            'email' => sanitize_email($_POST['mfc_new_email'])
        ];
        update_option('mfc_forms', $forms);
        wp_redirect(admin_url('admin.php?page=mon-formulaire-contact'));
        exit;
    }

    // Handling item deletion
    if (isset($_GET['delete'])) {
        $index = intval($_GET['delete']);
        $forms = get_option('mfc_forms');
        array_splice($forms, $index, 1);
        update_option('mfc_forms', $forms);
        wp_redirect(admin_url('admin.php?page=mon-formulaire-contact'));
        exit;
    }

    // Handling form update
    if (isset($_POST['mfc_update_form'])) {
        $index = intval($_POST['mfc_form_index']);
        $forms = get_option('mfc_forms');
        $forms[$index] = [
            'title' => sanitize_text_field($_POST['mfc_new_title']),
            'email' => sanitize_email($_POST['mfc_new_email'])
        ];
        update_option('mfc_forms', $forms);
        wp_redirect(admin_url('admin.php?page=mon-formulaire-contact'));
        exit;
    }
}
add_action('admin_init', 'mfc_handle_actions_and_redirects');

// Registers settings for the plugin.
function mfc_register_settings() {
    register_setting('mfc_options_group', 'mfc_email', 'sanitize_email');
    register_setting('mfc_options_group', 'mfc_title');

    add_settings_section('mfc_main_section', 'Paramètres Principaux', 'mfc_section_text', 'mon-formulaire-contact');
    add_settings_field('mfc_title_field', 'Titre du Formulaire', 'mfc_title_callback', 'mon-formulaire-contact', 'mfc_main_section');
    add_settings_field('mfc_email_field', 'Email d\'envoi', 'mfc_email_callback', 'mon-formulaire-contact', 'mfc_main_section');
}
add_action('admin_init', 'mfc_register_settings');

// Displays main settings text, seems unnecessary if it only outputs a paragraph.
function mfc_section_text() {
    echo '<p>Entrez vos paramètres ci-dessous :</p>';
}

// Callbacks for displaying settings fields.
function mfc_title_callback() {
    $title = get_option('mfc_title');
    echo '<input type="text" id="mfc_title" name="mfc_title" value="' . esc_attr($title) . '" />';
}

function mfc_email_callback() {
    $email = get_option('mfc_email');
    echo '<input type="email" id="mfc_email" name="mfc_email" value="' . esc_attr($email) . '" />';
}

// Generates the contact form shortcode.
function mfc_formulaire_contact($atts) {
    $atts = shortcode_atts(array('id' => 0), $atts, 'mon_formulaire_contact');
    $forms = get_option('mfc_forms');
    if (isset($forms[$atts['id']])) {
        $form = $forms[$atts['id']];
        ob_start();
        ?>
        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
            <input type="hidden" name="action" value="mfc_envoyer_email">
            <input type="hidden" name="form_id" value="<?php echo esc_attr($atts['id']); ?>">
            <label><?php echo esc_html($form['title']); ?></label>
            <input type="email" name="email" placeholder="Votre email" required>
            <textarea name="message" placeholder="Votre message" required></textarea>
            <input type="submit" value="Envoyer">
        </form>
        <?php
        return ob_get_clean();
    }
    return 'Formulaire non configuré.';
}
add_shortcode('mon_formulaire_contact', 'mfc_formulaire_contact');

// Handles the form submission.
function mfc_traiter_formulaire() {
    $form_id = intval($_POST['form_id']);
    $forms = get_option('mfc_forms');
    if (isset($forms[$form_id])) {
        $form = $forms[$form_id];
        $to = $form['email'];
        $subject = 'Nouveau message de votre site WordPress';
        $email = sanitize_email($_POST['email']);
        $message = sanitize_textarea_field($_POST['message']);
        $body = "Vous avez reçu un message de : $email\n\n$message";
        wp_mail($to, $subject, $body);
    }
}
add_action('admin_post_nopriv_mfc_envoyer_email', 'mfc_traiter_formulaire');
add_action('admin_post_mfc_envoyer_email', 'mfc_traiter_formulaire');

// Enqueue JavaScript for admin, only needed if 'edit.js' is necessary.
function mfc_enqueue_admin_scripts() {
    wp_enqueue_script('mfc-edit-script', plugins_url('/js/edit.js', __FILE__), array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'mfc_enqueue_admin_scripts');
