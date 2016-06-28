<?php 

$screens = array('alc');

$metaboxes = array(
  'alc_profile' => array(
    'title'       => __( 'General Information', 'alc_text' ),
    'slug'        => 'alc_profile',
    'description' => __( 'Basic information about the ALC', 'alc_text' ),
    'context'     => 'normal',
    'priority'    => 'high',
    'screens'      => $screens,
    'fields'      => array(
      array(
        'id' => 'address',
        'label' => __( 'Address', 'alc_text' ),
        'type' => 'text',
      ),
      array(
        'id' => 'website',
        'label' => __( 'Website', 'alc_text' ),
        'type' => 'url',
      ),
      array(
        'id' => 'age_range',
        'label' => __( 'Age Range', 'alc_text' ),
        'type' => 'text',
      ),
      array(
        'id' => 'facebook',
        'label' => __( 'Facebook', 'alc_text' ),
        'type' => 'url',
      ),
      array(
        'id' => 'twitter',
        'label' => __( 'Twitter', 'alc_text' ),
        'type' => 'url',
      )
    )
  ),
  'alc_holders' => array(
    'title'       => __( 'Coherence Holders', 'alc_text' ),
    'slug'        => 'alc_holders',
    'description' => __( 'Coherence Holders Contact Information', 'alc_text' ),
    'context'     => 'normal',
    'priority'    => 'high',
    'screens'      => $screens,
    'fields'      => array(
      array(
        'id'    => 'primary_name',
        'label' => __( 'Name primary', 'alc_text' ),
        'type'  => 'name',
      ),
      array(
        'id'    => 'primary_email',
        'label' => __( 'Email primary', 'alc_text' ),
        'type'  => 'email',
      ),
      array(
        'id'    => 'primary_phone',
        'label' => __( 'Phone primary', 'alc_text' ),
        'type'  => 'phone',
      ),
      array(
        'id'    => 'other_contacts',
        'label' => __( 'Other Contacts', 'alc_text' ),
        'desc'  => 'Place each contact on a new line, separate name|email|phone with pipes',
        'type'  => 'textarea',
      ),
    )
  ),
  'alc_map_info' => array(
    'title'       => __( 'Map Info', 'alc_text' ),
    'slug'        => 'alc_map_info',
    'description' => __( 'Public information that displays on map', 'alc_text' ),
    'context'     => 'normal',
    'priority'    => 'high',
    'screens'      => $screens,
    'fields'      => array(
      array(
        'id'    => 'on_map',
        'label' => __( 'On Map?', 'alc_text' ),
        'type'  => 'checkbox',
      ),
      array(
        'id'    => 'name',
        'label' => __( 'Map Name', 'alc_text' ),
        'type'  => 'text',
      ),
      array(
        'id'    => 'description',
        'label' => __( 'Map Description', 'alc_text' ),
        'type'  => 'textarea',
      ),
      array(
        'id' => 'cta_label',
        'label' => __( 'Call to Action Label', 'alc_text' ),
        'type' => 'text',
      ),
      array(
        'id' => 'cta',
        'label' => __( 'Call to Action Link', 'alc_text' ),
        'type' => 'url',
      ),
      array(
        'id' => 'contact_name',
        'label' => __( 'Public Contact Name', 'alc_text' ),
        'type' => 'text',
      ),
      array(
        'id' => 'contact_email',
        'label' => __( 'Public Contact Email', 'alc_text' ),
        'type' => 'email',
      ),
      array(
        'id' => 'contact_phone',
        'label' => __( 'Public Contact Phone', 'alc_text' ),
        'type' => 'phone',
      )
    )
  ),
  'alc_membership' => array(
    'title'       => __( 'ALC Membership', 'alc_text' ),
    'slug'        => 'alc_membership',
    'description' => __( 'ALC Membership Information', 'alc_text' ),
    'context'     => 'side',
    'priority'    => 'high',
    'screens'      => $screens,
    'fields'      => array(
      array(
        'id'    => 'active',
        'label' => __( 'Active?', 'alc_text' ),
        'type'  => 'checkbox',
        'desc'  => 'Is the ALC operating?',
      ),
      array(
        'id' => 'last_payment_date',
        'label' => __( 'Last Payment Date', 'alc_text' ),
        'type' => 'date',
      ),
      array(
        'id' => 'network_holder',
        'label' => __( 'Network Holder', 'alc_text' ),
        'type' => 'user',
      ),
      array(
        'id' => 'start_date',
        'label' => __( 'Start Date', 'alc_text' ),
        'type' => 'date',
      )
    )
  )
); // /$metaboxes

class alcMetabox {
  public $metabox;

  public function __construct($metabox) {
    $this->metabox = (object) $metabox;
    add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
    add_action( 'save_post', array( $this, 'save_post' ) );
  }

  /**
   * Hooks into WordPress' add_meta_boxes function.
   * Goes through screens (post types) and adds the meta box.
   */
  public function add_meta_boxes() {
    foreach ( $this->metabox->screens as $screen ) {
      add_meta_box(
        $this->metabox->slug,
        $this->metabox->title,
        array( $this, 'add_meta_box_callback' ),
        $screen,
        $this->metabox->context,
        $this->metabox->priority
      );
    }
  }

  /**
   * Generates the HTML for the meta box
   * 
   * @param object $post WordPress post object
   */
  public function add_meta_box_callback( $post ) {
    wp_nonce_field( $this->metabox->slug . '_data', $this->metabox->slug . '_nonce' );
    echo $this->metabox->description;
    $this->generate_fields( $post );
  }

  /**
   * Generates the field's HTML for the meta box.
   */
  public function generate_fields( $post ) {
    $output = '';
    $class = ($this->metabox->context != 'side') ? 'regular-text' : '' ;
    
    foreach ( $this->metabox->fields as $field ) {
      $label = '<label for="' . $field['id'] . '">' . $field['label'] . '</label>';

      $db_value = get_post_meta( $post->ID, $this->metabox->slug . '_' . $field['id'], true );
      switch ( $field['type'] ) {
        case 'checkbox':
          $input = sprintf(
            '<input %s id="%s" name="%s" type="checkbox" value="1">',
            $db_value === '1' ? 'checked' : '',
            $field['id'],
            $field['id']
          );
          if ( $field['desc'] ) $label .= '<p>' . $field['desc'] . '</p>';
          break;
        case 'media':
          $input = sprintf(
            '<input class="%s" id="%s" name="%s" type="text" value="%s"> <input class="button rational-metabox-media" id="%s_button" name="%s_button" type="button" value="Upload" />',
            $class,
            $field['id'],
            $field['id'],
            $db_value,
            $field['id'],
            $field['id']
          );
          if ( $field['desc'] ) $label .= '<p>' . $field['desc'] . '</p>';
          break;
        case 'textarea':
          $input = sprintf(
            '<textarea class="large-text" id="%s" name="%s" rows="5">%s</textarea>',
            $field['id'],
            $field['id'],
            $db_value
          );
          if ( $field['desc'] ) $label .= '<p>' . $field['desc'] . '</p>';
          break;

        // case 'user': search user by name or email
        // case 'address': search address
        // case 'geocode': enter address and geocode entry into hidden field
        default:
          $input = sprintf(
            '<input %s id="%s" name="%s" type="%s" value="%s">',
            $field['type'] !== 'color' ? 'class="' . $class . '"' : '',
            $field['id'],
            $field['id'],
            $field['type'],
            $db_value
          );
      }
      $output .= $this->row_format( $label, $input, $field['type'] );
    }
    echo $this->container( $output );
  }

  /**
   * Generates the HTML for rows.
   */
  public function row_format( $label, $input, $type = false ) {
    switch ($this->metabox->context) {
      case 'side':
        $output = '<p>' . $input . ' '; 
        $output .= ($type != 'checkbox') ? '<br>' : '' ; 
        $output .= $label . '</p>'; 
        break;
      
      default:
        $output = sprintf(
          '<tr><th scope="row">%s</th><td>%s</td></tr>',
          $label,
          $input
        );
        break;
    }
    return $output;
  }

  /**
   * Generates the HTML container.
   */
  public function container( $inside ) {
    switch ($this->metabox->context) {
      case 'side':
        $output = $inside;
        break;
      
      default:
        $output = '<table class="form-table"><tbody>' . $inside . '</tbody></table>';
        break;
    }
    return $output;
  }

  /**
   * Hooks into WordPress' save_post function
   */
  public function save_post( $post_id ) {
    if ( ! isset( $_POST[$this->metabox->slug . '_nonce'] ) )
      return $post_id;

    $nonce = $_POST[$this->metabox->slug . '_nonce'];
    if ( !wp_verify_nonce( $nonce, $this->metabox->slug . '_data' ) )
      return $post_id;

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
      return $post_id;

    foreach ( $this->metabox->fields as $field ) {
      if ( isset( $_POST[ $field['id'] ] ) ) {
        switch ( $field['type'] ) {
          case 'email':
            $_POST[ $field['id'] ] = sanitize_email( $_POST[ $field['id'] ] );
            break;
          case 'text':
            $_POST[ $field['id'] ] = sanitize_text_field( $_POST[ $field['id'] ] );
            break;
        }
        update_post_meta( $post_id, $this->metabox->slug . '_' . $field['id'], $_POST[ $field['id'] ] );
      } else if ( $field['type'] === 'checkbox' ) {
        update_post_meta( $post_id, $this->metabox->slug . '_' . $field['id'], '0' );
      }
    }
  }

}

foreach ($metaboxes as $metabox) {
  new alcMetabox($metabox);
}
// new alcMetabox($metaboxes['alc_profile']);
// new alcMetabox($metaboxes['alc_holders']);
// new alcMetabox($metaboxes['alc_map_info']);
// new alcMetabox($metaboxes['alc_membership']);