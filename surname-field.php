<?php
/*
 * Plugin Name: Surname Field
 * Plugin URI: https://example.com/surname-field-woocommerce
 * Description: This plugin adds a surname field to the WooCommerce register form and provides a setting page on the dashboard where the user can select the option to save the surname field in the first name, last name, nickname, or both. 우커머스 회원가입 폼에 성함(Full Name) 입력란을 추가하는 플러그인 입니다. 저장된 값은 First Name, Last Name, 또는 Nickname 값으로 저장할 수 있습니다.
 * Version: 1.0
 * Author: Sang Hyun Han
 * Author URI: https://example.com
 * License: GPL2
 */

// Add the surname field to the WooCommerce register form
add_action( 'woocommerce_register_form_start', 'surname_field_register_form' );
function surname_field_register_form() {
  $surname_field_label = get_option( 'surname_field_label', __( 'Surname', 'woocommerce' ) );
  $surname = ( ! empty( $_POST['surname'] ) ) ? esc_attr( $_POST['surname'] ) : '';
  ?>
  <p class="form-row form-row-wide">
    <label for="reg_surname"><?php echo $surname_field_label; ?> <span class="required">*</span></label>
    <input type="text" class="input-text" name="surname" id="reg_surname" value="<?php echo esc_attr( $surname ); ?>" />
  </p>
  <?php
}


// Save the value of the surname field when the user registers
add_action( 'woocommerce_created_customer', 'surname_field_save_registration_data' );
function surname_field_save_registration_data( $customer_id ) {
  if ( isset( $_POST['surname'] ) ) {
    update_user_meta( $customer_id, 'surname', sanitize_text_field( $_POST['surname'] ) );
  }
}

// Add a top-level menu item to the dashboard
add_action( 'admin_menu', 'surname_field_settings_menu' );
function surname_field_settings_menu() {
  add_options_page( __( 'Surname Field Settings', 'woocommerce' ), __( 'Surname Field', 'woocommerce' ), 'manage_options', 'surname-field-settings', 'surname_field_settings_page' );
}

// Add a settings page to the dashboard
function surname_field_settings_page() {
  // Check if the user has permission to access the settings page
  if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( __( 'You do not have sufficient permissions to access this page.', 'woocommerce' ) );
  }

// Check if the form has been submitted
if ( isset( $_POST['submit'] ) && wp_verify_nonce( $_POST['surname_field_settings_nonce'], 'surname_field_settings' ) ) {
    // Debug code: Print the values of the form fields
    // echo '<pre>';
    // print_r( $_POST );
    // echo '</pre>';
  
    // Validate and sanitize the form data
    $surname_field_location = ( isset( $_POST['surname_field_location'] ) && is_array( $_POST['surname_field_location'] ) ) ? array_map( 'sanitize_text_field', $_POST['surname_field_location'] ) : array();
    $surname_field_label = ( isset( $_POST['surname_field_label'] ) ) ? sanitize_text_field( $_POST['surname_field_label'] ) : __( 'Surname', 'woocommerce' );

    // Save the form data
    update_option( 'surname_field_location', $surname_field_location );
    update_option( 'surname_field_label', $surname_field_label );
  }
  
  // Load the plugin's settings
  $surname_field_location = get_option( 'surname_field_location', array( 'last_name' ) );
  $surname_field_label = get_option( 'surname_field_label', __( 'Surname', 'woocommerce' ) );
  ?>
  <div class="wrap">
    <h1><?php _e( 'Surname Field Settings', 'woocommerce' ); ?></h1>
    <form method="post">
      <?php wp_nonce_field( 'surname_field_settings', 'surname_field_settings_nonce' ); ?>
      <table class="form-table">
        <tbody>
          <tr>
            <th scope="row"><label for="surname_field_location"><?php _e( 'Save Surname Field In', 'woocommerce' ); ?></label></th>
            <td>
              <fieldset>
                <legend class="screen-reader-text"><span><?php _e( 'Save Surname Field In', 'woocommerce' ); ?></span></legend>
                <label for="surname_field_location_first_name">
                  <input type="checkbox" name="surname_field_location[]" id="surname_field_location_first_name" value="first_name" <?php checked( in_array( 'first_name', $surname_field_location ), true ); ?> /><?php _e( 'First Name', 'woocommerce' ); ?>
                </label><br>
                <label for="surname_field_location_last_name">
                  <input type="checkbox" name="surname_field_location[]" id="surname_field_location_last_name" value="last_name" <?php checked( in_array( 'last_name', $surname_field_location ), true ); ?> /><?php _e( 'Last Name', 'woocommerce' ); ?>
                </label><br>
                <label for="surname_field_location_nickname">
                <input type="checkbox" name="surname_field_location[]" id="surname_field_location_nickname" value="nickname" <?php checked( in_array( 'nickname', $surname_field_location ), true ); ?> /><?php _e( 'Nickname', 'woocommerce' ); ?>
                </label><br>
                </fieldset>
            </td>
            <tr>
          <th scope="row"><label for="surname_field_label"><?php _e( 'Surname Field Label', 'woocommerce' ); ?></label></th>
          <td>
            <input type="text" class="regular-text" name="surname_field_label" id="surname_field_label" value="<?php echo esc_attr( $surname_field_label ); ?>" />
            <p class="description"><?php _e( 'The label for the surname field on the WooCommerce register form.', 'woocommerce' ); ?></p>
          </td>
        </tr>
      </tbody>
    </table>
    <input type="submit" class="button button-primary" name="submit" value="<?php _e( 'Save Changes', 'woocommerce' ); ?>">
  </form>
</div>
<?php
}

// Save the surname field in the selected location when the user registers
add_action( 'woocommerce_created_customer', 'surname_field_save_location' );
function surname_field_save_location( $customer_id ) {
  if ( isset( $_POST['surname'] ) ) {
    $surname = sanitize_text_field( $_POST['surname'] );
    $surname_field_location = get_option( 'surname_field_location', array( 'last_name' ) );
    if ( in_array( 'first_name', $surname_field_location ) ) {
      update_user_meta( $customer_id, 'first_name', $surname );
    }
    if ( in_array( 'last_name', $surname_field_location ) ) {
      update_user_meta( $customer_id, 'last_name', $surname );
    }
    if ( in_array( 'nickname', $surname_field_location ) ) {
      update_user_meta( $customer_id, 'nickname', $surname );
    }
  }
}

// Change the label of the surname field on the WooCommerce register form
add_filter( 'woocommerce_form_field_args', 'surname_field_label', 10, 3 );
function surname_field_label( $args, $key, $value ) {
  if ( $key == 'surname' ) {
    $args['label'] = get_option( 'surname_field_label', __( 'Surname', 'woocommerce' ) );
  }

  return $args;
}


  
  