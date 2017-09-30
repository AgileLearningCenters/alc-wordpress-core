<?php

/**
 *
 *
 */
require_once 'class.textfield.php';

class WPToolset_Field_Submit extends WPToolset_Field_Textfield {

    public function metaform() {
        $attributes = $this->getAttr();
        $shortcode_class = array_key_exists( 'class', $attributes ) ? $attributes['class'] : "";

        if ( array_key_exists( 'use_bootstrap', $this->_data )
		    && $this->_data['use_bootstrap']
	    ) {
		    $metaform[] = array(
			    '#type' => 'submit',
			    '#title' => $this->getTitle(),
			    '#description' => $this->getDescription(),
			    '#name' => $this->getName(),
			    '#value' => esc_attr( __( $this->getValue(), 'wpv-views' ) ),
			    '#validate' => $this->getValidationData(),
			    '#attributes' => array(
				    'class' => "btn btn-primary {$shortcode_class}",
			    ),
		    );
	    } else {
		    $metaform[] = array(
			    '#type' => 'submit',
			    '#title' => $this->getTitle(),
			    '#description' => $this->getDescription(),
			    '#name' => $this->getName(),
			    '#value' => esc_attr( __( $this->getValue(), 'wpv-views' ) ),
			    '#validate' => $this->getValidationData(),
			    '#attributes' => array(
				    'class' => $shortcode_class,
			    ),
		    );
	    }
                        
        return $metaform;
    }

}
