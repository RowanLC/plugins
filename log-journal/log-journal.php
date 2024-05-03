<?php
/*
 * Plugin Name: logsurveil
 * Description:       Pugin de log
 * Version:           1.0.1
 * Author:            Moi
 * Text Domain:       my-basics-plugin
 * Domain Path:       /languages
 */


 
// 1 exercice simple
// filtrer l'affichage du champs page_link pour ne laisser que le nom de la page.

// 2 exercice moyen
// ajouter un bouton qui permet changer le nombre de ligne afficher

// 3 ajouter un bouton qui permet de purger la base de log

// 4 exercice un peu plus difficile
// ajouter un checkbox et un bouton qui permetent de supprimer des lignes de log 
// permet de de gérer les versions et de modifier la table avec les mises à jour du plugin


// 5 exercices cauchemardesque
// ajouter un lien sur ip_address qui trouve l'ip de l'utilisateur et ajouter le pays dans la base de données
// la difficulté est de trouver l'api whois gratuite..

add_option( "jal_db_version", "1.3" );


// require_once __DIR__ . '/lib/statistiques.php';

/**
 * installLogTable créer la table wp_plugin_log
 * avec les champ ip_address  page_link et create_at
 *
 * @return void
 */
function installLogTable(){
    // '$wpdb' est une instance de la classe WordPress Database Access Abstraction Object. Cet objet est utilisé pour interagir avec la base de données WordPress.
    global $wpdb;
    // Cette ligne déclare une variable globale $jal_db_version, mais elle n'est pas utilisée dans la fonction, donc elle semble ne pas être nécessaire ici. Peut-être qu'elle est utilisée ailleurs dans le plugin.
    global $jal_db_version ;

    // Cela récupère la valeur du jeu de caractères et de l'interclassement qui seront utilisés pour créer la table. C'est une bonne pratique pour s'assurer que la table utilise le même jeu de caractères que le reste de la base de données.
    $charset_collate = $wpdb->get_charset_collate();

    // Cela définit le nom de la table en ajoutant le préfixe de la table WordPress à "log_journal_data". Le préfixe est une chaîne de caractères qui est utilisée avant le nom de la table pour assurer la compatibilité avec les préfixes de table personnalisés définis par l'utilisateur.
    $xblog_table_name = $wpdb->prefix . 'log_journal_data';

    // Cette ligne crée la requête SQL pour créer la table.
    $sql = "CREATE TABLE $xblog_table_name (
        id INT(11) NOT NULL AUTO_INCREMENT,
        ip_address VARCHAR(50) NOT NULL,
        page_link VARCHAR(255) NOT NULL,
        create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        page_title VARCHAR(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // Cela inclut le fichier "upgrade.php" de WordPress, qui contient la fonction dbDelta() utilisée pour exécuter la requête SQL et mettre à jour la structure de la table.
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    // Cette fonction est utilisée pour exécuter la requête SQL. Elle gère les mises à jour de la structure de la table de manière à respecter les spécifications de la base de données, comme les changements de version ou les modifications de schéma.
    dbDelta( $sql );
}

// Cette fonction enregistre la fonction installLogTable() comme étant la fonction à exécuter lors de l'activation du plugin. Ainsi, lorsque le plugin est activé, la table de journalisation sera créée dans la base de données.
register_activation_hook( __FILE__, 'installLogTable' );

/**
 * log_update_db_check check la version du plugin
 * 
 *
 * @return void
 */

// La fonction log_update_db_check() est définie. Cette fonction est responsable de vérifier si une mise à jour de la base de données est nécessaire.
function log_update_db_check() {
    // Cela permet d'accéder à la variable globale $jal_db_version qui contient la version actuelle de la structure de la base de données. Cette variable est utilisée pour comparer avec la version stockée dans les options du site WordPress.
    global $jal_db_version;
    // Cette condition vérifie si la version de la base de données stockée dans les options du site est différente de la version actuelle. Si elles sont différentes, cela signifie qu'une mise à jour de la structure de la base de données est nécessaire.
    if ( get_site_option( 'jal_db_version' ) != $jal_db_version ) {
        // Si une mise à jour est nécessaire, cette ligne appelle la fonction installLogTable()
        installLogTable();
    }
}

// quand le plugin est chargé on vérifie si la version est mise à jour
add_action( 'plugins_loaded', 'log_update_db_check' );

/**
 * La fonction wp_plugin_log_user_ip utilise la variable global $wpdb pour accéder à l'objet de la base de données WordPress.
* Elle utilise la fonction $_SERVER['REMOTE_ADDR'] pour obtenir l'adresse IP de l'utilisateur qui visite l'article ou la page.
* Elle utilise la fonction get_permalink() pour obtenir l'URL de la page ou de l'article en cours de visite.
* Elle utilise la méthode $wpdb->insert() pour insérer les informations dans la table wp_plugin_log. Cette méthode prend trois arguments : le nom de la table, un tableau associatif contenant les valeurs à insérer, et un tableau indiquant le type de chaque valeur.
 *
 * @return void
 */

// Cette fonction est responsable de l'enregistrement des données dans la table de journalisation.
function wp_plugin_log_user_ip() {
    global $wpdb;
    //var_dump("ip_address: ", $_SERVER['REMOTE_ADDR'], "page_link: ", get_permalink());

    // Cette condition vérifie si l'URL du site est locale en appelant une fonction wp_plugin_check_local_url(). Si c'est le cas, cela génère une adresse IP aléatoire à des fins de démonstration ou de test en appelant generate_random_ip(). Sinon, il récupère l'adresse IP de l'utilisateur à partir de $_SERVER['REMOTE_ADDR'].
    if(wp_plugin_check_local_url(get_site_url())){
        $ip_address = generate_random_ip();
    }
    else {
        $ip_address = $_SERVER['REMOTE_ADDR'];
    }

    // Cela récupère le lien de la page visitée à l'aide de la fonction WordPress get_permalink().
    $page_link = get_permalink();
    echo get_the_title();
    $page_title = get_the_title();
    // Cela définit le nom de la table de journalisation en utilisant le préfixe de la table WordPress et en ajoutant "log_journal_data".
    $xblog_table_name = $wpdb->prefix . 'log_journal_data';
    // Cette ligne insère les données récupérées dans la table de journalisation. Elle utilise la méthode insert() de l'objet $wpdb pour effectuer l'opération d'insertion.
    $wpdb->insert( 
        $xblog_table_name, 
        array( 
            'ip_address' => $ip_address, 
            'page_link' => $page_link,
            'page_title' => $page_title,            
        ), 
        array( 
            '%s', 
            '%s',
            '%s', 
        ) 
    );
}

// Cette ligne lie la fonction wp_plugin_log_user_ip() à l'action WordPress 'wp'. Cela signifie que chaque fois que l'action 'wp' est déclenchée (ce qui se produit lorsqu'une page est chargée), la fonction wp_plugin_log_user_ip() sera exécutée pour enregistrer les données dans la table de journalisation.
add_action( 'wp', 'wp_plugin_log_user_ip' );

function get_page_title_from_url($url) {
    // Récupérer le contenu de la page à partir de l'URL
    $page_content = file_get_contents($url);

    // Si le contenu est récupéré avec succès
    if ($page_content !== false) {
        // Trouver la balise <title> dans le contenu de la page
        if (preg_match('/<title>(.*?)<\/title>/', $page_content, $matches)) {
            // Extraire le titre de la page de la balise <title>
            $page_title = $matches[1];
            // Retourner le titre de la page
            return $page_title;
        }
    }

    // Si le titre de la page n'est pas trouvé, retourner une chaîne vide
    return '';
}


/**
 * La fonction wp_plugin_log_admin_menu utilise la fonction add_menu_page() pour ajouter une nouvelle page dans la section d'administration de WordPress. Cette page affichera les informations stockées dans la table wp_plugin_log. 
*
 * @return void
 */

// Cette fonction est responsable de l'ajout des éléments de menu à l'interface d'administration.
function wp_plugin_log_admin_menu() {
    /* Ajoute une nouvelle page de menu principal avec le titre 'Plugin Log'
    - Plugin Log = Titre affiché dans le menu
    - manage_options = Capacité requise pour afficher cette page
    - 'wp_plugin_log' = Identifiant unique de la page, utilisé pout construire l'URL de la page et pour identifier la page dans le code
    'wp_plugin_log_page' = Fonction à appeler pour afficher le contenu de la page
    */
    add_menu_page( 'Plugin Log', 'Plugin Log', 'manage_options', 'wp_plugin_log', 'wp_plugin_log_page' );

    /* Ajoute un sous-menu à la page principale "Plugin Log"
    - 'wp_plugin_log' = Identifiant de la page principale à laquelle ce sous-menu est rattaché
    'Purge Log Database' = Titre affiché pour le sous-menu
    'Purge Database' = Le libellé du sous-menu
    'wp_plugin_log_purge_database' = Identifiant unique de la sous-page
    'wp_plugin_log_purge_database' = Fonction à appeler pour afficher le contenu de la sous-page*/
    add_submenu_page( 'wp_plugin_log', 'Purge Log Database', 'Purge Database', 'manage_options', 'wp_plugin_log_purge_database', 'wp_plugin_log_purge_database');
}
/**
 * La fonction add_action() est utilisée pour appeler la fonction wp_plugin_log_admin_menu lorsque WordPress charge la section d'administration.
 */
add_action( 'admin_menu', 'wp_plugin_log_admin_menu' );

/**
 * La fonction wp_plugin_log_page récupère les informations de la table wp_plugin_log en utilisant la méthode $wpdb->get_results(). Elle boucle ensuite sur chaque ligne de la table pour afficher les informations dans un tableau HTML. Undocumented function
 *
 * @return void
 */

// Cette fonction est responsable de l'affichage de la page dans l'interface d'administration.
function wp_plugin_log_page() {
    global $wpdb;

    // Cela définit le nom de la table de journalisation en utilisant le préfixe de la table WordPress et en ajoutant "log_journal_data".
    $xblog_table_name = $wpdb->prefix . 'log_journal_data';
 
      // Vérifie si le formulaire pour supprimer des journaux a été soumis et si des journaux ont été sélectionnés pour la suppression.
      if ( isset( $_POST['delete_logs'] ) && isset( $_POST['logs'] ) ) {
        $logs_to_delete = $_POST['logs'];
        // Si le formulaire est soumis et que des journaux sont sélectionnés, la boucle foreach itère sur chaque ID de journal à supprimer et utilise la méthode $wpdb->delete() pour supprimer les entrées correspondantes dans la table de journalisation.
        foreach ( $logs_to_delete as $log_id ) {
            $wpdb->delete( $xblog_table_name, array( 'id' => $log_id ), array( '%d' ) );
        }
        // Affiche un message de succès indiquant que les journaux sélectionnés ont été supprimés avec succès
        echo '<div class="notice notice-success is-dismissible"><p>Selected logs have been deleted successfully!</p></div>';
    }

    // Cette ligne vérifie si le paramètre 'paged' est défini dans la requête (généralement dans l'URL) et l'attribue à la variable $current_page. Si le paramètre 'paged' n'est pas défini, $current_page est défini à 1 par défaut.
    isset($_REQUEST['paged']) ?  $current_page = $_REQUEST['paged']  : $current_page = 1;
    // Définit le nombre d'éléments à afficher par page
    $per_page = 10;
    // Cela calcule l'offset pour la requête SQL afin de récupérer les éléments à afficher sur la page actuelle.
    $offset = ( $current_page - 1 ) * $per_page;
    // Cela récupère le nombre total d'éléments dans la table de journalisation en exécutant une requête SQL COUNT(*).
    $total_items = $wpdb->get_var( "SELECT COUNT(*) FROM $xblog_table_name" );
    // Cela calcule le nombre total de pages nécessaires pour afficher tous les éléments en divisant le nombre total d'éléments par le nombre d'éléments par page et en arrondissant à l'entier supérieur avec la fonction ceil().
    $total_pages = ceil( $total_items / $per_page );
    
    // Cela récupère le paramètre 'orderby' de l'URL pour déterminer le champ à utiliser pour trier les résultats. Par défaut, il utilise 'create_at' (probablement le champ de création dans la table).
    $orderby = isset( $_GET['orderby'] ) ? $_GET['orderby'] : 'create_at';
    // Cela récupère le paramètre 'order' de l'URL pour déterminer l'ordre de tri (ascendant ou descendant). Par défaut, il utilise 'desc'.
    $order = isset( $_GET['order'] ) ? $_GET['order'] : 'desc';
    // Cela détermine l'ordre de tri opposé pour le lien de tri dans l'interface utilisateur.
    $order_link = ($order == 'asc') ? 'desc' : 'asc';

    // Cela récupère les enregistrements de la table de journalisation en utilisant la requête SQL avec les clauses ORDER BY, LIMIT et OFFSET calculées précédemment pour la pagination et le tri des données.
    $logs = $wpdb->get_results( "SELECT * FROM $xblog_table_name ORDER BY $orderby $order LIMIT $per_page OFFSET $offset" );

    /**
     * La boucle `foreach` est utilisée pour afficher les informations récupérées de la base de données 
     * dans un tableau HTML.
     */
    echo '<h2>Plugin Log</h2>';
        // Cela commence un formulaire qui sera utilisé pour supprimer des journaux sélectionnés.
        echo '<form method="post" action="">';
    // Cela commence un tableau HTML pour afficher les journaux
    echo '<table>';
    //echo '<tr><th>IP Address</th><th>Page Link</th><th>Date</th></tr>';

    // Cette ligne définit une chaîne de caractères HTML contenant les en-têtes de colonne du tableau avec des liens de tri pour chaque colonne.
    $html = '<tr><th>
    <a href="'.get_site_url().'/wp-admin/admin.php?page=wp_plugin_log&orderby=ip_address&order=' . $order_link . '">IP Address
    </a></th><th><a href="'.get_site_url().'/wp-admin/admin.php?page=wp_plugin_log&orderby=page_link&order=' . $order_link . '">Page Link
    </a></th><th><a href="'.get_site_url().'/wp-admin/admin.php?page=wp_plugin_log&orderby=create_at&order=' . $order_link . '">Date
    </a></th></tr>';
    // Cette ligne affiche les en-têtes de colonne dans le tableau.
    echo htmlspecialchars_decode($html) ;
    // Cette ligne commence la balise <thead> du tableau avec les en-têtes de colonne.
    echo '<thead><tr><th>Select</th><th>ID</th><th>IP Address</th><th>Page Link</th><th>Created At</th></tr><th>Page Title</th></thead>';
    // Cette ligne commence la balise <tbody> du tableau où les données des journaux seront affichées.
    echo '<tbody>';
    foreach ( $logs as $log ) {
        echo '<tr>';
        echo '<td><input type="checkbox" name="logs[]" value="' . $log->id . '"></td>';
        echo '<td>' . $log->id . '</td>';
        echo '<td>' . $log->ip_address . '</td>';
        echo '<td>' . get_page_name_from_url($log->page_link) . '</td>';
        echo '<td>' . $log->create_at . '</td>';
        echo '<td>' . $log->page_title . '</td>';
        echo '</tr>';
    }
    // Cette ligne termine la balise <tbody> du tableau.
    echo '</tbody>';
    // Cette ligne termine la balise <table> du tableau.
    echo '</table>';
    echo '<br>';
    // Ceci affiche un bouton de soumission du formulaire avec le libellé "Delete Selected Logs".
    echo '<input type="submit" name="delete_logs" value="Delete Selected Logs" class="button button-primary">';
    // Terine le formulaire
    echo '</form>';

    // Variables utilisées pour le debug
    // var_dump("total_items",$total_items);
    // var_dump("current_page",$current_page);
    // var_dump("offset",$offset);
    // var_dump($_REQUEST);

    // Afficher la pagination    
    echo '<div class="pagination">';
    $format = 'page/%#%/';
    // La fonction `paginate_links()` est utilisée pour afficher les liens de pagination.
    // Cette fonction prend un tableau d'options, y compris la base des liens,
    // le format des liens, le texte pour les liens "Précédent" et "Suivant", 
    //le nombre total de pages et la page actuelle.
    echo paginate_links( array(
        'base' => add_query_arg( 'paged', '%#%' ),
        'format' => $format,
        'prev_text' => '&laquo;',
        'next_text' => '&raquo;',
        'total' => $total_pages,
        'current' =>  $current_page )
    );
    echo '</div>';

    plugin_stats();
}

function wp_plugin_log_purge_database() {
    global $wpdb;
    $xblog_table_name = $wpdb->prefix . 'log_journal_data';
    // Cette ligne exécute une requête SQL pour vider la table de journalisation en utilisant la commande TRUNCATE TABLE, ce qui supprime toutes les lignes de la table tout en conservant sa structure.
    $wpdb->query("TRUNCATE TABLE $xblog_table_name");
}

function wp_plugin_check_local_url($url) {
    // Cela crée un tableau contenant les hôtes locaux courants.
    $local_hosts = array('localhost', '127.0.0.1');
    // Cette ligne utilise la fonction parse_url() pour analyser l'URL donnée et extraire ses différents composants, tels que le protocole, l'hôte, le chemin, etc.
    $parsed_url = parse_url($url);
    // Cette condition vérifie si l'URL contient un composant 'host' (hôte), ce qui signifie qu'elle contient une information d'hôte.
    if (isset($parsed_url['host'])) {
        // Extrait l'hôte de l'URL analysée
        $host = $parsed_url['host'];
        // Cette condition vérifie si l'hôte extrait de l'URL est présent dans le tableau $local_hosts, ce qui signifie qu'il correspond à un hôte local.
        if (in_array($host, $local_hosts)) {
            return true;
        }
    }
    return false;
}


/**
 * La fonction generate_random_ip utilise une boucle for pour générer quatre nombres aléatoires entre 0 et 255.
* Elle stocke ces nombres dans un tableau $ip.
* Elle utilise la fonction implode pour fusionner les nombres en une chaîne de caractères séparée par des points, qui représente l'adresse IP aléatoire.
* L'exemple d'utilisation montre comment appeler la fonction et stocker le résultat dans une variable $random_ip.
* Enfin, le code affiche l'adresse IP aléatoire en utilisant la fonction echo.
* N'oubliez pas que cette adresse IP aléatoire ne correspond pas à une véritable adresse IP et qu'elle ne doit pas être utilisée dans un contexte de production.
 */

function generate_random_ip() {
    $ip = array();
    for ($i = 0; $i < 4; $i++) {
        $ip[] = rand(0, 255);
    }
    return implode('.', $ip);
}

/**
 * Undocumented function
 * Cette fonction utilise la fonction parse_url() pour extraire le chemin de l'URL, puis la fonction explode() 
 * pour séparer le chemin en segments. 
 * La fonction end() est utilisée pour récupérer le dernier segment, qui est le nom de la page.
 * @param [type] $url
 * @return void
 */
function get_page_name_from_url($url) {
    $path = parse_url($url, PHP_URL_PATH);
    $segments = explode('/', rtrim($path, '/'));
    return end($segments);
}


function plugin_stats() {
    global $wpdb; // Accéder à la base de données Wordpress

    // Cette ligne récupère le nombre total de visiteurs uniques en comptant le nombre d'adresses IP distinctes dans la table de journalisation.
    $total_visitors = $wpdb->get_var("SELECT COUNT(DISTINCT ip_address) FROM {$wpdb->prefix}log_journal_data");
    // Cela récupère le nombre total de visites en comptant le nombre total d'enregistrements dans la table de journalisation.
    $total_visits = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}log_journal_data");

    // Cette requête SQL récupère les cinq pages les plus visitées, triées par nombre de visites décroissant.
    $most_visited_pages = $wpdb->get_results("
        SELECT page_link, COUNT(*) as visit_count
        FROM {$wpdb->prefix}log_journal_data
        GROUP BY page_link
        ORDER BY visit_count DESC
        LIMIT 5"); // Les 5 pages les plus visitées, triées par nombre de visites décroissant

    // Cette requête SQL récupère les dix visiteurs les plus récents en sélectionnant les adresses IP distinctes et les dates de création les plus récentes à partir de la table de journalisation.
    $recent_visitors = $wpdb->get_results("
        SELECT DISTINCT ip_address, create_at
        FROM {$wpdb->prefix}log_journal_data
        ORDER BY create_at DESC
        LIMIT 10"); // Les 10 visiteurs les plus récents

    // Afficher les résultats
    echo "<p>Nombre total de visiteurs uniques: $total_visitors</p>";
    echo "<p>Nombre total de visites: $total_visits</p>";
    echo "<p>Pages les plus visitées:</p>";
    echo "<ul>";
    foreach ($most_visited_pages as $page) {
        echo "<li>{$page->page_link} ({$page->visit_count} visites)</li>";
    }
    echo "</ul>";
    echo "<p>Visiteurs récents:</p>";
    echo "<ul>";
    foreach ($recent_visitors as $visitor) {
        echo "<li>{$visitor->ip_address} ({$visitor->create_at})</li>";
    }
    echo "</ul>";
}