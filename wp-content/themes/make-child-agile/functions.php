<?php
/**
 * @package Make Child
 *
 * Add your custom functions here.
 */

wp_enqueue_script(
    'child-script',
    get_stylesheet_directory_uri() . '/js/child.js',
    $script_dependencies,
    TTFMAKE_VERSION,
    true
);

function ttfmake_option_defaults() {
  $defaults = array(
    // Site Title & Tagline
    'hide-site-title'                          => 0,
    'hide-tagline'                             => 1,
    'color-site-title'                         => '#171717',

    // Navigation
    'navigation-mobile-label'                  => __( 'Menu', 'make' ),

    // General
    'general-layout'                           => 'full-width',
    'general-sticky-label'                     => __( 'Featured', 'make' ),

    // Logo
    'logo-regular'                             => 'http://agilelearningcenters.org/wp-content/uploads/2014/07/alc_circle_logo.png',
    'logo-retina'                              => 'http://agilelearningcenters.org/wp-content/uploads/2014/07/alc_circle_retina.png',
    'logo-favicon'                             => 'http://agilelearningcenters.org/wp-content/uploads/2014/07/alc_circle_favicon.png',
    'logo-apple-touch'                         => 'http://agilelearningcenters.org/wp-content/uploads/2014/07/alc_circle_appletouch.png',

    // Background
    'background_color'                         => '#b9bcbf',
    'background_image'                         => '',
    'background_repeat'                        => 'repeat',
    'background_position_x'                    => 'left',
    'background_attachment'                    => 'scroll',
    'background_size'                          => 'auto',

    // Fonts
    'font-site-title'                          => 'sans-serif',
    'font-header'                              => 'sans-serif',
    'font-body'                                => 'Open Sans',
    'font-site-title-size'                     => 34,
    'font-site-tagline-size'                   => 12,
    'font-nav-size'                            => 22,
    'font-header-size'                         => 50,
    'font-widget-size'                         => 16,
    'font-body-size'                           => 19,
    'font-subset'                              => 'latin',

    // Colors
    'color-primary'                            => '#403391',
    'color-secondary'                          => '#eaecee',
    'color-text'                               => '#171717',
    'color-detail'                             => '#b9bcbf',

    // Layout - Blog
    'layout-blog-hide-header'                  => 0,
    'layout-blog-hide-footer'                  => 0,
    'layout-blog-sidebar-left'                 => 0,
    'layout-blog-sidebar-right'                => 1,
    'layout-blog-featured-images'              => 'post-header',
    'layout-blog-post-date'                    => 'absolute',
    'layout-blog-post-author'                  => 'avatar',
    'layout-blog-auto-excerpt'                 => 0,
    'layout-blog-show-categories'              => 1,
    'layout-blog-show-tags'                    => 1,
    'layout-blog-featured-images-alignment'    => 'center',
    'layout-blog-post-date-location'           => 'top',
    'layout-blog-post-author-location'         => 'post-footer',
    'layout-blog-comment-count'                => 'none',
    'layout-blog-comment-count-location'       => 'before-content',

    // Layout - Archive
    'layout-archive-hide-header'               => 0,
    'layout-archive-hide-footer'               => 0,
    'layout-archive-sidebar-left'              => 0,
    'layout-archive-sidebar-right'             => 1,
    'layout-archive-featured-images'           => 'post-header',
    'layout-archive-post-date'                 => 'absolute',
    'layout-archive-post-author'               => 'avatar',
    'layout-archive-auto-excerpt'              => 0,
    'layout-archive-show-categories'           => 1,
    'layout-archive-show-tags'                 => 1,
    'layout-archive-featured-images-alignment' => 'center',
    'layout-archive-post-date-location'        => 'top',
    'layout-archive-post-author-location'      => 'post-footer',
    'layout-archive-comment-count'             => 'none',
    'layout-archive-comment-count-location'    => 'before-content',

    // Layout - Search
    'layout-search-hide-header'                => 0,
    'layout-search-hide-footer'                => 0,
    'layout-search-sidebar-left'               => 0,
    'layout-search-sidebar-right'              => 1,
    'layout-search-featured-images'            => 'thumbnail',
    'layout-search-post-date'                  => 'absolute',
    'layout-search-post-author'                => 'name',
    'layout-search-auto-excerpt'               => 1,
    'layout-search-show-categories'            => 1,
    'layout-search-show-tags'                  => 1,
    'layout-search-featured-images-alignment'  => 'center',
    'layout-search-post-date-location'         => 'top',
    'layout-search-post-author-location'       => 'post-footer',
    'layout-search-comment-count'              => 'none',
    'layout-search-comment-count-location'     => 'before-content',

    // Layout - Posts
    'layout-post-hide-header'                  => 0,
    'layout-post-hide-footer'                  => 0,
    'layout-post-sidebar-left'                 => 0,
    'layout-post-sidebar-right'                => 0,
    'layout-post-featured-images'              => 'post-header',
    'layout-post-post-date'                    => 'absolute',
    'layout-post-post-author'                  => 'avatar',
    'layout-post-show-categories'              => 1,
    'layout-post-show-tags'                    => 1,
    'layout-post-featured-images-alignment'    => 'center',
    'layout-post-post-date-location'           => 'top',
    'layout-post-post-author-location'         => 'post-footer',
    'layout-post-comment-count'                => 'none',
    'layout-post-comment-count-location'       => 'before-content',

    // Layout - Pages
    'layout-page-hide-header'                  => 0,
    'layout-page-hide-footer'                  => 0,
    'layout-page-sidebar-left'                 => 0,
    'layout-page-sidebar-right'                => 0,
    'layout-page-hide-title'                   => 1,
    'layout-page-featured-images'              => 'none',
    'layout-page-post-date'                    => 'none',
    'layout-page-post-author'                  => 'none',
    'layout-page-featured-images-alignment'    => 'center',
    'layout-page-post-date-location'           => 'top',
    'layout-page-post-author-location'         => 'post-footer',
    'layout-page-comment-count'                => 'none',
    'layout-page-comment-count-location'       => 'before-content',

    // Header
    'header-text-color'                        => '#171717',
    'header-background-color'                  => '#f5ed08',
    'header-background-image'                  => '',
    'header-background-repeat'                 => 'no-repeat',
    'header-background-position'               => 'center',
    'header-background-size'                   => 'cover',
    'header-bar-background-color'              => '#403391',
    'header-bar-text-color'                    => '#ffffff',
    'header-bar-border-color'                  => '#ffffff',
    'header-text'                              => '',
    'header-show-social'                       => 0,
    'header-show-search'                       => 1,
    'header-bar-content-layout'                => 'default',
    'header-layout'                            => 2,
    'header-branding-position'                 => 'left',

    // Main
    'main-background-color'                    => '#ffffff',
    'main-background-image'                    => '',
    'main-background-repeat'                   => 'repeat',
    'main-background-position'                 => 'left',
    'main-background-size'                     => 'auto',
    'main-content-link-underline'              => 0,

    // Footer
    'footer-text-color'                        => '#464849',
    'footer-border-color'                      => '#b9bcbf',
    'footer-background-color'                  => '#eaecee',
    'footer-background-image'                  => 'http://agilelearningcenters.org/wp-content/uploads/2014/07/alc_smile_logo.png',
    'footer-background-repeat'                 => 'no-repeat',
    'footer-background-position'               => 'center',
    'footer-background-size'                   => 'contain',
    'footer-widget-areas'                      => 3,
    'footer-text'                              => '',
    'footer-show-social'                       => 1,
    'footer-layout'                            => 1,

    // Social
    'social-facebook'                          => '',
    'social-twitter'                           => '',
    'social-google-plus-square'                => '',
    'social-linkedin'                          => '',
    'social-instagram'                         => '',
    'social-flickr'                            => '',
    'social-youtube'                           => '',
    'social-vimeo-square'                      => '',
    'social-pinterest'                         => '',
    'social-email'                             => '',
    'social-hide-rss'                          => 0,
    'social-custom-rss'                        => '',
  );

  return apply_filters( 'ttfmake_setting_defaults', $defaults );
}