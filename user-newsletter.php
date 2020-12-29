<?php
/*
Plugin Name: User Newsletter
Description: Plugin which allows admin to add posts destined to specific user. Using of WooCommerce "My Account" page.
Version: 1.0
*/


/* MY ACCOUNT PAGE FUNCTIONS */

function usernewsletter_custom_endpoints() {
    add_rewrite_endpoint( 'newsletter', EP_ROOT | EP_PAGES );
}
add_action( 'init', 'usernewsletter_custom_endpoints' );

function usernewsletter_custom_query_vars( $vars ) {
    $vars[] = 'newsletter';
    return $vars;
}
add_filter( 'query_vars', 'usernewsletter_custom_query_vars', 0 );

add_filter( 'woocommerce_account_menu_items' , 'usernewsletter_menu_panel_nav' );

function usernewsletter_menu_panel_nav() {
    $items = array(
        'dashboard'       => __( 'Dashboard', 'woocommerce' ),
        'orders'          => __( 'Orders', 'woocommerce' ),
        'newsletter' => __( 'Newsletter', 'woocommerce' ), 
        'edit-address'    => __( 'Addresses', 'woocommerce' ),
        'customer-logout' => __( 'Logout', 'woocommerce' ),
    );

    return $items;
}

/* Contents of newsletter tab */

function usernewsletter_newsletter_content() {

    $current_user = wp_get_current_user();
    $email = esc_html( $current_user->user_email );

    $args = array(
        'post_type' => 'newsletters',
        'post_status' => 'publish',
        'meta_key' => '_user_email', 'meta_value' => $email
    );
    $the_query = new WP_Query( $args );

    if ( $the_query->have_posts() ) : ?>


<?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
<h2 class="user_post_heading"><?php the_title(); ?></h2>
<?php the_content(); ?>
<?php endwhile; ?>

<?php wp_reset_postdata(); ?>

<?php else : ?>
<p><?php _e( 'There is no newsletters' ); ?></p>
<?php endif; 
}

add_action( 'woocommerce_account_newsletter_endpoint', 'usernewsletter_newsletter_content' );

/* newsletter POST TYPE */

function usernewsletter_newsletter_post_type() {
    $labels = array(
        'name'                => 'Newsletters',
        'singular_name'       => 'newsletter',
        'menu_name'           => 'Newslettters',
        'all_items'           => 'All Newsletters',
        'view_item'           => 'View Newsletter',
        'add_new_item'        => 'Add new Newsletter',
        'add_new'             => 'Add Newsletter',
        'edit_item'           => 'Edit Newsletter',
        'update_item'         => 'Update',
        'search_items'        => 'Search Newsletters',
        'not_found'           => 'Not found',
        'not_found_in_trash'  => 'Not found'
    ); 
    $args = array(
        'label' => 'Newsletters',
        'rewrite' => array(
            'slug' => 'newsletters'
        ),
        'description'         => 'newsletter',
        'labels'              => $labels,
        'supports'            => array('editor', 'title'),
        'taxonomies'          => array(),
        'hierarchical'        => false,
        'public'              => true, 
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 4,
        'menu_icon'           => 'dashicons-buddicons-pm',
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'post',
        'show_in_rest'       => true
    );
    register_post_type( 'newsletters', $args );
} 
add_action( 'init', 'usernewsletter_newsletter_post_type', 0 );

/* Adding custom meta box */

function usernewsletter_client_meta_box_add() {

    $post_types = array ( 'newsletters');
    foreach( $post_types as $post_type ){
        add_meta_box( 'usernewsletter_client-meta-box-id', 'Użytkownik', 'usernewsletter_client_meta_box', $post_type, 'normal', 'high' );
    }

} 
add_action( 'add_meta_boxes', 'usernewsletter_client_meta_box_add' );


function usernewsletter_client_meta_box( $post ) {
    $value = get_post_meta( $post->ID, '_user_email', true );
?>
<p>
    <label for="usernewsletter_client_meta_box_email">Email użytkownika: </label>
    <select name='usernewsletter_client_meta_box_email' id='usernewsletter_client_meta_box_email'>
        <?php $users=get_users(); 
    foreach ($users as $user): ?>
        <option value="<?php echo esc_html( $user->user_email ); ?>" <?php selected( $value, $user->user_email ); ?> ><?php echo esc_html( $user->user_email ); ?></option>
        <?php endforeach; ?>
    </select>
</p>
<?php   
}

function usernewsletter_client_meta_box_save_postdata( $post_id ) {
    if ( array_key_exists( 'usernewsletter_client_meta_box_email', $_POST ) ) {
        update_post_meta(
            $post_id,
            '_user_email',
            $_POST['usernewsletter_client_meta_box_email']
        );
    }
}
add_action( 'save_post', 'usernewsletter_client_meta_box_save_postdata' );

?>