<?php
/**
 * @package Make Plus
 */

global $ttfmake_section_data, $ttfmake_sections;
// Load the section class and render
$section_template = dirname( __FILE__ ) . '/section.php';
require_once( $section_template );

$section = new TTFMP_Panels_Frontend_Section( $ttfmake_section_data, $ttfmake_sections );
$section->render();
