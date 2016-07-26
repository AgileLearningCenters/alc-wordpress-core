<?php 

//Include admin
include dirname( __FILE__ ) .'/custom-facebook-feed-admin.php';
include dirname( __FILE__ ) .'/cff_autolink.php';

// Add shortcodes
add_shortcode('custom-facebook-feed', 'display_cff');
/**
 * @param $atts
 * @param null $content
 * @return string
 */
function display_cff($atts, $content = null) {
    //Which extensions are active?
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    $cff_ext_options = get_option('cff_extensions_status');

    //Set extensions in extensions plugin all to false by default
    $cff_ext_multifeed_active_check = false;
    $cff_ext_date_range_active_check = false;
    $cff_ext_featured_post_active_check = false;
    $cff_ext_album_active_check = false;
    $cff_ext_masonry_columns_active_check = false;
    $cff_ext_carousel_active_check = false;
    $cff_extensions_reviews_active = false;


    if (WPW_SL_ITEM_NAME == 'Custom Facebook Feed WordPress Plugin Smash'){
        //Set page variables
        if( isset($cff_ext_options[ 'cff_extensions_multifeed_active' ]) ) $cff_ext_multifeed_active_check = $cff_ext_options[ 'cff_extensions_multifeed_active' ];
        if( isset($cff_ext_options[ 'cff_extensions_date_range_active' ]) ) $cff_ext_date_range_active_check = $cff_ext_options[ 'cff_extensions_date_range_active' ];
        if( isset($cff_ext_options[ 'cff_extensions_featured_post_active' ]) ) $cff_ext_featured_post_active_check = $cff_ext_options[ 'cff_extensions_featured_post_active' ];
        if( isset($cff_ext_options[ 'cff_extensions_album_active' ]) ) $cff_ext_album_active_check = $cff_ext_options[ 'cff_extensions_album_active' ];
        if( isset($cff_ext_options[ 'cff_extensions_masonry_columns_active' ]) ) $cff_ext_masonry_columns_active_check = $cff_ext_options[ 'cff_extensions_masonry_columns_active' ];
        if( isset($cff_ext_options[ 'cff_extensions_carousel_active' ]) ) $cff_ext_carousel_active_check = $cff_ext_options[ 'cff_extensions_carousel_active' ];
        if( isset($cff_ext_options[ 'cff_extensions_reviews_active' ]) ) $cff_extensions_reviews_active = $cff_ext_options[ 'cff_extensions_reviews_active' ];
    }

    ( is_plugin_active( 'cff-multifeed/cff-multifeed.php' ) || $cff_ext_multifeed_active_check ) ? $cff_ext_multifeed_active = true : $cff_ext_multifeed_active = false;
    ( is_plugin_active( 'cff-date-range/cff-date-range.php' ) || $cff_ext_date_range_active_check ) ? $cff_ext_date_active = true : $cff_ext_date_active = false;
    ( is_plugin_active( 'cff-featured-post/cff-featured-post.php' ) || $cff_ext_featured_post_active_check ) ? $cff_featured_post_active = true : $cff_featured_post_active = false;
    ( is_plugin_active( 'cff-album/cff-album.php' ) || $cff_ext_album_active_check ) ? $cff_album_active = true : $cff_album_active = false;
    ( is_plugin_active( 'cff-masonry/cff-masonry.php' ) || $cff_ext_masonry_columns_active_check ) ? $cff_masonry_columns_active = true : $cff_masonry_columns_active = false;
    ( is_plugin_active( 'cff-carousel/cff-carousel.php' ) || $cff_ext_carousel_active_check ) ? $cff_carousel_active = true : $cff_carousel_active = false;
    ( is_plugin_active( 'cff-reviews/cff-reviews.php' ) || $cff_extensions_reviews_active ) ? $cff_reviews_active = true : $cff_reviews_active = false;

    //Style options
    $options = get_option('cff_style_settings');
    //Create the types string to set as shortcode default
    $type_string = '';
    if($options[ 'cff_show_links_type' ]) $type_string .= 'links,';
    if($options[ 'cff_show_event_type' ]) $type_string .= 'events,';
    if($options[ 'cff_show_video_type' ]) $type_string .= 'videos,';
    if($options[ 'cff_show_photos_type' ]) $type_string .= 'photos,';
    if($options[ 'cff_show_albums_type' ]) $type_string .= 'albums,';
    //If the album option hasn't been set yet in the $options array (ie. plugin has been updated but the option hasn't been saved) then set albums to display by default
    if ( !array_key_exists( 'cff_show_albums_type', $options ) ) $type_string .= 'albums,';
    if($options[ 'cff_show_status_type' ]) $type_string .= 'statuses,';

    //Create the includes string to set as shortcode default
    $include_string = '';
    if($options[ 'cff_show_author' ]) $include_string .= 'author,';
    if($options[ 'cff_show_text' ]) $include_string .= 'text,';
    if($options[ 'cff_show_desc' ]) $include_string .= 'desc,';
    if($options[ 'cff_show_shared_links' ]) $include_string .= 'sharedlinks,';
    if($options[ 'cff_show_date' ]) $include_string .= 'date,';
    if($options[ 'cff_show_media' ]) $include_string .= 'media,';
    if($options[ 'cff_show_event_title' ]) $include_string .= 'eventtitle,';
    if($options[ 'cff_show_event_details' ]) $include_string .= 'eventdetails,';
    if($options[ 'cff_show_meta' ]) $include_string .= 'social,';
    if($options[ 'cff_show_link' ]) $include_string .= 'link,';
    if($options[ 'cff_show_like_box' ]) $include_string .= 'likebox,';

    //Reviews rated string
    $cff_reviews_string = '';
    if( isset($options[ 'cff_reviews_rated_5' ]) && isset($options[ 'cff_reviews_rated_4' ]) && isset($options[ 'cff_reviews_rated_3' ]) && isset($options[ 'cff_reviews_rated_2' ]) && isset($options[ 'cff_reviews_rated_1' ]) ){
        if($options[ 'cff_reviews_rated_5' ]) $cff_reviews_string .= '5,';
        if($options[ 'cff_reviews_rated_4' ]) $cff_reviews_string .= '4,';
        if($options[ 'cff_reviews_rated_3' ]) $cff_reviews_string .= '3,';
        if($options[ 'cff_reviews_rated_2' ]) $cff_reviews_string .= '2,';
        if($options[ 'cff_reviews_rated_1' ]) $cff_reviews_string .= '1';
    }

    //Pass in shortcode attrbutes, include filter for extensions
    $atts = shortcode_atts(
    array(
        'accesstoken' => get_option('cff_access_token'),
        'pagetoken' => get_option('cff_page_access_token'),
        'id' => get_option('cff_page_id'),
        'pagetype' => get_option('cff_page_type'),
        'num' => get_option('cff_num_show'),
        'limit' => get_option('cff_post_limit'),
        'others' => '',
        'showpostsby' => get_option('cff_show_others'),
        'cachetime' => get_option('cff_cache_time'),
        'cacheunit' => get_option('cff_cache_time_unit'),
        'locale' => get_option('cff_locale'),
        'ajax' => get_option('cff_ajax'),
        'offset' => '',

        //General
        'width' => isset($options[ 'cff_feed_width' ]) ? $options[ 'cff_feed_width' ] : '',
        'widthresp' => isset($options[ 'cff_feed_width_resp' ]) ? $options[ 'cff_feed_width_resp' ] : '',        
        'height' => isset($options[ 'cff_feed_height' ]) ? $options[ 'cff_feed_height' ] : '',
        'padding' => isset($options[ 'cff_feed_padding' ]) ? $options[ 'cff_feed_padding' ] : '',
        'bgcolor' => isset($options[ 'cff_bg_color' ]) ? $options[ 'cff_bg_color' ] : '',
        'showauthor' => '',
        'showauthornew' => isset($options[ 'cff_show_author' ]) ? $options[ 'cff_show_author' ] : '',
        'class' => isset($options[ 'cff_class' ]) ? $options[ 'cff_class' ] : '',
        'type' => $type_string,
        //Events only
        'eventsource' => isset($options[ 'cff_events_source' ]) ? $options[ 'cff_events_source' ] : '',
        'eventoffset' => isset($options[ 'cff_event_offset' ]) ? $options[ 'cff_event_offset' ] : '',
        'eventimage' => isset($options[ 'cff_event_image_size' ]) ? $options[ 'cff_event_image_size' ] : '',
        'pastevents' => 'false',
        //Albums only
        'albumsource' => isset($options[ 'cff_albums_source' ]) ? $options[ 'cff_albums_source' ] : '',
        'showalbumtitle' => isset($options[ 'cff_show_album_title' ]) ? $options[ 'cff_show_album_title' ] : '',
        'showalbumnum' => isset($options[ 'cff_show_album_number' ]) ? $options[ 'cff_show_album_number' ] : '',
        'albumcols' => isset($options[ 'cff_album_cols' ]) ? $options[ 'cff_album_cols' ] : '',
        //Photos only
        'photosource' => isset($options[ 'cff_photos_source' ]) ? $options[ 'cff_photos_source' ] : '',
        'photocols' => isset($options[ 'cff_photos_cols' ]) ? $options[ 'cff_photos_cols' ] : '',
        //Videos only
        'videosource' => isset($options[ 'cff_videos_source' ]) ? $options[ 'cff_videos_source' ] : '',
        'showvideoname' => isset($options[ 'cff_show_video_name' ]) ? $options[ 'cff_show_video_name' ] : '',
        'showvideodesc' => isset($options[ 'cff_show_video_desc' ]) ? $options[ 'cff_show_video_desc' ] : '',
        'videocols' => isset($options[ 'cff_video_cols' ]) ? $options[ 'cff_video_cols' ] : '',
        //Filters
        'filter' => isset($options[ 'cff_filter_string' ]) ? $options[ 'cff_filter_string' ] : '',
        'exfilter' => isset($options[ 'cff_exclude_string' ]) ? $options[ 'cff_exclude_string' ] : '',

        //Post Layout
        'layout' => isset($options[ 'cff_preset_layout' ]) ? $options[ 'cff_preset_layout' ] : '',
        'enablenarrow' => isset($options[ 'cff_enable_narrow' ]) ? $options[ 'cff_enable_narrow' ] : '',
        'mediaposition' => isset($options[ 'cff_media_position' ]) ? $options[ 'cff_media_position' ] : '',
        'disablelightbox' => isset($options[ 'cff_disable_lightbox' ]) ? $options[ 'cff_disable_lightbox' ] : '',
        'include' => $include_string,
        'exclude' => '',

        //Post Style
        'postbgcolor' => isset($options[ 'cff_post_bg_color' ]) ? $options[ 'cff_post_bg_color' ] : '',
        'postcorners' => isset($options[ 'cff_post_rounded' ]) ? $options[ 'cff_post_rounded' ] : '',

        //Typography
        'textformat' => isset($options[ 'cff_title_format' ]) ? $options[ 'cff_title_format' ] : '',
        'textsize' => isset($options[ 'cff_title_size' ]) ? $options[ 'cff_title_size' ] : '',
        'textweight' => isset($options[ 'cff_title_weight' ]) ? $options[ 'cff_title_weight' ] : '',
        'textcolor' => isset($options[ 'cff_title_color' ]) ? $options[ 'cff_title_color' ] : '',
        'textlinkcolor' => isset($options[ 'cff_posttext_link_color' ]) ? $options[ 'cff_posttext_link_color' ] : '',
        'textlink' => isset($options[ 'cff_title_link' ]) ? $options[ 'cff_title_link' ] : '',
        'posttags' => isset($options[ 'cff_post_tags' ]) ? $options[ 'cff_post_tags' ] : '',
        'linkhashtags' => isset($options[ 'cff_link_hashtags' ]) ? $options[ 'cff_link_hashtags' ] : '',

        //Author
        'authorsize' => isset($options[ 'cff_author_size' ]) ? $options[ 'cff_author_size' ] : '',
        'authorcolor' => isset($options[ 'cff_author_color' ]) ? $options[ 'cff_author_color' ] : '',

        //Description
        'descsize' => isset($options[ 'cff_body_size' ]) ? $options[ 'cff_body_size' ] : '',
        'descweight' => isset($options[ 'cff_body_weight' ]) ? $options[ 'cff_body_weight' ] : '',
        'desccolor' => isset($options[ 'cff_body_color' ]) ? $options[ 'cff_body_color' ] : '',
        'linktitleformat' => isset($options[ 'cff_link_title_format' ]) ? $options[ 'cff_link_title_format' ] : '',
        'fulllinkimages' => isset($options[ 'cff_full_link_images' ]) ? $options[ 'cff_full_link_images' ] : '',
        'linktitlesize' => isset($options[ 'cff_link_title_size' ]) ? $options[ 'cff_link_title_size' ] : '',
        'linktitlecolor' => isset($options[ 'cff_link_title_color' ]) ? $options[ 'cff_link_title_color' ] : '',
        'linkurlcolor' => isset($options[ 'cff_link_url_color' ]) ? $options[ 'cff_link_url_color' ] : '',
        'linkbgcolor' => isset($options[ 'cff_link_bg_color' ]) ? $options[ 'cff_link_bg_color' ] : '',
        'linkbordercolor' => isset($options[ 'cff_link_border_color' ]) ? $options[ 'cff_link_border_color' ] : '',
        'disablelinkbox' => isset($options[ 'cff_disable_link_box' ]) ? $options[ 'cff_disable_link_box' ] : '',


        //Event title
        'eventtitleformat' => isset($options[ 'cff_event_title_format' ]) ? $options[ 'cff_event_title_format' ] : '',
        'eventtitlesize' => isset($options[ 'cff_event_title_size' ]) ? $options[ 'cff_event_title_size' ] : '',
        'eventtitleweight' => isset($options[ 'cff_event_title_weight' ]) ? $options[ 'cff_event_title_weight' ] : '',
        'eventtitlecolor' => isset($options[ 'cff_event_title_color' ]) ? $options[ 'cff_event_title_color' ] : '',
        'eventtitlelink' => isset($options[ 'cff_event_title_link' ]) ? $options[ 'cff_event_title_link' ] : '',
        //Event date
        'eventdatesize' => isset($options[ 'cff_event_date_size' ]) ? $options[ 'cff_event_date_size' ] : '',
        'eventdateweight' => isset($options[ 'cff_event_date_weight' ]) ? $options[ 'cff_event_date_weight' ] : '',
        'eventdatecolor' => isset($options[ 'cff_event_date_color' ]) ? $options[ 'cff_event_date_color' ] : '',
        'eventdatepos' => isset($options[ 'cff_event_date_position' ]) ? $options[ 'cff_event_date_position' ] : '',
        'eventdateformat' => isset($options[ 'cff_event_date_formatting' ]) ? $options[ 'cff_event_date_formatting' ] : '',
        'eventdatecustom' => isset($options[ 'cff_event_date_custom' ]) ? $options[ 'cff_event_date_custom' ] : '',
        //Event details
        'eventdetailssize' => isset($options[ 'cff_event_details_size' ]) ? $options[ 'cff_event_details_size' ] : '',
        'eventdetailsweight' => isset($options[ 'cff_event_details_weight' ]) ? $options[ 'cff_event_details_weight' ] : '',
        'eventdetailscolor' => isset($options[ 'cff_event_details_color' ]) ? $options[ 'cff_event_details_color' ] : '',
        'eventlinkcolor' => isset($options[ 'cff_event_link_color' ]) ? $options[ 'cff_event_link_color' ] : '',        

        //Date
        'datepos' => isset($options[ 'cff_date_position' ]) ? $options[ 'cff_date_position' ] : '',
        'datesize' => isset($options[ 'cff_date_size' ]) ? $options[ 'cff_date_size' ] : '',
        'dateweight' => isset($options[ 'cff_date_weight' ]) ? $options[ 'cff_date_weight' ] : '',
        'datecolor' => isset($options[ 'cff_date_color' ]) ? $options[ 'cff_date_color' ] : '',
        'dateformat' => isset($options[ 'cff_date_formatting' ]) ? $options[ 'cff_date_formatting' ] : '',
        'datecustom' => isset($options[ 'cff_date_custom' ]) ? $options[ 'cff_date_custom' ] : '',
        'timezone' => isset($options[ 'cff_timezone' ]) ? $options[ 'cff_timezone' ] : 'America/Chicago',

        //Link to Facebook
        'linksize' => isset($options[ 'cff_link_size' ]) ? $options[ 'cff_link_size' ] : '',
        'linkweight' => isset($options[ 'cff_link_weight' ]) ? $options[ 'cff_link_weight' ] : '',
        'linkcolor' => isset($options[ 'cff_link_color' ]) ? $options[ 'cff_link_color' ] : '',
        'viewlinktext' => isset($options[ 'cff_view_link_text' ]) ? $options[ 'cff_view_link_text' ] : '',
        'linktotimeline' => isset($options[ 'cff_link_to_timeline' ]) ? $options[ 'cff_link_to_timeline' ] : '',
        
        //Social
        'iconstyle' => isset($options[ 'cff_icon_style' ]) ? $options[ 'cff_icon_style' ] : '',
        'socialtextcolor' => isset($options[ 'cff_meta_text_color' ]) ? $options[ 'cff_meta_text_color' ] : '',
        'socialbgcolor' => isset($options[ 'cff_meta_bg_color' ]) ? $options[ 'cff_meta_bg_color' ] : '',
        'sociallinkcolor' => isset($options[ 'cff_meta_link_color' ]) ? $options[ 'cff_meta_link_color' ] : '',
        'expandcomments' => isset($options[ 'cff_expand_comments' ]) ? $options[ 'cff_expand_comments' ] : '',
        'commentsnum' => isset($options[ 'cff_comments_num' ]) ? $options[ 'cff_comments_num' ] : '',
        'hidecommentimages' => isset($options[ 'cff_hide_comment_avatars' ]) ? $options[ 'cff_hide_comment_avatars' ] : '',

        //Misc
        'textlength' => get_option('cff_title_length'),
        'desclength' => get_option('cff_body_length'),
        'likeboxpos' => isset($options[ 'cff_like_box_position' ]) ? $options[ 'cff_like_box_position' ] : '',
        'likeboxoutside' => isset($options[ 'cff_like_box_outside' ]) ? $options[ 'cff_like_box_outside' ] : '',
        'likeboxcolor' => isset($options[ 'cff_likebox_bg_color' ]) ? $options[ 'cff_likebox_bg_color' ] : '',
        'likeboxtextcolor' => isset($options[ 'cff_like_box_text_color' ]) ? $options[ 'cff_like_box_text_color' ] : '',
        'likeboxwidth' => isset($options[ 'cff_likebox_width' ]) ? $options[ 'cff_likebox_width' ] : '',
        'likeboxheight' => isset($options[ 'cff_likebox_height' ]) ? $options[ 'cff_likebox_height' ] : '',
        'likeboxfaces' => isset($options[ 'cff_like_box_faces' ]) ? $options[ 'cff_like_box_faces' ] : '',
        'likeboxborder' => isset($options[ 'cff_like_box_border' ]) ? $options[ 'cff_like_box_border' ] : '',
        'likeboxcover' => isset($options[ 'cff_like_box_cover' ]) ? $options[ 'cff_like_box_cover' ] : '',
        'likeboxsmallheader' => isset($options[ 'cff_like_box_small_header' ]) ? $options[ 'cff_like_box_small_header' ] : '',
        'likeboxhidebtn' => isset($options[ 'cff_like_box_hide_cta' ]) ? $options[ 'cff_like_box_hide_cta' ] : '',

        'credit' => isset($options[ 'cff_show_credit' ]) ? $options[ 'cff_show_credit' ] : '',
        'nofollow' => 'true',

        //Page Header
        'showheader' => isset($options[ 'cff_show_header' ]) ? $options[ 'cff_show_header' ] : '',
        'headeroutside' => isset($options[ 'cff_header_outside' ]) ? $options[ 'cff_header_outside' ] : '',
        'headertext' => isset($options[ 'cff_header_text' ]) ? $options[ 'cff_header_text' ] : '',
        'headerbg' => isset($options[ 'cff_header_bg_color' ]) ? $options[ 'cff_header_bg_color' ] : '',
        'headerpadding' => isset($options[ 'cff_header_padding' ]) ? $options[ 'cff_header_padding' ] : '',
        'headertextsize' => isset($options[ 'cff_header_text_size' ]) ? $options[ 'cff_header_text_size' ] : '',
        'headertextweight' => isset($options[ 'cff_header_text_weight' ]) ? $options[ 'cff_header_text_weight' ] : '',
        'headertextcolor' => isset($options[ 'cff_header_text_color' ]) ? $options[ 'cff_header_text_color' ] : '',
        'headericon' => isset($options[ 'cff_header_icon' ]) ? $options[ 'cff_header_icon' ] : '',
        'headericoncolor' => isset($options[ 'cff_header_icon_color' ]) ? $options[ 'cff_header_icon_color' ] : '',
        'headericonsize' => isset($options[ 'cff_header_icon_size' ]) ? $options[ 'cff_header_icon_size' ] : '',

        'videoheight' => isset($options[ 'cff_video_height' ]) ? $options[ 'cff_video_height' ] : '',
        'videoaction' => isset($options[ 'cff_video_action' ]) ? $options[ 'cff_video_action' ] : '',
        'sepcolor' => isset($options[ 'cff_sep_color' ]) ? $options[ 'cff_sep_color' ] : '',
        'sepsize' => isset($options[ 'cff_sep_size' ]) ? $options[ 'cff_sep_size' ] : '',

        //Translate
        'seemoretext' => isset( $options[ 'cff_see_more_text' ] ) ? stripslashes( esc_attr( $options[ 'cff_see_more_text' ] ) ) : '',
        'seelesstext' => isset( $options[ 'cff_see_less_text' ] ) ? stripslashes( esc_attr( $options[ 'cff_see_less_text' ] ) ) : '',
        'photostext' => isset( $options[ 'cff_translate_photos_text' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_photos_text' ] ) ) : '',
        'facebooklinktext' => isset( $options[ 'cff_facebook_link_text' ] ) ? stripslashes( esc_attr( $options[ 'cff_facebook_link_text' ] ) ) : '',
        'sharelinktext' => isset( $options[ 'cff_facebook_share_text' ] ) ? stripslashes( esc_attr( $options[ 'cff_facebook_share_text' ] ) ) : '',
        'showfacebooklink' => isset($options[ 'cff_show_facebook_link' ]) ? $options[ 'cff_show_facebook_link' ] : '',
        'showsharelink' => isset($options[ 'cff_show_facebook_share' ]) ? $options[ 'cff_show_facebook_share' ] : '',
        'buyticketstext' => isset($options[ 'cff_buy_tickets_text' ]) ? $options[ 'cff_buy_tickets_text' ] : '',

        'maptext' => isset( $options[ 'cff_map_text' ] ) ? stripslashes( esc_attr( $options[ 'cff_map_text' ] ) ) : '',
        'previouscommentstext' => isset( $options[ 'cff_translate_view_previous_comments_text' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_view_previous_comments_text' ] ) ) : '',
        'commentonfacebooktext' => isset( $options[ 'cff_translate_comment_on_facebook_text' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_comment_on_facebook_text' ] ) ) : '',
        'likesthistext' => isset( $options[ 'cff_translate_likes_this_text' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_likes_this_text' ] ) ) : '',
        'likethistext' => isset( $options[ 'cff_translate_like_this_text' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_like_this_text' ] ) ) : '',
        'andtext' => isset( $options[ 'cff_translate_and_text' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_and_text' ] ) ) : '',
        'othertext' => isset( $options[ 'cff_translate_other_text' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_other_text' ] ) ) : '',
        'otherstext' => isset( $options[ 'cff_translate_others_text' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_others_text' ] ) ) : '',
        'noeventstext' => isset( $options[ 'cff_no_events_text' ] ) ? stripslashes( esc_attr( $options[ 'cff_no_events_text' ] ) ) : '',
        'replytext' => isset( $options[ 'cff_translate_reply_text' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_reply_text' ] ) ) : '',
        'repliestext' => isset( $options[ 'cff_translate_replies_text' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_replies_text' ] ) ) : '',

        'secondtext' => isset( $options[ 'cff_translate_second' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_second' ] ) ) : 'second',
        'secondstext' => isset( $options[ 'cff_translate_seconds' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_seconds' ] ) ) : 'seconds',
        'minutetext' => isset( $options[ 'cff_translate_minute' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_minute' ] ) ) : 'minute',
        'minutestext' => isset( $options[ 'cff_translate_minutes' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_minutes' ] ) ) : 'minutes',
        'hourtext' => isset( $options[ 'cff_translate_hour' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_hour' ] ) ) : 'hour',
        'hourstext' => isset( $options[ 'cff_translate_hours' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_hours' ] ) ) : 'hours',
        'daytext' => isset( $options[ 'cff_translate_day' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_day' ] ) ) : 'day',
        'daystext' => isset( $options[ 'cff_translate_days' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_days' ] ) ) : 'days',
        'weektext' => isset( $options[ 'cff_translate_week' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_week' ] ) ) : 'week',
        'weekstext' => isset( $options[ 'cff_translate_weeks' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_weeks' ] ) ) : 'weeks',
        'monthtext' => isset( $options[ 'cff_translate_month' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_month' ] ) ) : 'month',
        'monthstext' => isset( $options[ 'cff_translate_months' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_months' ] ) ) : 'months',
        'yeartext' => isset( $options[ 'cff_translate_year' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_year' ] ) ) : 'year',
        'yearstext' => isset( $options[ 'cff_translate_years' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_years' ] ) ) : 'years',
        'agotext' => isset( $options[ 'cff_translate_ago' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_ago' ] ) ) : 'ago',

        //Extensions
        'from' => get_option( 'cff_date_from' ),
        'until' => get_option( 'cff_date_until' ),
        'featuredpost' => get_option( 'cff_featured_post_id' ),
        'album' => '',
        'lightbox' => get_option('cff_lightbox'),
        //Reviews
        'reviewsrated' => $cff_reviews_string,
        'starsize' => isset($options[ 'cff_star_size' ]) ? $options[ 'cff_star_size' ] : '',
        'reviewslinktext' => isset( $options[ 'cff_reviews_link_text' ] ) ? stripslashes( esc_attr( $options[ 'cff_reviews_link_text' ] ) ) : ''

    ), $atts, 'custom_facebook_feed' );

    /********** GENERAL **********/
    $cff_page_type = $atts[ 'pagetype' ];
    $cff_is_group = false;
    if ($cff_page_type == 'group') $cff_is_group = true;

    $cff_feed_width = $atts[ 'width' ];
    if ( is_numeric(substr($cff_feed_width, -1, 1)) ) $cff_feed_width = $cff_feed_width . 'px';

    //Set to be 100% width on mobile?
    $cff_feed_width_resp = $atts[ 'widthresp' ];
    ( $cff_feed_width_resp == 'on' || $cff_feed_width_resp == 'true' || $cff_feed_width_resp == true ) ? $cff_feed_width_resp = true : $cff_feed_width_resp = false;
    if( $atts[ 'widthresp' ] == 'false' ) $cff_feed_width_resp = false;


    $cff_feed_height = $atts[ 'height' ];
    if ( is_numeric(substr($cff_feed_height, -1, 1)) ) $cff_feed_height = $cff_feed_height . 'px';

    $cff_feed_padding = $atts[ 'padding' ];
    if ( is_numeric(substr($cff_feed_padding, -1, 1)) ) $cff_feed_padding = $cff_feed_padding . 'px';

    $cff_bg_color = $atts[ 'bgcolor' ];
    $cff_show_author = $atts[ 'showauthornew' ];
    $cff_cache_time = $atts[ 'cachetime' ];
    $cff_locale = $atts[ 'locale' ];

    if ( empty($cff_locale) || !isset($cff_locale) || $cff_locale == '' ) $cff_locale = 'en_US';

    $cff_cache_time_unit = $atts[ 'cacheunit' ];

    //Don't allow cache time to be zero - set to 1 minute instead to minimize API requests
    if(!isset($cff_cache_time) || $cff_cache_time == '0'){
        $cff_cache_time = 1;
        $cff_cache_time_unit = 'minutes';
    }
    if($cff_cache_time == 'none') $cff_cache_time = 0;

    $cff_class = $atts['class'];
    //Compile feed styles
    $cff_feed_styles = '';
    if ( !empty($cff_feed_width) || !empty($cff_feed_height) || !empty($cff_feed_padding) || (!empty($cff_bg_color) && $cff_bg_color != '#') ) $cff_feed_styles .= 'style="';
    if ( !empty($cff_feed_width) ) $cff_feed_styles .= 'width:' . $cff_feed_width . '; ';
    if ( !empty($cff_feed_height) ) $cff_feed_styles .= 'height:' . $cff_feed_height . '; ';
    if ( !empty($cff_feed_padding) ) $cff_feed_styles .= 'padding:' . $cff_feed_padding . '; ';
    if ( !empty($cff_bg_color) && $cff_bg_color != '#' ) $cff_feed_styles .= 'background-color:#' . str_replace('#', '', $cff_bg_color) . '; ';
    if ( !empty($cff_feed_width) || !empty($cff_feed_height) || !empty($cff_feed_padding) || (!empty($cff_bg_color) && $cff_bg_color != '#') )$cff_feed_styles .= '"';
    //Like box
    $cff_like_box_position = $atts[ 'likeboxpos' ];
    $cff_like_box_outside = $atts[ 'likeboxoutside' ];
    //Open links in new window?
    $target = 'target="_blank"';
    /********** POST TYPES **********/
    $cff_types = $atts[ 'type' ];
    //Look for non-plural version of string in the types string in case user specifies singular in shortcode
    $cff_show_links_type = false;
    $cff_show_event_type = false;
    $cff_show_video_type = false;
    $cff_show_photos_type = false;
    $cff_show_status_type = false;
    $cff_show_albums_type = false;
    $cff_reviews = false;
    if ( stripos($cff_types, 'link') !== false ) $cff_show_links_type = true;
    if ( stripos($cff_types, 'event') !== false ) $cff_show_event_type = true;
    if ( stripos($cff_types, 'video') !== false ) $cff_show_video_type = true;
    if ( stripos($cff_types, 'photo') !== false ) $cff_show_photos_type = true;
    if ( stripos($cff_types, 'album') !== false ) $cff_show_albums_type = true;
    if ( stripos($cff_types, 'status') !== false ) $cff_show_status_type = true;
    if ( stripos($cff_types, 'review') !== false && $cff_reviews_active ) $cff_reviews = true;

    //Only events
    $cff_events_source = $atts[ 'eventsource' ];
    if ( empty($cff_events_source) || !isset($cff_events_source) ) $cff_events_source = 'eventspage';

    $cff_event_offset = $atts[ 'eventoffset' ];
    if ( empty($cff_event_offset) || !isset($cff_event_offset) ) $cff_event_offset = '6';

    $cff_events_only = false;
    if ($cff_show_event_type && !$cff_show_links_type && !$cff_show_video_type && !$cff_show_photos_type && !$cff_show_status_type && !$cff_show_albums_type) $cff_events_only = true;
    
    //ALBUMS ONLY
    $cff_albums_source = $atts[ 'albumsource' ];
    $cff_show_album_title = $atts[ 'showalbumtitle' ];
    ( $cff_show_album_title == 'on' || $cff_show_album_title == 'true' || $cff_show_album_title == true ) ? $cff_show_album_title = true : $cff_show_album_title = false;
    if( $atts[ 'showalbumtitle' ] == 'false' ) $cff_show_album_title = false;

    $cff_show_album_number = $atts[ 'showalbumnum' ];
    ( $cff_show_album_number == 'on' || $cff_show_album_number == 'true' || $cff_show_album_number == true ) ? $cff_show_album_number = true : $cff_show_album_number = false;
    if( $atts[ 'showalbumnum' ] == 'false' ) $cff_show_album_number = false;

    $cff_album_cols = $atts['albumcols'];

    $cff_albums_only = false;
    if ($cff_show_albums_type && !$cff_show_links_type && !$cff_show_video_type && !$cff_show_photos_type && !$cff_show_status_type && !$cff_show_event_type) $cff_albums_only = true;

    //PHOTOS ONLY
    $cff_photos_source = $atts[ 'photosource' ];
    isset($atts['photocols']) ? $cff_photos_cols = $atts['photocols'] : $cff_photos_cols = '1';

    $cff_photos_only = false;
    if ( ($cff_show_photos_type && $cff_photos_source == 'photospage') && !$cff_show_links_type && !$cff_show_video_type && !$cff_show_event_type && !$cff_show_status_type && !$cff_show_albums_type) $cff_photos_only = true;
    if( $cff_featured_post_active && !empty($atts['featuredpost']) ) $cff_photos_only = false;


    //VIDEOS ONLY
    $cff_videos_source = $atts[ 'videosource' ];

    $cff_show_video_name = $atts[ 'showvideoname' ];
    ( $cff_show_video_name == 'on' || $cff_show_video_name == 'true' || $cff_show_video_name == true ) ? $cff_show_video_name = true : $cff_show_video_name = false;
    if( $atts[ 'showvideoname' ] == 'false' ) $cff_show_video_name = false;

    $cff_show_video_desc = $atts[ 'showvideodesc' ];
    ( $cff_show_video_desc == 'on' || $cff_show_video_desc == 'true' || $cff_show_video_desc == true ) ? $cff_show_video_desc = true : $cff_show_video_desc = false;
    if( $atts[ 'showvideodesc' ] == 'false' ) $cff_show_video_desc = false;

    $cff_video_cols = $atts['videocols'];

    $cff_videos_only = false;
    if ( ($cff_show_video_type && $cff_videos_source == 'videospage') && !$cff_show_albums_type && !$cff_show_links_type && !$cff_show_photos_type && !$cff_show_status_type && !$cff_show_event_type) $cff_videos_only = true;
    if( $cff_featured_post_active && !empty($atts['featuredpost']) ) $cff_videos_only = false;

    /********** LAYOUT **********/
    //Include string
    $cff_includes = $atts[ 'include' ];
    //Look for non-plural version of string in the types string in case user specifies singular in shortcode
    $cff_show_author = false;
    $cff_show_text = false;
    $cff_show_desc = false;
    $cff_show_shared_links = false;
    $cff_show_date = false;
    $cff_show_media = false;
    $cff_show_event_title = false;
    $cff_show_event_details = false;
    $cff_show_meta = false;
    $cff_show_link = false;
    $cff_show_like_box = false;
    if ( stripos($cff_includes, 'author') !== false ) $cff_show_author = true;
    if ( stripos($cff_includes, 'text') !== false ) $cff_show_text = true;
    if ( stripos($cff_includes, 'desc') !== false ) $cff_show_desc = true;
    if ( stripos($cff_includes, 'sharedlink') !== false ) $cff_show_shared_links = true;
    if ( stripos($cff_includes, 'date') !== false ) $cff_show_date = true;
    if ( stripos($cff_includes, 'media') !== false ) $cff_show_media = true;
    if ( stripos($cff_includes, 'eventtitle') !== false ) $cff_show_event_title = true;
    if ( stripos($cff_includes, 'eventdetail') !== false ) $cff_show_event_details = true;
    if ( stripos($cff_includes, 'social') !== false ) $cff_show_meta = true;
    if ( stripos($cff_includes, ',link') !== false ) $cff_show_link = true; //comma used to separate it from 'sharedlinks' - which also contains 'link' string
    if ( stripos($cff_includes, 'like') !== false ) $cff_show_like_box = true;

    //Exclude string
    $cff_excludes = $atts[ 'exclude' ];
    //Look for non-plural version of string in the types string in case user specifies singular in shortcode
    if ( stripos($cff_excludes, 'author') !== false ) $cff_show_author = false;
    if ( stripos($cff_excludes, 'text') !== false ) $cff_show_text = false;
    if ( stripos($cff_excludes, 'desc') !== false ) $cff_show_desc = false;
    if ( stripos($cff_excludes, 'sharedlink') !== false ) $cff_show_shared_links = false;
    if ( stripos($cff_excludes, 'date') !== false ) $cff_show_date = false;
    if ( stripos($cff_excludes, 'media') !== false ) $cff_show_media = false;
    if ( stripos($cff_excludes, 'eventtitle') !== false ) $cff_show_event_title = false;
    if ( stripos($cff_excludes, 'eventdetail') !== false ) $cff_show_event_details = false;
    if ( stripos($cff_excludes, 'social') !== false ) $cff_show_meta = false;
    if ( stripos($cff_excludes, ',link') !== false ) $cff_show_link = false; //comma used to separate it from 'sharedlinks' - which also contains 'link' string
    if ( stripos($cff_excludes, 'like') !== false ) $cff_show_like_box = false;

    $cff_preset_layout = $atts[ 'layout' ];
    //Default is thumbnail layout
    $cff_thumb_layout = false;
    $cff_half_layout = false;
    $cff_full_layout = true;
    if (($cff_preset_layout == 'thumb' || empty($cff_preset_layout)) && $cff_show_media) {
        $cff_thumb_layout = true;
    } else if ($cff_preset_layout == 'half'  && $cff_show_media) {
        $cff_half_layout = true;
    } else {
        $cff_full_layout = true;
    }

    //Get the media position
    $cff_media_position = $atts['mediaposition'];
    if ( $cff_thumb_layout || $cff_half_layout) $cff_media_position = 'below';

    //If the old shortcode option 'showauthor' is being used then apply it
    $cff_show_author_old = $atts[ 'showauthor' ];
    if( $cff_show_author_old == 'false' ) $cff_show_author = false;
    if( $cff_show_author_old == 'true' ) $cff_show_author = true;


    //LIGHTBOX
    $cff_disable_lightbox = $atts['disablelightbox'];
    ( $cff_disable_lightbox == 'on' || $cff_disable_lightbox == 'true' || $cff_disable_lightbox == true ) ? $cff_disable_lightbox = true : $cff_disable_lightbox = false;
    if( $atts[ 'disablelightbox' ] == 'false' ) $cff_disable_lightbox = false;
    
    /********** META **********/
    $cff_icon_style = 'cff-' . $atts[ 'iconstyle' ];
    $cff_meta_text_color = $atts[ 'socialtextcolor' ];
    $cff_meta_bg_color = $atts[ 'socialbgcolor' ];

    $cff_expand_comments = $atts['expandcomments'];
    if($cff_expand_comments == 'false') $cff_expand_comments = false;
    
    !isset( $atts['commentsnum'] ) ? $cff_comments_num = '4' : $cff_comments_num = $atts['commentsnum'];

    $cff_hide_comment_avatars = $atts['hidecommentimages'];
    if($cff_hide_comment_avatars == 'false') $cff_hide_comment_avatars = false;


    $cff_meta_styles = '';
    if ( !empty($cff_meta_text_color) || ( !empty($cff_meta_bg_color) && $cff_meta_bg_color !== '#' ) ) $cff_meta_styles = 'style="';
    if ( !empty($cff_meta_text_color) ) $cff_meta_styles .= 'color:#' . str_replace('#', '', $cff_meta_text_color) . ';';
    if ( !empty($cff_meta_bg_color) && $cff_meta_bg_color !== '#' ) $cff_meta_styles .= 'background-color:#' . str_replace('#', '', $cff_meta_bg_color) . ';';
    if ( !empty($cff_meta_text_color) || ( !empty($cff_meta_bg_color) && $cff_meta_bg_color !== '#' ) ) $cff_meta_styles .= '"';

    $cff_meta_link_color = 'style="color:#' . str_replace('#', '', $atts['sociallinkcolor']) . ';"';

    /********** TYPOGRAPHY **********/
    //See More text
    $cff_see_more_text = $atts[ 'seemoretext' ];
    $cff_see_less_text = $atts[ 'seelesstext' ];
    //See Less text
    //Title
    $cff_title_format = $atts[ 'textformat' ];
    if (empty($cff_title_format)) $cff_title_format = 'p';
    $cff_title_size = $atts[ 'textsize' ];
    $cff_title_weight = $atts[ 'textweight' ];
    $cff_title_color = $atts[ 'textcolor' ];


    $cff_title_styles = '';
    if( ( !empty($cff_title_size) && $cff_title_size != 'inherit' ) || ( !empty($cff_title_weight) && $cff_title_weight != 'inherit' ) || ( !empty($cff_title_color) && $cff_title_color !== '#' ) ) $cff_title_styles = 'style="';
        if ( !empty($cff_title_size) && $cff_title_size != 'inherit' ) $cff_title_styles .=  'font-size:' . $cff_title_size . 'px; ';
        if ( !empty($cff_title_weight) && $cff_title_weight != 'inherit' ) $cff_title_styles .= 'font-weight:' . $cff_title_weight . '; ';
        if ( !empty($cff_title_color) && $cff_title_color !== '#' ) $cff_title_styles .= 'color:#' . str_replace('#', '', $cff_title_color) . ';';
    if( ( !empty($cff_title_size) && $cff_title_size != 'inherit' ) || ( !empty($cff_title_weight) && $cff_title_weight != 'inherit' ) || ( !empty($cff_title_color) && $cff_title_color !== '#' ) ) $cff_title_styles .= '"';


    $cff_title_link = $atts[ 'textlink' ];
    //Text link color
    $cff_posttext_link_color = str_replace('#', '', $atts['textlinkcolor'] );

    ( $cff_title_link == 'on' || $cff_title_link == 'true' || $cff_title_link == true ) ? $cff_title_link = true : $cff_title_link = false;
    if( $atts[ 'textlink' ] == 'false' ) $cff_title_link = false;

    //Author
    $cff_author_size = $atts[ 'authorsize' ];
    $cff_author_color = $atts[ 'authorcolor' ];

    $cff_author_styles = '';
    if( ( !empty($cff_author_size) && $cff_author_size != 'inherit' ) || ( !empty($cff_author_color) && $cff_author_color !== '#' ) ) $cff_author_styles = 'style="';
        if ( !empty($cff_author_size) && $cff_author_size != 'inherit' ) $cff_author_styles .=  'font-size:' . $cff_author_size . 'px; ';
        if ( !empty($cff_author_color) && $cff_author_color !== '#' ) $cff_author_styles .= 'color:#' . str_replace('#', '', $cff_author_color) . ';';
    if( ( !empty($cff_author_size) && $cff_author_size != 'inherit' ) || ( !empty($cff_author_color) && $cff_author_color !== '#' ) ) $cff_author_styles .= '"';

    //Description
    $cff_body_size = $atts[ 'descsize' ];
    $cff_body_weight = $atts[ 'descweight' ];
    $cff_body_color = $atts[ 'desccolor' ];

    $cff_body_styles = '';
    if( ( !empty($cff_body_size) && $cff_body_size != 'inherit' ) || ( !empty($cff_body_weight) && $cff_body_weight != 'inherit' ) || ( !empty($cff_body_color) && $cff_body_color !== '#' ) ) $cff_body_styles = 'style="';
        if ( !empty($cff_body_size) && $cff_body_size != 'inherit' ) $cff_body_styles .=  'font-size:' . $cff_body_size . 'px; ';
        if ( !empty($cff_body_weight) && $cff_body_weight != 'inherit' ) $cff_body_styles .= 'font-weight:' . $cff_body_weight . '; ';
        if ( !empty($cff_body_color) && $cff_body_color !== '#' ) $cff_body_styles .= 'color:#' . str_replace('#', '', $cff_body_color) . ';';
    if( ( !empty($cff_body_size) && $cff_body_size != 'inherit' ) || ( !empty($cff_body_weight) && $cff_body_weight != 'inherit' ) || ( !empty($cff_body_color) && $cff_body_color !== '#' ) ) $cff_body_styles .= '"';

    //Shared link title
    $cff_link_title_format = $atts[ 'linktitleformat' ];
    if (empty($cff_link_title_format)) $cff_link_title_format = 'p';
    $cff_link_title_size = $atts[ 'linktitlesize' ];
    $cff_link_title_color = str_replace('#', '', $atts[ 'linktitlecolor' ]);
    $cff_link_url_color = $atts[ 'linkurlcolor' ];

    $cff_link_title_styles = '';
    if ( !empty($cff_link_title_size) && $cff_link_title_size != 'inherit' ) $cff_link_title_styles =  'style="font-size:' . $cff_link_title_size . 'px;"';

    //Shared link box
    $cff_link_bg_color = $atts[ 'linkbgcolor' ];
    $cff_link_border_color = $atts[ 'linkbordercolor' ];
    $cff_disable_link_box = $atts['disablelinkbox'];
    ($cff_disable_link_box == 'true' || $cff_disable_link_box == 'on') ? $cff_disable_link_box = true : $cff_disable_link_box = false;

    $cff_full_link_images = $atts['fulllinkimages'];
    ($cff_full_link_images == 'true' || $cff_full_link_images == 'on') ? $cff_full_link_images = true : $cff_full_link_images = false;

    
    $cff_link_box_styles = '';
    if( !empty($cff_link_border_color) || (!empty($cff_link_bg_color) && $cff_link_bg_color !== '#') ) $cff_link_box_styles = 'style="';
        if ( !empty($cff_link_border_color) ) $cff_link_box_styles .=  'border: 1px solid #' . str_replace('#', '', $cff_link_border_color) . '; ';
        if ( !empty($cff_link_bg_color) && $cff_link_bg_color !== '#' ) $cff_link_box_styles .= 'background-color: #' . str_replace('#', '', $cff_link_bg_color) . ';';
    if( !empty($cff_link_border_color) || (!empty($cff_link_bg_color) && $cff_link_bg_color !== '#') ) $cff_link_box_styles .= '"';


    //Event Title
    $cff_event_title_format = $atts[ 'eventtitleformat' ];
    if (empty($cff_event_title_format)) $cff_event_title_format = 'p';
    $cff_event_title_size = $atts[ 'eventtitlesize' ];
    $cff_event_title_weight = $atts[ 'eventtitleweight' ];
    $cff_event_title_color = $atts[ 'eventtitlecolor' ];


    $cff_event_title_styles = '';
    if( ( !empty($cff_event_title_size) && $cff_event_title_size != 'inherit' ) || ( !empty($cff_event_title_weight) && $cff_event_title_weight != 'inherit' ) || ( !empty($cff_event_title_color) && $cff_event_title_color !== '#' ) ) $cff_event_title_styles = 'style="';
        if ( !empty($cff_event_title_size) && $cff_event_title_size != 'inherit' ) $cff_event_title_styles .=  'font-size:' . $cff_event_title_size . 'px; ';
        if ( !empty($cff_event_title_weight) && $cff_event_title_weight != 'inherit' ) $cff_event_title_styles .= 'font-weight:' . $cff_event_title_weight . '; ';
        if ( !empty($cff_event_title_color) && $cff_event_title_color !== '#' ) $cff_event_title_styles .= 'color:#' . str_replace('#', '', $cff_event_title_color) . ';';
    if( ( !empty($cff_event_title_size) && $cff_event_title_size != 'inherit' ) || ( !empty($cff_event_title_weight) && $cff_event_title_weight != 'inherit' ) || ( !empty($cff_event_title_color) && $cff_event_title_color !== '#' ) ) $cff_event_title_styles .= '"';


    $cff_event_title_link = $atts[ 'eventtitlelink' ];
    ( $cff_event_title_link == 'on' || $cff_event_title_link == 'true' || $cff_event_title_link == true ) ? $cff_event_title_link = true : $cff_event_title_link = false;
    if( $atts[ 'eventtitlelink' ] == 'false' ) $cff_event_title_link = false;

    //Event Date
    $cff_event_date_size = $atts[ 'eventdatesize' ];
    $cff_event_date_weight = $atts[ 'eventdateweight' ];
    $cff_event_date_color = $atts[ 'eventdatecolor' ];
    $cff_event_date_position = $atts[ 'eventdatepos' ];
    $cff_event_date_formatting = $atts[ 'eventdateformat' ];
    $cff_event_date_custom = $atts[ 'eventdatecustom' ];

    $cff_event_date_styles = '';
    if( ( !empty($cff_event_date_size) && $cff_event_date_size != 'inherit' ) || ( !empty($cff_event_date_weight) && $cff_event_date_weight != 'inherit' ) || ( !empty($cff_event_date_color) && $cff_event_date_color !== '#' ) ) $cff_event_date_styles = 'style="';
        if ( !empty($cff_event_date_size) && $cff_event_date_size != 'inherit' ) $cff_event_date_styles .=  'font-size:' . $cff_event_date_size . 'px; ';
        if ( !empty($cff_event_date_weight) && $cff_event_date_weight != 'inherit' ) $cff_event_date_styles .= 'font-weight:' . $cff_event_date_weight . '; ';
        if ( !empty($cff_event_date_color) && $cff_event_date_color !== '#' ) $cff_event_date_styles .= 'color:#' . str_replace('#', '', $cff_event_date_color) . ';';
    if( ( !empty($cff_event_date_size) && $cff_event_date_size != 'inherit' ) || ( !empty($cff_event_date_weight) && $cff_event_date_weight != 'inherit' ) || ( !empty($cff_event_date_color) && $cff_event_date_color !== '#' ) ) $cff_event_date_styles .= '"';


    //Event Details
    $cff_event_details_size = $atts[ 'eventdetailssize' ];
    $cff_event_details_weight = $atts[ 'eventdetailsweight' ];
    $cff_event_details_color = $atts[ 'eventdetailscolor' ];
    $cff_event_link_color = str_replace('#', '', $atts[ 'eventlinkcolor' ]);

    $cff_event_details_styles = '';
    if( ( !empty($cff_event_details_size) && $cff_event_details_size != 'inherit' ) || ( !empty($cff_event_details_weight) && $cff_event_details_weight != 'inherit' ) || ( !empty($cff_event_details_color) && $cff_event_details_color !== '#' ) ) $cff_event_details_styles = 'style="';
        if ( !empty($cff_event_details_size) && $cff_event_details_size != 'inherit' ) $cff_event_details_styles .=  'font-size:' . $cff_event_details_size . 'px; ';
        if ( !empty($cff_event_details_weight) && $cff_event_details_weight != 'inherit' ) $cff_event_details_styles .= 'font-weight:' . $cff_event_details_weight . '; ';
        if ( !empty($cff_event_details_color) && $cff_event_details_color !== '#' ) $cff_event_details_styles .= 'color:#' . str_replace('#', '', $cff_event_details_color) . ';';
    if( ( !empty($cff_event_details_size) && $cff_event_details_size != 'inherit' ) || ( !empty($cff_event_details_weight) && $cff_event_details_weight != 'inherit' ) || ( !empty($cff_event_details_color) && $cff_event_details_color !== '#' ) ) $cff_event_details_styles .= '"';

    //No Upcoming Events text
    $cff_no_events_text = $atts['noeventstext'];
    if (!isset($cff_no_events_text) || empty($cff_no_events_text)) $cff_no_events_text = 'No upcoming events';

    //Date
    $cff_date_position = $atts[ 'datepos' ];
    if (!isset($cff_date_position)) $cff_date_position = 'below';
    $cff_date_size = $atts[ 'datesize' ];
    $cff_date_weight = $atts[ 'dateweight' ];
    $cff_date_color = $atts[ 'datecolor' ];

    $cff_date_styles = '';
    if( ( !empty($cff_date_size) && $cff_date_size != 'inherit' ) || ( !empty($cff_date_weight) && $cff_date_weight != 'inherit' ) || ( !empty($cff_date_color) && $cff_date_color !== '#' ) ) $cff_date_styles = 'style="';
        if ( !empty($cff_date_size) && $cff_date_size != 'inherit' ) $cff_date_styles .=  'font-size:' . $cff_date_size . 'px; ';
        if ( !empty($cff_date_weight) && $cff_date_weight != 'inherit' ) $cff_date_styles .= 'font-weight:' . $cff_date_weight . '; ';
        if ( !empty($cff_date_color) && $cff_date_color !== '#' ) $cff_date_styles .= 'color:#' . str_replace('#', '', $cff_date_color) . ';';
    if( ( !empty($cff_date_size) && $cff_date_size != 'inherit' ) || ( !empty($cff_date_weight) && $cff_date_weight != 'inherit' ) || ( !empty($cff_date_color) && $cff_date_color !== '#' ) ) $cff_date_styles .= '"';    

    $cff_date_before = isset( $options[ 'cff_date_before' ] ) ? stripslashes( esc_attr( $options[ 'cff_date_before' ] ) ) : '';
    $cff_date_after = isset( $options[ 'cff_date_after' ] ) ? stripslashes( esc_attr( $options[ 'cff_date_after' ] ) ) : '';

    //Set user's timezone based on setting
    $cff_timezone = $atts['timezone'];
    $cff_orig_timezone = date_default_timezone_get();
    date_default_timezone_set($cff_timezone);

    //Posted ago strings
    $cff_date_translate_strings = array(
        'cff_translate_second' => $atts['secondtext'],
        'cff_translate_second' => $atts['secondtext'],
        'cff_translate_seconds' => $atts['secondstext'],
        'cff_translate_minute' => $atts['minutetext'],
        'cff_translate_minutes' => $atts['minutestext'],
        'cff_translate_hour' => $atts['hourtext'],
        'cff_translate_hours' => $atts['hourstext'],
        'cff_translate_day' => $atts['daytext'],
        'cff_translate_days' => $atts['daystext'],
        'cff_translate_week' => $atts['weektext'],
        'cff_translate_weeks' => $atts['weekstext'],
        'cff_translate_month' => $atts['monthtext'],
        'cff_translate_months' => $atts['monthstext'],
        'cff_translate_year' => $atts['yeartext'],
        'cff_translate_years' => $atts['yearstext'],
        'cff_translate_ago' => $atts['agotext']
    );

    //Link to Facebook
    $cff_link_size = $atts[ 'linksize' ];
    $cff_link_weight = $atts[ 'linkweight' ];
    $cff_link_color = $atts[ 'linkcolor' ];

    $cff_link_styles = '';
    if( ( !empty($cff_link_size) && $cff_link_size != 'inherit' ) || ( !empty($cff_link_weight) && $cff_link_weight != 'inherit' ) || ( !empty($cff_link_color) && $cff_link_color !== '#' ) ) $cff_link_styles = 'style="';
        if ( !empty($cff_link_size) && $cff_link_size != 'inherit' ) $cff_link_styles .=  'font-size:' . $cff_link_size . 'px; ';
        if ( !empty($cff_link_weight) && $cff_link_weight != 'inherit' ) $cff_link_styles .= 'font-weight:' . $cff_link_weight . '; ';
        if ( !empty($cff_link_color) && $cff_link_color !== '#' ) $cff_link_styles .= 'color:#' . str_replace('#', '', $cff_link_color) . ';';
    if( ( !empty($cff_link_size) && $cff_link_size != 'inherit' ) || ( !empty($cff_link_weight) && $cff_link_weight != 'inherit' ) || ( !empty($cff_link_color) && $cff_link_color !== '#' ) ) $cff_link_styles .= '"';

    $cff_facebook_link_text = $atts[ 'facebooklinktext' ];
    $cff_facebook_share_text = $atts[ 'sharelinktext' ];
    if ($cff_facebook_share_text == '') $cff_facebook_share_text = 'Share';


    //Show Facebook link
    $cff_show_facebook_link = $atts[ 'showfacebooklink' ];
    ( $cff_show_facebook_link == 'on' || $cff_show_facebook_link == 'true' || $cff_show_facebook_link == true ) ? $cff_show_facebook_link = true : $cff_show_facebook_link = false;
    if( $atts[ 'showfacebooklink' ] === 'false' ) $cff_show_facebook_link = false;


    //Show Share link
    $cff_show_facebook_share = $atts[ 'showsharelink' ];
    ( $cff_show_facebook_share == 'on' || $cff_show_facebook_share == 'true' || $cff_show_facebook_share == true ) ? $cff_show_facebook_share = true : $cff_show_facebook_share = false;
    if( $atts[ 'showsharelink' ] === 'false' ) $cff_show_facebook_share = false;

    $cff_view_link_text = $atts[ 'viewlinktext' ];
    $cff_link_to_timeline = $atts[ 'linktotimeline' ];
    /********** MISC **********/
    //Like Box styles
    $cff_likebox_bg_color = $atts[ 'likeboxcolor' ];

    $cff_like_box_text_color = $atts[ 'likeboxtextcolor' ];
    $cff_like_box_colorscheme = 'light';
    if ($cff_like_box_text_color == 'white') $cff_like_box_colorscheme = 'dark';

    $cff_likebox_width = $atts[ 'likeboxwidth' ];
    if ( is_numeric(substr($cff_likebox_width, -1, 1)) ) $cff_likebox_width = $cff_likebox_width . 'px';

    $cff_likebox_height = $atts[ 'likeboxheight' ];
    $cff_likebox_height = preg_replace('/px$/', '', $cff_likebox_height);

    if ( !isset($cff_likebox_width) || empty($cff_likebox_width) || $cff_likebox_width == '' ) $cff_likebox_width = '';
    $cff_like_box_faces = $atts[ 'likeboxfaces' ];
    if ( !isset($cff_like_box_faces) || empty($cff_like_box_faces) ) $cff_like_box_faces = 'false';
    $cff_like_box_border = $atts[ 'likeboxborder' ];
    if ($cff_like_box_border) {
        $cff_like_box_border = 'true';
    } else {
        $cff_like_box_border = 'false';
    }

    $cff_like_box_cover = $atts[ 'likeboxcover' ];
    ( $cff_like_box_cover == 'on' || $cff_like_box_cover == 'true' || $cff_like_box_cover == true ) ? $cff_like_box_cover = 'false' : $cff_like_box_cover = 'true';
    if( $atts[ 'likeboxcover' ] === 'false' ) $cff_like_box_cover = 'true';


    $cff_like_box_small_header = $atts[ 'likeboxsmallheader' ];
    if ($cff_like_box_small_header) {
        $cff_like_box_small_header = 'true';
    } else {
        $cff_like_box_small_header = 'false';
    }

    $cff_like_box_hide_cta = $atts[ 'likeboxhidebtn' ];
    if ($cff_like_box_hide_cta) {
        $cff_like_box_hide_cta = 'true';
    } else {
        $cff_like_box_hide_cta = 'false';
    }

    //Compile Like box styles
    $cff_likebox_styles = 'style="width: ' . $cff_likebox_width . ';';
    if ( !empty($cff_likebox_bg_color) ) $cff_likebox_styles .= ' background-color:#' . str_replace('#', '', $cff_likebox_bg_color) . ';';

    //Set the left margin on the like box based on how it's being displayed
    if ( (!empty($cff_likebox_bg_color) && $cff_likebox_bg_color != '#') || ($cff_like_box_faces == 'true' || $cff_like_box_faces == 'on') ) $cff_likebox_styles .= ' margin-left: 0px;';  

    $cff_likebox_styles .= '"';

    //Get feed header settings
    $cff_header_bg_color = $atts['headerbg'];
    $cff_header_padding = $atts['headerpadding'];
    if ( is_numeric(substr($cff_header_padding, -1, 1)) ) $cff_header_padding = $cff_header_padding . 'px';

    $cff_header_text_size = $atts['headertextsize'];
    $cff_header_text_weight = $atts['headertextweight'];
    $cff_header_text_color = $atts['headertextcolor'];

    //Compile feed header styles
    $cff_header_styles = '';
    if( ( !empty($cff_header_bg_color) && $cff_header_bg_color !== '#' ) || !empty($cff_header_padding) || ( !empty($cff_header_text_size) && $cff_header_text_size != 'inherit' ) || ( !empty($cff_header_text_weight) && $cff_header_text_weight != 'inherit' ) || (!empty($cff_header_text_color) && $cff_header_text_color !== '#') ) $cff_header_styles = 'style="';
        if ( !empty($cff_header_bg_color) && $cff_header_bg_color !== '#' ) $cff_header_styles .= 'background-color: #' . str_replace('#', '', $cff_header_bg_color) . '; ';
        if ( !empty($cff_header_padding) ) $cff_header_styles .= 'padding: ' . $cff_header_padding . '; ';
        if ( !empty($cff_header_text_size) && $cff_header_text_size != 'inherit' ) $cff_header_styles .= 'font-size: ' . $cff_header_text_size . 'px; ';
        if ( !empty($cff_header_text_weight) && $cff_header_text_weight != 'inherit' ) $cff_header_styles .= 'font-weight: ' . $cff_header_text_weight . '; ';
        if ( !empty($cff_header_text_color) && $cff_header_text_color !== '#' ) $cff_header_styles .= 'color: #' . str_replace('#', '', $cff_header_text_color) . '; ';
    if( ( !empty($cff_header_bg_color) && $cff_header_bg_color !== '#' ) || !empty($cff_header_padding) || ( !empty($cff_header_text_size) && $cff_header_text_size != 'inherit' ) || ( !empty($cff_header_text_weight) && $cff_header_text_weight != 'inherit' ) || (!empty($cff_header_text_color) && $cff_header_text_color !== '#') ) $cff_header_styles .= '"';

    //Photos translate text
    $cff_translate_photos_text = $atts['photostext'];
    if (!isset($cff_translate_photos_text) || empty($cff_translate_photos_text)) $cff_translate_photos_text = 'photos';


    //Video
    //Dimensions
    $cff_video_height = $atts[ 'videoheight' ];
    //Action
    $cff_video_action = $atts[ 'videoaction' ];
    //Separating Line
    $cff_sep_color = $atts[ 'sepcolor' ];
    if (empty($cff_sep_color)) $cff_sep_color = 'ddd';
    $cff_sep_size = $atts[ 'sepsize' ];
    //If empty then set a 0px border
    if ( empty($cff_sep_size) || $cff_sep_size == '' ) {
        $cff_sep_size = 0;
        //Need to set a color otherwise the CSS is invalid
        $cff_sep_color = 'fff';
    }

    $cff_post_bg_color = str_replace('#', '', $atts['postbgcolor']);
    $cff_post_rounded = $atts['postcorners'];
    ($cff_post_bg_color !== '#' && $cff_post_bg_color !== '') ? $cff_post_bg_color_check = true : $cff_post_bg_color_check = false;
    ($cff_sep_color !== '#' && $cff_sep_color !== '') ? $cff_sep_color_check = true : $cff_sep_color_check = false;

    //CFF item styles
    $cff_item_styles = '';
    if( $cff_sep_color_check || $cff_post_bg_color_check ){
        $cff_item_styles = 'style="';
        if($cff_sep_color_check && !$cff_post_bg_color_check) $cff_item_styles .= 'border-bottom: ' . $cff_sep_size . 'px solid #' . str_replace('#', '', $cff_sep_color) . '; ';
        if($cff_post_bg_color_check) $cff_item_styles .= 'background-color: #' . $cff_post_bg_color . '; ';
        if(isset($cff_post_rounded) && $cff_post_rounded !== '0') $cff_item_styles .= '-webkit-border-radius: ' . $cff_post_rounded . 'px; -moz-border-radius: ' . $cff_post_rounded . 'px; border-radius: ' . $cff_post_rounded . 'px; ';
        $cff_item_styles .= '"';
    }
   
    //Text limits
    $title_limit = $atts['textlength'];
    if (!isset($title_limit)) $title_limit = 9999;
    $body_limit = $atts['desclength'];

    //Assign the Access Token and Page ID variables
    $access_token = trim( $atts['accesstoken'] );
    $page_id = trim( $atts['id'] );

    //If user pastes their full URL into the Page ID field then strip it out
    $cff_facebook_string = 'facebook.com';
    ( stripos($page_id, $cff_facebook_string) !== false) ? $cff_page_id_url_check = true : $cff_page_id_url_check = false;
    
    if ( $cff_page_id_url_check === true ) {
        //Remove trailing slash if exists
        $page_id = preg_replace('{/$}', '', $page_id);
        //Get last part of url
        $page_id = substr( $page_id, strrpos( $page_id, '/' )+1 );
    }

    //If the Page ID contains a query string at the end then remove it
    if ( stripos( $page_id, '?') !== false ) $page_id = substr($page_id, 0, strrpos($page_id, '?'));


    //Get show posts attribute. If not set then default to 25
    $show_posts = $atts['num'];
    if (empty($show_posts)) $show_posts = 25;
    if ( $show_posts == 0 || $show_posts == 'undefined' ) $show_posts = 25;

    //If the 'Enter my own Access Token' box is unchecked then don't use the user's access token, even if there's one in the field
    get_option('cff_show_access_token') ? $cff_show_access_token = true : $cff_show_access_token = false;

    //Regular tokens
    $access_token_array = array(
        '214840262228845|jDMpRKuUA6pE50zkcLI_n0O_xo8',
        '109107172826653|2ZWWn9b2kGF4LD3IWdgvFSV5Icw',
        '1089043857827104|sQP6VAF9GYWw63F6hoo5ZbkmbL4',
        '559167130910609|_k3Jp7zVjgcJYHaPEppyxBAbpJs',
        '1710591165888924|Ng5pfmT-qoYtvvcJ1cz8vJJxJvc',
        '994360207285429|lL1a1xxcWYASdw0Vr_qwQw8NZAM',
        '783129931822943|RDyZgqMwI51LNDhU9EYxx2JK5kA',
        '480939705434761|joaCCxWk05Ik4t4tli7Mzvg0rt8'
    );
    //FQL tokens
    $access_token_array_fql = array(
        '300694180076642|-cozSG1L4topnAqQOwaIEpy4Ufk',
        '439271626171835|-V79s0TIUVsjj_5lgc6ydVvaFZ8',
        '188877464498533|gObD45qMCG-uE9WGVt3-djx-6Sw',
        '636437039752698|Tt-zXlDy-Nu4CCkNteGfcUe65ow',
        '1448491852049169|eUTjw_pIVoPzC1R1pxVQhmtFqQ0'
    );

    //If Access Token is blank or 'Use own Access Token' setting is unchecked then default to a regular token
    if ($access_token == '' || !$cff_show_access_token) $access_token = $access_token_array[rand(0, 7)];

    //Use an FQL token
    if ( $cff_photos_only && $cff_is_group ) $access_token = $access_token_array_fql[rand(0, 4)];

    //Reviews Access Token
    $page_access_token = $atts['pagetoken'];

    //Check whether a Page ID has been defined
    if ($page_id == '') {
        echo "Please enter the Page ID of the Facebook feed you'd like to display.  You can do this in either the Custom Facebook Feed plugin settings or in the shortcode itself. For example [custom-facebook-feed id=YOUR_PAGE_ID_HERE].<br /><br />";
        return false;
    }

    //Is it SSL?
    $cff_ssl = '';
    if (is_ssl()) $cff_ssl = '&return_ssl_resources=true';

    //Use posts? or feed?
    $show_others = $atts['others'];
    $show_posts_by = $atts['showpostsby'];
    $graph_query = 'posts';
    $cff_show_only_others = false;

    //If 'others' shortcode option is used then it overrides any other option
    if ($show_others) {

        //Show posts by everyone
        if ( $show_others == 'on' || $show_others == 'true' || $show_others == true || $cff_is_group ) $graph_query = 'feed';

        //Only show posts by me
        if ( $show_others == 'false' ) $graph_query = 'posts';

    } else {
    //Else use the settings page option or the 'showpostsby' shortcode option

        //Only show posts by me
        if ( $show_posts_by == 'me' ) $graph_query = 'posts';

        //Show posts by everyone
        if ( $show_posts_by == 'others' || $cff_is_group ) $graph_query = 'feed';

        //Show posts ONLY by others
        if ( $show_posts_by == 'onlyothers' && !$cff_is_group ) {
            $graph_query = 'feed';
            $cff_show_only_others = true;
        }

    }

    //If the limit isn't set then set it to be 5 more than the number of posts defined
    if ( isset($atts['limit']) && $atts['limit'] !== '' ) {
        $cff_post_limit = $atts['limit'];
    } else {
        $cff_post_limit = intval(intval($show_posts) + 7);
    }
    if( $cff_post_limit >= 100 ) $cff_post_limit = 100;

    //Calculate the cache time in seconds
    if($cff_cache_time_unit == 'minutes') $cff_cache_time_unit = 60;
    if($cff_cache_time_unit == 'hours') $cff_cache_time_unit = 60*60;
    if($cff_cache_time_unit == 'days') $cff_cache_time_unit = 60*60*24;
    $cache_seconds = $cff_cache_time * $cff_cache_time_unit;

    //Set like box variable
    //If there are more than one page id then use the first one
    isset( $options[ 'cff_app_id' ] ) && !empty( $options[ 'cff_app_id' ] ) ? $cff_app_id = $options[ 'cff_app_id' ] : $cff_app_id = '712681982206086';
    $cff_like_box_params = '&appId=' .$cff_app_id;
    $like_box_page_id = explode(",", str_replace(' ', '', $page_id) );
    
    //Start Like Box
    $like_box = '<';
    //If the Like Box is at the top then change the element from a div so that it doesn't interfere with the "nth-of-type" used for grids in CSS
    ($cff_like_box_position == 'top') ? $like_box .= 'section' : $like_box .= 'div';
    $like_box .= ' class="cff-likebox';

    if ($cff_like_box_outside) $like_box .= ' cff-outside';
    $like_box .= ($cff_like_box_position == 'top') ? ' cff-top' : ' cff-bottom';
    $like_box .= '" ><script src="https://connect.facebook.net/' . $cff_locale . '/all.js#xfbml=1'.$cff_like_box_params.'"></script><div class="fb-page" data-href="https://www.facebook.com/'.$like_box_page_id[0].'" data-width="'.$cff_likebox_width.'" data-hide-cover="'.$cff_like_box_cover.'" data-show-facepile="'.$cff_like_box_faces.'" data-small-header="'.$cff_like_box_small_header.'" data-hide-cta="'.$cff_like_box_hide_cta.'" data-show-posts="false" data-adapt-container-width="true"><div class="fb-xfbml-parse-ignore"><blockquote cite="https://www.facebook.com/'.$like_box_page_id[0].'"><a href="https://www.facebook.com/'.$like_box_page_id[0].'">'.$cff_facebook_link_text.'</a></blockquote></div></div><div id="fb-root"></div></';

    ($cff_like_box_position == 'top') ? $like_box .= 'section' : $like_box .= 'div';
    $like_box .= '>';

    //Don't show like box if it's a group
    if($cff_is_group) $like_box = '';

    //Feed header
    $cff_show_header = $atts['showheader'];
    ($cff_show_header == 'true' || $cff_show_header == 'on') ? $cff_show_header = true : $cff_show_header = false;

    $cff_header_outside = $atts['headeroutside'];
    ($cff_header_outside == 'true' || $cff_header_outside == 'on') ? $cff_header_outside = true : $cff_header_outside = false;

    $cff_header_text = $atts['headertext'];
    $cff_header_icon = $atts['headericon'];
    $cff_header_icon_color = $atts['headericoncolor'];
    $cff_header_icon_size = $atts['headericonsize'];

    $cff_header = '<h3 class="cff-header';
    if ($cff_header_outside) $cff_header .= ' cff-outside';
    $cff_header .= '" ' . $cff_header_styles . '>';
    $cff_header .= '<i class="fa fa-' . $cff_header_icon . '"';
    if(!empty($cff_header_icon_color) || !empty($cff_header_icon_size)) $cff_header .= ' style="';
    if(!empty($cff_header_icon_color)) $cff_header .= 'color: #' . str_replace('#', '', $cff_header_icon_color) . ';';
    if(!empty($cff_header_icon_size)) $cff_header .= ' font-size: ' . $cff_header_icon_size . 'px;';
    if(!empty($cff_header_icon_color) || !empty($cff_header_icon_size))$cff_header .= '"';
    $cff_header .= '></i>';
    $cff_header .= '<span class="cff-header-text" style="height: '.$cff_header_icon_size.'px;">' . $cff_header_text . '</span>';
    $cff_header .= '</h3>';

    //Misc Settings
    $cff_nofollow = $atts['nofollow'];
    ( $cff_nofollow == 'on' || $cff_nofollow == 'true' || $cff_nofollow == true ) ? $cff_nofollow = true : $cff_nofollow = false;
    if( $atts[ 'nofollow' ] == 'false' ) $cff_nofollow = false;
    ( $cff_nofollow ) ? $cff_nofollow = ' rel="nofollow"' : $cff_nofollow = '';

    //Narrow styles
    $cff_enable_narrow = $atts['enablenarrow'];
    ($cff_enable_narrow == 'true' || $cff_enable_narrow == 'on') ? $cff_enable_narrow = true : $cff_enable_narrow = false;

    //If the number of posts is set to zero then don't show any and set limit to one
    if ( ($atts['num'] == '0' || $atts['num'] == 0) && $atts['num'] !== ''){
        $show_posts = 0;
        $cff_post_limit = 1;
    }

    //***START FEED***
    $cff_content = '';

    //Add the page header to the outside of the top of feed
    if ($cff_show_header && $cff_header_outside) $cff_content .= $cff_header;

    //Add like box to the outside of the top of feed
    if ($cff_like_box_position == 'top' && $cff_show_like_box && $cff_like_box_outside) $cff_content .= $like_box;

    //Create CFF container HTML
    $cff_content .= '<div class="cff-wrapper">';
    $cff_content .= '<div id="cff" ';
    if( !empty($title_limit) ) $cff_content .= 'data-char="'.$title_limit.'" ';
    $cff_content .= 'class="';
    if( !empty($cff_class) ) $cff_content .= $cff_class . ' ';

    // Hook for adding classes to the #cff element
    $classes = '';
    $classes .= apply_filters( 'cff_feed_class', $classes, $atts ).' ';
    $cff_content .= $classes;

    if ( !empty($cff_feed_height) ) $cff_content .= 'cff-fixed-height ';
    if ( $cff_thumb_layout ) $cff_content .= 'cff-thumb-layout ';
    if ( $cff_half_layout ) $cff_content .= 'cff-half-layout ';
    if ( !$cff_enable_narrow ) $cff_content .= 'cff-disable-narrow ';
    if ( $cff_feed_width_resp ) $cff_content .= 'cff-width-resp';
    //Lightbox extension
    if ( $cff_disable_lightbox && ($atts['lightbox'] == 'true' || $atts['lightbox'] == 'on') ) $cff_content .= ' cff-lightbox';
    if ( !$cff_disable_lightbox ) $cff_content .= ' cff-lb';
    $cff_content .= '" ' . $cff_feed_styles;
    $cff_content .= ' data-fb-text="'.$cff_facebook_link_text.'"';

    //Add the absolute path to the container to be used in the connect.php file for group albums
    if($cff_albums_only && $cff_albums_source == 'photospage' && $cff_is_group) $cff_content .= ' data-group="true" ';

    // $cff_content .= apply_filters('cff_data_atts',$cff_content,$atts).' ';
    if( $cff_carousel_active ){
        if( function_exists('cff_carousel_data_atts') ) $cff_content .= cff_carousel_data_atts( $atts );
    }

    $cff_content .= '>';
    //Add the page header to the inside of the top of feed
    if ($cff_show_header && !$cff_header_outside) $cff_content .= $cff_header;

    //Add like box to the inside of the top of feed
    if ($cff_like_box_position == 'top' && $cff_show_like_box && !$cff_like_box_outside) $cff_content .= $like_box;
    //Limit var
    $i = 0;


    //Multifeed extension
    ( $cff_ext_multifeed_active ) ? $page_ids = cff_multifeed_ids($page_id) : $page_ids = array($page_id);

    //Define array for post items
    $cff_posts_array = array();

    //LOOP THROUGH PAGE IDs
    foreach ( $page_ids as $page_id ) {
    
        //EVENTS ONLY
        if ($cff_events_only && $cff_events_source == 'eventspage'){

            //Get the user's ID
            $get_page_info = cff_fetchUrl('https://graph.facebook.com/' . $page_id . '?fields=name,id&access_token=' . $access_token);
            $page_info = json_decode($get_page_info);
            //Get user ID
            isset($page_info->id) ? $u_id = $page_info->id : $u_id = '';

            //Add 6 hours to the current time. This means events will still be shown for 6 hours after their start time has passed.
            $cff_event_offset_time = '-' . $cff_event_offset . ' hours';
            $curtimeplus = strtotime($cff_event_offset_time, time());

            //Start time string
            $cff_start_time_string = "&since=".$curtimeplus;

            //Date range extension
            if ( $cff_ext_date_active && ( !empty($atts['from']) || !empty($atts['until']) ) ) {

                ( !empty($atts['from']) ) ? $cff_date_from = strtotime($atts['from']) : $cff_date_from = $curtimeplus;
                ( !empty($atts['until']) ) ? $cff_date_until = strtotime($atts['until']) : $cff_date_until = $curtimeplus;

                $cff_start_time_string = cff_ext_date($cff_date_from, $cff_date_until);
            }

            //Events URL
            $cff_events_json_url = "https://graph.facebook.com/v2.6/".$u_id."/events/?fields=id,name,attending_count,cover,start_time,end_time,timezone,place,description,ticket_uri".$cff_start_time_string."&limit=200&access_token=" . $access_token . "&format=json-strings" . $cff_ssl;

            //Past events
            ( $atts['pastevents'] !== 'false' ) ? $cff_past_events = true : $cff_past_events = false;
            //Get past events. Limit must be set high to get all past events and be able to show the newest ones first
            if($cff_past_events) $cff_events_json_url = 'https://graph.facebook.com/v2.6/'.$u_id.'/events?fields=name,id,description,start_time,end_time,timezone,place,ticket_uri,cover&limit=200&until='.date('Y-m-d').'&access_token='.$access_token;

            //Group events
            if($cff_is_group && !$cff_past_events) $cff_events_json_url = 'https://graph.facebook.com/v2.6/' . $page_id . '/events?fields=name,id,description,start_time,end_time,timezone,ticket_uri,place,cover&limit=200&since='.date('Y-m-d').'&access_token=' . $access_token;

            //Featured Post extension
            if( $cff_featured_post_active && !empty($atts['featuredpost']) ) $cff_events_json_url = cff_featured_event_id( trim( $atts['featuredpost'] ), $access_token );

            if ($cff_cache_time != 0){

                $events_trans_items_arr = array(
                    'page_id' => $page_id,
                    'post_limit' => substr($cff_post_limit, 0, 3),
                    'page_type' => $cff_page_type
                );

                $trans_arr_item_count = 1;
                // $cff_ext_date_active = true;
                if($cff_ext_date_active){
                    $events_trans_items_arr['from'] = $atts['from'];
                    $events_trans_items_arr['until'] = $atts['until'];
                    // $events_trans_items_arr['from'] = '1234567890';
                    // $events_trans_items_arr['until'] = '0987654321';
                    $trans_arr_item_count = $trans_arr_item_count+2;
                }
                if( $cff_featured_post_active && !empty($atts['featuredpost']) ){
                    $events_trans_items_arr['featured_post'] = $atts['featuredpost'];
                    // $events_trans_items_arr['featured_post'] = '123124432144_2343253253253253552521512352';
                    $trans_arr_item_count++;
                }
                if($cff_past_events) $events_trans_items_arr['past_events'] = $cff_past_events;

                $arr_item_max_length = floor( 32/$trans_arr_item_count ); //Max length of 45 accounting for the 'cff_ej_' prefix and other options below
                $arr_item_max_length_half = floor($arr_item_max_length/2);

                $transient_name = 'cff_ej_';
                foreach ($events_trans_items_arr as $key => $value) {
                    if($value !== false){
                        if( $key == 'page_id' || $key == 'featured_post' || $key == 'from' || $key == 'until' ){
                        $transient_name .= substr($value, 0, $arr_item_max_length_half) . substr($value, $arr_item_max_length_half*-1);  //-10
                    }
                        if( $key == 'post_limit' ) $transient_name .= substr($value, 0, 3);
                        if( $key == 'page_type' || $key == 'past_events' ) $transient_name .= substr($value, 0, 1);
                    }
                }
                //Make sure it's not more than 45 chars
                $transient_name = substr($transient_name, 0, 45);


                // $transient_name = 'cff_ej_' . $page_id . '_' . strtotime($atts['from']) . strtotime($atts['until']) . $atts['featuredpost'] . $cff_past_events . $cff_page_type;

                if ( false === ( $events_json = get_transient( $transient_name ) ) || $events_json === null ) {
                    //Get the contents of the events page
                    $events_json = cff_fetchUrl($cff_events_json_url);
                    //Cache the JSON
                    set_transient( $transient_name, $events_json, $cache_seconds );
                } else {
                    $events_json = get_transient( $transient_name );
                    //If we can't find the transient then fall back to just getting the json from the api
                    if ($events_json == false) $events_json = cff_fetchUrl($cff_events_json_url);
                }
            } else {
                $events_json = cff_fetchUrl($cff_events_json_url);
            }            

            //Interpret data with JSON
            //Convert eid integer to a string otherwise json_decode returns it as a float
            // $events_json = preg_replace('/"eid":(\d+)/', '"eid":"$1"', $events_json);
            $events_json = preg_replace('/"id":(\d+)/', '"id":"$1"', $events_json);
            //If it's a Featured Post event then wrap it in a "data" object so that it's parsed the same way as regular events in the loop below
            if( $cff_featured_post_active && !empty($atts['featuredpost']) ) $events_json = '{"data": ['.$events_json.'] }';
            $event_data = json_decode($events_json);           


            //If there is no event data then show a message
            if( empty($event_data->data) ){

                //Check whether it's group events
                if($cff_is_group && strlen($access_token) < 50 ){
                    $cff_content .= '<div class="cff-error-msg"><p>Unable to display Facebook Group events <br /><a href="javascript:void(0);" id="cff-show-error" onclick="cffShowError()">Show Error Message</a></p>';
                    $cff_content .= '<script type="text/javascript">function cffShowError() { document.getElementById("cff-error-reason").style.display = "block"; document.getElementById("cff-show-error").style.display = "none"; }</script>';
                    $cff_content .= '</p><div id="cff-error-reason">';
                    $cff_content .= 'Error: "User" Access Token required by Facebook<br />Please refer to our <a href="https://smashballoon.com/custom-facebook-feed/docs/errors/" target="_blank">Error Message Reference</a> for a solution.';
                    $cff_content .= '</div></div>'; //End .cff-error-msg and #cff-error-reason
                    $cff_content .= '</div></div>'; //End #cff and .cff-wrapper

                    return $cff_content;
                }

            } else {

                //EVENTS LOOP
                foreach ($event_data->data as $event )
                {
                    //Only create posts for the amount of posts specified
                    // if ( $i == $show_posts ) break;
                    $i++;
                    isset($event->id) ? $id = $event->id : $id = '';

                    isset($event->name) ? $event_name = $event->name : $event_name = '';
                    isset($event->attending_count) ? $attending_count = $event->attending_count : $attending_count = '';

                    //Picture source
                    ( isset($event->cover) ) ? $pic_big = $event->cover->source : $pic_big = plugins_url( '/img/event-image.png' , __FILE__ );
                    ( $atts['eventimage'] == 'cropped' ) ? $crop_event_pic = true : $crop_event_pic = false;

                    isset($event->start_time) ? $start_time = $event->start_time : $start_time = '';
                    isset($event->end_time) ? $end_time = $event->end_time : $end_time = '';
                    isset($event->timezone) ? $timezone = $event->timezone : $timezone = '';
                    //Venue
                    isset($event->place->location->latitude) ? $venue_latitude = $event->place->location->latitude : $venue_latitude = '';
                    isset($event->place->location->longitude) ? $venue_longitude = $event->place->location->longitude : $venue_longitude = '';
                    isset($event->place->location->city) ? $venue_city = $event->place->location->city : $venue_city = '';
                    isset($event->place->location->state) ? $venue_state = $event->place->location->state : $venue_state = '';
                    isset($event->place->location->country ) ? $venue_country = $event->place->location->country : $venue_country = '';
                    isset($event->place->id) ? $venue_id = $event->place->id : $venue_id = '';
                    $venue_link = 'https://facebook.com/' . $venue_id;
                    isset($event->place->location->street) ? $venue_street = $event->place->location->street : $venue_street = '';
                    isset($event->place->location->zip) ? $venue_zip = $event->place->location->zip : $venue_zip = '';
                    isset($event->place->name) ? $location = $event->place->name : $location = '';

                    isset($event->description) ? $description = $event->description : $description = '';
                    $event_link = 'https://facebook.com/events/' . $id;
                    isset($event->ticket_uri) ? $ticket_uri = $event->ticket_uri : $ticket_uri = '';

                    $cff_buy_tickets_text = $atts['buyticketstext'];

                    //Event date
                    $event_time = $start_time;
                    //If timezone migration is enabled then remove last 5 characters
                    if ( strlen($event_time) == 24 ) $event_time = substr($event_time, 0, -5);


                    if (!empty($start_time)) $cff_event_date = '<p class="cff-date" '.$cff_event_date_styles.'><span class="cff-start-date">' . cff_eventdate(strtotime($event_time), $cff_event_date_formatting, $cff_event_date_custom) . '</span>';
                    if( isset($event->end_time) ) $cff_event_date .= '<span class="cff-end-date"> - ' . cff_eventdate(strtotime($end_time), $cff_event_date_formatting, $cff_event_date_custom) . '</span>';
                    $cff_event_date .= '</p>';


                    //Event title
                    $cff_event_title = '';
                    if ($cff_event_title_link) $cff_event_title .= '<a href="'.$event_link.'" '.$target.$cff_nofollow.'>';
                    $cff_event_title .= '<' . $cff_event_title_format . ' ' . $cff_event_title_styles . '>' . $event_name . '</' . $cff_event_title_format . '>';
                    if ($cff_event_title_link) $cff_event_title .= '</a>';
                    
                    //***************************//
                    //***CREATE THE EVENT HTML***//
                    //***************************//
                    $cff_post_item = '<div class="cff-item cff-event author-'. cff_to_slug($page_id);
                    if ($cff_post_bg_color_check) $cff_post_item .= ' cff-box';
                    $cff_post_item .= '" id="cff_'. $id .'" ' . $cff_item_styles . '>';
                    //Picture
                    if($cff_show_media){

                        //Fix Photon (Jetpack) issue
                        $cff_picture_querystring = '';
                        if (parse_url($pic_big, PHP_URL_QUERY)){
                            $picture_url_parts = parse_url($pic_big);
                            $cff_picture_querystring = $picture_url_parts['query'];
                        }

                        //Remove any quotes from event name to use in the image alt tag
                        $event_name = str_replace('"', "", $event_name);
                        $event_name = str_replace("'", "", $event_name);
                        //Alt text
                        isset( $event_name ) ? $cff_alt_text = strip_tags($event_name) : $cff_alt_text = $cff_facebook_link_text;

                        $cff_post_item .= '<a title="' . $cff_facebook_link_text . '" class="cff-photo';
                        if( $crop_event_pic ) $cff_post_item .= ' cff-crop';
                        $cff_post_item .= '" href="'.$event_link.'" '.$target.$cff_nofollow.'><img src="'. $pic_big .'" alt="'.$cff_alt_text.'" data-querystring="'.$cff_picture_querystring.'" /></a>';
                    }
                    //Start text wrapper
                    if ( ($cff_thumb_layout || $cff_half_layout) ) $cff_post_item .= '<div class="cff-details">';
                        //show event date above title
                        if ($cff_show_date && $cff_event_date_position == 'above') $cff_post_item .= $cff_event_date;
                        //Show event title
                        if ($cff_show_event_title && !empty($event_name)) $cff_post_item .= $cff_event_title;
                        //show event date below title
                        if ($cff_show_date && $cff_event_date_position !== 'above') $cff_post_item .= $cff_event_date;
                        //Show event details
                        if ($cff_show_event_details){
                            if (!empty($location)) $cff_post_item .= '<p class="cff-location" ' . $cff_event_details_styles . '>';
                            if (!empty($venue_id)) $cff_post_item .= '<a href="'. $venue_link .'" '.$target.$cff_nofollow.' style="color:#' . $cff_event_link_color . ';">';
                            if (!empty($location)) $cff_post_item .= '<b>' . $location . '</b>';
                            if (!empty($venue_id)) $cff_post_item .= '</a>';
                            if (!empty($venue_street)) $cff_post_item .= '<br />' . $venue_street;
                            if (!empty($venue_city)) $cff_post_item .= '<br />' . $venue_city . ', ' . $venue_state . ' &nbsp;' . $venue_zip;
                            $cff_map_text = $atts[ 'maptext' ];
                            if (!empty($venue_latitude)) $cff_post_item .= ' <a href="https://maps.google.com/maps?q=' . $venue_latitude . ',+' . $venue_longitude . '" '.$target.$cff_nofollow.' style="color:#' . $cff_event_link_color . ';">'.$cff_map_text.'</a>';
                            if (!empty($location)) $cff_post_item .= '</p>';
                            if (!empty($description)){
                                
                                $cff_post_item .= '<p class="cff-desc" ';

                                //Set the char limit on the element
                                if (!empty($body_limit)) {
                                    if (strlen($description) > $body_limit) $cff_post_item .= 'data-char="'. $body_limit .'" ';
                                }

                                //Replace line breaks in text (needed for IE8 and to prevent lost line breaks in HTML minification)
                                $description = preg_replace("/\r\n|\r|\n/",'<br/>', $description);

                                $cff_post_item .= $cff_event_details_styles . '><span class="cff-desc-text">' . cff_autolink($description, $link_color=$cff_event_link_color) . '</span>';

                                //Add the See More and See Less links if needed
                                if (!empty($body_limit)) {
                                    if (strlen($description) > $body_limit) $cff_post_item .= '<span class="cff-expand">... <a href="#" style="color: #'.$cff_posttext_link_color.'"><span class="cff-more">' . $cff_see_more_text . '</span><span class="cff-less">' . $cff_see_less_text . '</span></a></span>';
                                }

                                $cff_post_item .= '</p>';

                            }
                        }
                    //End details
                    if ( ($cff_thumb_layout || $cff_half_layout) ) $cff_post_item .= '</div>';
                    $cff_post_item .= '<div class="cff-meta-wrap">';



                    $cff_post_item .= '<div class="cff-post-links">';


                    //Social media sharing URLs
                    $cff_share_facebook = 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($event_link);
                    $cff_share_twitter = 'https://twitter.com/intent/tweet?text=' . urlencode($event_link);
                    $cff_share_google = 'https://plus.google.com/share?url=' . urlencode($event_link);
                    $cff_share_linkedin = 'https://www.linkedin.com/shareArticle?mini=true&amp;url=' . urlencode($event_link) . '&amp;title=' . rawurlencode( strip_tags($cff_event_title) . ' - ' . strip_tags($cff_event_date) );
                    $cff_share_email = 'mailto:?subject=Facebook&amp;body=' . urlencode($event_link) . '%20-%20' . rawurlencode( strip_tags($cff_event_title) . ' - ' . strip_tags($cff_event_date) );

                        //Buy tickets link
                        if($ticket_uri !== '') $cff_post_item .= '<a href="' . $ticket_uri . '" target="_blank">'.$cff_buy_tickets_text.'</a><span class="cff-dot" ' . $cff_link_styles . '>&middot;</span>';

                        //View on Facebook link
                        if($cff_show_facebook_link) $cff_post_item .= '<a class="cff-viewpost" href="' . $event_link . '" ' . $target . $cff_nofollow.' ' . $cff_link_styles . '>'.$cff_facebook_link_text.'</a>';

                        //Share link
                        if($cff_show_facebook_share){
                            $cff_post_item .= '<div class="cff-share-container">';
                            
                            if($cff_show_facebook_link) $cff_post_item .= '<span class="cff-dot" ' . $cff_link_styles . '>&middot;</span>';

                            $cff_post_item .= '<a class="cff-share-link" href="javascript:void(0);" title="' . $cff_facebook_share_text . '" ' . $cff_link_styles . '>' . $cff_facebook_share_text . '</a>';
                            $cff_post_item .= "<p class='cff-share-tooltip'><a href='".$cff_share_facebook."' target='_blank' class='cff-facebook-icon'><i class='fa fa-facebook-square'></i></a><a href='".$cff_share_twitter."' target='_blank' class='cff-twitter-icon'><i class='fa fa-twitter'></i></a><a href='".$cff_share_google."' target='_blank' class='cff-google-icon'><i class='fa fa-google-plus'></i></a><a href='".$cff_share_linkedin."' target='_blank' class='cff-linkedin-icon'><i class='fa fa-linkedin'></i></a><a href='".$cff_share_email."' target='_blank' class='cff-email-icon'><i class='fa fa-envelope'></i></a><i class='fa fa-play fa-rotate-90'></i></p></div>";
                        }
                        
                        $cff_post_item .= '</div>'; 

                    $cff_post_item .= '</div></div>';



                    //Get the filter string
                    $cff_filter_string = $atts[ 'filter' ];
                    //Create a string from the event title, location and address to use in the filter check below
                    $cff_event_address_string = $cff_event_title . $location . $venue_street . $venue_city . $venue_state . $venue_zip;

                    $cff_show_post = true;
                    if ( $cff_filter_string != '' ){
                        //Explode it into multiples
                        $cff_filter_strings_array = explode(',', $cff_filter_string);
                        //Hide the post if both the post text and description don't contain the string
                        $string_in_address = true;
                        $string_in_desc = true;
                        if ( cff_stripos_arr($cff_event_address_string, $cff_filter_strings_array) === false ) $string_in_address = false;
                        if ( cff_stripos_arr($description, $cff_filter_strings_array) === false ) $string_in_desc = false;

                        if( $string_in_address == false && $string_in_desc == false ) $cff_show_post = false;
                    }

                    $cff_exclude_string = $atts[ 'exfilter' ];
                    if ( $cff_exclude_string != '' ){
                        //Explode it into multiples
                        $cff_exclude_strings_array = explode(',', $cff_exclude_string);
                        //Hide the post if both the post text and description don't contain the string
                        $string_in_address = false;
                        $string_in_desc = false;

                        if ( cff_stripos_arr($cff_event_address_string, $cff_exclude_strings_array) !== false ) $string_in_address = true;
                        if ( cff_stripos_arr($description, $cff_exclude_strings_array) !== false ) $string_in_desc = true;

                        if( $string_in_address == true || $string_in_desc == true ) $cff_show_post = false;
                    }

                    //Change the seconds value of the event_time unix value so that if more than 1 event has the same start time then it doesn't get replaced in the posts array
                    $event_time_unix = strtotime($event_time);
                    $event_time = substr( $event_time_unix , 0, -1) . rand(1, 9);

                    //PUSH TO ARRAY if the post should be shown
                    if( $cff_show_post !== false ) $cff_posts_array = cff_array_push_assoc($cff_posts_array, $event_time, $cff_post_item);

                } // End the loop

            } // End empty() check

            //Sort all of the events by all page IDs to show the most recent upcoming events first
            if(!$cff_past_events) ksort($cff_posts_array);

            // if($cff_past_events) usort($cff_posts_array, 'cffSortByOrder');
            if($cff_past_events) krsort($cff_posts_array);

        } //End EVENTS ONLY
        
        //ALL POSTS
        if (!$cff_events_only || ($cff_events_only && $cff_events_source == 'timeline') ){

            //Create date range using the Date Range extension
            ( $cff_ext_date_active ) ? $cff_date_range = cff_ext_date(strtotime( $atts['from'] ), strtotime( $atts['until'] )) : $cff_date_range = '';

            $cff_posts_json_url = 'https://graph.facebook.com/v2.6/' . $page_id . '/' . $graph_query . '?fields=id,from{name,id},message,message_tags,story,story_tags,picture,full_picture,link,source,name,caption,description,type,status_type,object_id,created_time,attachments{subattachments},shares,likes{id,name},comments{id,from,message,message_tags,created_time,like_count,comment_count,attachment}&access_token=' . $access_token . '&limit=' . $cff_post_limit . '&locale=' . $cff_locale . $cff_ssl . $cff_date_range;

            //REVIEWS
            if ( $cff_reviews ) $cff_posts_json_url = cff_reviews_url( $page_id, $page_access_token, $cff_post_limit, $cff_locale, $cff_date_range );

            //VIDEOS ONLY
            if($cff_videos_only){
                $cff_posts_json_url = 'https://graph.facebook.com/v2.6/'.$page_id.'/videos/?access_token='.$access_token.'&fields=source,title,description,embed_html,format{picture}&locale='.$cff_locale . $cff_date_range . '&limit=' . $cff_post_limit;
            }


            //PHOTOS ONLY
            if($cff_photos_only){
                //Get the user's ID
                $get_page_info = cff_fetchUrl('https://graph.facebook.com/' . $page_id . '?fields=name,id&access_token=' . $access_token);
                $page_info = json_decode($get_page_info);
                //Get user ID
                $u_id = $page_info->id;

                //PHOTOS ONLY
                if($cff_is_group){
                    //For groups
                    $cff_posts_json_url = "https://graph.facebook.com/fql?q=SELECT%20pid,created,src_big,link,caption%20FROM%20photo%20WHERE%20pid%20IN%20(SELECT%20pid%20FROM%20photo_tag%20WHERE%20subject='".$u_id."')%20OR%20pid%20IN%20(SELECT%20pid%20FROM%20photo%20WHERE%20aid%20IN%20(SELECT%20aid%20FROM%20album%20WHERE%20owner='".$u_id."'%20AND%20type!='profile'))%20LIMIT%20".$cff_post_limit."%20&access_token=".$access_token;
                } else {
                    //For pages
                    $cff_posts_json_url = 'https://graph.facebook.com/'.$page_id.'/photos?type=uploaded&fields=id,created_time,link,picture,name&limit='.$cff_post_limit.'&access_token='.$access_token;
                }
            }

            //ALBUMS ONLY
            if($cff_albums_only && $cff_albums_source == 'photospage'){
                $cff_posts_json_url = 'https://graph.facebook.com/' . $page_id . '/albums?fields=id,name,description,link,cover_photo,count,created_time&access_token=' . $access_token . '&limit=' . $cff_post_limit. '&locale=' . $cff_locale . $cff_date_range;

                if($cff_is_group) $cff_posts_json_url = 'https://graph.facebook.com/' . $page_id . '/albums?fields=created_time,name,count,cover_photo,link,modified,id&access_token=' . $access_token . '&limit=' . $cff_post_limit. '&locale=' . $cff_locale . $cff_date_range;
            }
 

            //Featured Post extension
            if ( $cff_featured_post_active && !empty($atts['featuredpost']) ) $cff_posts_json_url = cff_featured_post_id( trim( $atts['featuredpost'] ), $access_token );

            //ALBUM EMBED
            $cff_album_id = $atts['album'];
            if( $cff_album_active && !empty($cff_album_id) ) {

                //Get the JSON back from the Album extension
                $cff_album_json_url = cff_album_id( trim( $cff_album_id ), $access_token, $cff_post_limit );

                //Don't use caching if the cache time is set to zero
                if ($cff_cache_time != 0){
                    // Get any existing copy of our transient data
                    $transient_name = 'cff_album_' . $cff_album_id . '_' . $cff_post_limit;
                    $transient_name = substr($transient_name, 0, 45);

                    if ( false === ( $album_json = get_transient( $transient_name ) ) || $album_json === null ) {
                        //Get the contents of the Facebook page
                        $album_json = cff_fetchUrl($cff_album_json_url);
                        //Cache the JSON
                        set_transient( $transient_name, $album_json, $cache_seconds );
                    } else {
                        $album_json = get_transient( $transient_name );
                        //If we can't find the transient then fall back to just getting the json from the api
                        if ($album_json == false) $album_json = cff_fetchUrl($cff_album_json_url);
                    }
                } else {
                    $album_json = cff_fetchUrl($cff_album_json_url);
                }
            }

            //Don't use caching if the cache time is set to zero
            if ($cff_cache_time != 0){

                $trans_items_arr = array(
                    'page_id' => $page_id,
                    'post_limit' => substr($cff_post_limit, 0, 3),
                    'show_posts_by' => substr($show_posts_by, 0, 2)
                );

                $trans_arr_item_count = 1;
                if($cff_ext_date_active){
                    $trans_items_arr['from'] = $atts['from'];
                    $trans_items_arr['until'] = $atts['until'];
                    // $trans_items_arr['from'] = '12342316172134';
                    // $trans_items_arr['until'] = '3434565463663456';
                    $trans_arr_item_count = $trans_arr_item_count+2;
                }
                if( $cff_featured_post_active && !empty($atts['featuredpost']) ){
                    $trans_items_arr['featured_post'] = $atts['featuredpost'];
                    $trans_arr_item_count++;
                }
                if($cff_albums_only) $trans_items_arr['albums_source'] = $cff_albums_source;
                $trans_items_arr['albums_only'] = intval($cff_albums_only);
                $trans_items_arr['photos_only'] = intval($cff_photos_only);
                $trans_items_arr['videos_only'] = intval($cff_videos_only);
                $trans_items_arr['reviews'] = intval($cff_reviews);

                $arr_item_max_length = floor( 30/$trans_arr_item_count ); //40 minus the 10 needed for the other 6 values shown below equals 30
                $arr_item_max_length_half = floor($arr_item_max_length/2);

                $transient_name = 'cff_';
                foreach ($trans_items_arr as $key => $value) {
                    if($value !== false){
                        if( $key == 'page_id' || $key == 'featured_post' || $key == 'from' || $key == 'until' ) $transient_name .= substr($value, 0, $arr_item_max_length_half) . substr($value, $arr_item_max_length_half*-1);  //-10
                        if( $key == 'post_limit' || $key == 'show_posts_by' ) $transient_name .= substr($value, 0, 3);
                        if( $key == 'albums_only' || $key == 'photos_only' || $key == 'videos_only' || $key == 'albums_source' || $key == 'reviews' ) $transient_name .= substr($value, 0, 1);
                    }
                }
                //Make sure it's not more than 45 chars
                $transient_name = substr($transient_name, 0, 45);

                // Get any existing copy of our transient data
                if ( false === ( $posts_json = get_transient( $transient_name ) ) || $posts_json === null ) {
                    //Get the contents of the Facebook page
                    $posts_json = cff_fetchUrl($cff_posts_json_url);
                    
                    //Check whether any data is returned from the API. If it isn't then don't cache the error response and instead keep checking the API on every page load until data is returned.
                    $FBdata = json_decode($posts_json);
                    if( !empty($FBdata->data) ) {
                        //Cache the JSON
                        set_transient( $transient_name, $posts_json, $cache_seconds );
                    }
                } else {
                    $posts_json = get_transient( $transient_name );
                    //If we can't find the transient then fall back to just getting the json from the api
                    if ($posts_json == false) $posts_json = cff_fetchUrl($cff_posts_json_url);
                }
            } else {
                $posts_json = cff_fetchUrl($cff_posts_json_url);
            }


            if ( $cff_show_only_others ) {
                //Get the numeric ID of the page so can compare it to the author of each post
                $page_object = cff_fetchUrl('https://graph.facebook.com/' . $page_id . '?fields=name,id&access_token=' . $access_token);
                $page_object = json_decode($page_object);
                $numeric_page_id = $page_object->id;
            }
            

            //Interpret data with JSON
            $FBdata = json_decode($posts_json);


            //Show notice that
            if( ($cff_photos_only && empty($cff_album_id)) && $cff_is_group && current_user_can( 'manage_options' ) ){

                    global $current_user;
                        $user_id = $current_user->ID;

                    // Use this to show notice again
                    // delete_user_meta($user_id, 'cff_group_photos_notice_dismiss');

                    /* Check that the user hasn't already clicked to ignore the message */
                    if ( ! get_user_meta($user_id, 'cff_group_photos_notice_dismiss') ) {

                        $cff_content .= "<section class='cff-error-msg'>";
                        $cff_content .= "<p><b>This message is only visible to admins:</b><br />Facebook is deprecating version 2.0 of their API on Augst 8th, 2016, which unfortunately means that Facebook no longer supports displaying photo grid feeds from Facebook Groups. Please see <a href='https://smashballoon.com/can-i-display-photos-from-a-facebook-group/' target='_blank'>here</a> for more information. We apologize for any inconvenience.</p>";
                        $cff_content .= "<a class='cff_notice_dismiss' href='" .esc_url( add_query_arg( 'cff_group_photos_notice_dismiss', '0' ) ). "'><i class='fa fa-times-circle' aria-hidden='true'></i></a></section>";

                    }
                
            }

            //If there's no data then show a pretty error message
            if( ( empty($FBdata->data) && empty($FBdata->videos) ) && (!$cff_featured_post_active || empty($atts['featuredpost'])) && !$cff_ext_multifeed_active ) {


                    //Group photos deprecated with API v2.0
                    if( ($cff_photos_only && empty($cff_album_id)) && $cff_is_group ){

                        $cff_content .= "<p><i class='fa fa-facebook-square' style='color: #3b5998; padding-right: 5px;'></i><a href='https://www.facebook.com/groups/".$page_id."/photos' target='_blank'>View photos on Facebook</a>";

                    } else {

                        if( current_user_can( 'manage_options' ) ){
                            $cff_content .= "<div class='cff-error-msg'>";
                            $cff_content .= '<p><b>This message is only visible to admins:</b><br />Unable to display Facebook posts <br /><a href="javascript:void(0);" id="cff-show-error" onclick="cffShowError()">Show Error Message</a></p>';
                            $cff_content .= '<script type="text/javascript">function cffShowError() { document.getElementById("cff-error-reason").style.display = "block"; document.getElementById("cff-show-error").style.display = "none"; }</script>';
                            $cff_content .= '</p><div id="cff-error-reason">';
                            
                            if( isset($FBdata->error->message) ) $cff_content .= 'Error: ' . $FBdata->error->message;
                            if( isset($FBdata->error->type) ) $cff_content .= '<br />Type: ' . $FBdata->error->type;
                            if( isset($FBdata->error->code) ) $cff_content .= '<br />Code: ' . $FBdata->error->code;
                            if( isset($FBdata->error->error_subcode) ) $cff_content .= '<br />Subcode: ' . $FBdata->error->error_subcode;

                            if( isset($FBdata->error_msg) ) $cff_content .= 'Error: ' . $FBdata->error_msg;
                            if( isset($FBdata->error_code) ) $cff_content .= '<br />Code: ' . $FBdata->error_code;
                            
                            if($FBdata == null) $cff_content .= 'Error: Server configuration issue';

                            if( empty($FBdata->error) && empty($FBdata->error_msg) && $FBdata !== null ) $cff_content .= 'Error: No posts available for this Facebook ID';

                            $cff_content .= '<br />Please refer to our <a href="https://smashballoon.com/custom-facebook-feed/docs/errors/" target="_blank">Error Message Reference</a> for a solution.';
                            $cff_content .= '</div>'; //End #cff-error-reason
                            $cff_content .= "</div>"; //End .cff-error-msg
                        }

                        if($cff_is_group){
                            $cff_content .= "<p><i class='fa fa-facebook-square' style='color: #3b5998; padding-right: 5px;'></i><a href='https://www.facebook.com/groups/".$page_id."' target='_blank'>Join us on Facebook</a>";
                        } else {
                            if($cff_show_like_box) $cff_content .= $like_box;
                        }                        

                    }

                    

                    $cff_content .= '</div></div>'; //End #cff and .cff-wrapper
                    

                return $cff_content;
            }

            //ALBUM EMBED
            if( $cff_album_active && !empty($cff_album_id) ) $FBdata = json_decode($album_json);

            //***STARTS POSTS LOOP***
            $fbdata_string = '';
            //If the Featured Post extension is active then adjust the loop as there is no 'data'
            if($cff_featured_post_active && !empty($atts['featuredpost'])){
                if( isset($FBdata) && !empty($FBdata) ) $fbdata_string = $FBdata;
            } else {
                
                if( $cff_videos_only && isset($FBdata->videos) ){
                    //Videos only
                    $fbdata_string = $FBdata->videos->data;
                } else {
                    //All other posts
                    if( isset($FBdata->data) ) $fbdata_string = $FBdata->data;
                }
                
            }


            if($fbdata_string){

                foreach ($fbdata_string as $news)
                {
                    if ($cff_featured_post_active && !empty($atts['featuredpost'])) $news = $FBdata;

                    $cff_post_item = '';

                    //Explode News and Page ID's into 2 values
                    $PostID = '';
                    if( isset($news->id) ){
                        $cff_post_id = $news->id;
                        $PostID = explode("_", $cff_post_id);
                    }
                    if( isset($PostID[0]) ) $orig_post_id = $PostID[0];
                    if( isset($PostID[1]) ) $orig_post_id .= '_' . $PostID[1];

                    //Check the post type
                    isset($news->type) ? $cff_post_type = $news->type : $cff_post_type = '';
                    if ($cff_post_type == 'link') {
                        isset($news->story) ? $story = $news->story : $story = '';
                        //Check whether it's an event
                        $event_link_check = "facebook.com/events/";
                        //Make sure URL doesn't include 'permalink' as that indicates someone else sharing a post from within an event (eg: https://www.facebook.com/events/617323338414282/permalink/617324268414189/) and the event ID is then not retrieved properly from the event URL as it's formatted like so: facebook.com/events/EVENT_ID/permalink/POST_ID
                        $event_link_check = stripos($news->link, $event_link_check);
                        $event_link_check_2 = stripos($news->link, "permalink/");
                        if ( $event_link_check && !$event_link_check_2 ) $cff_post_type = 'event';
                    }

                    //Set the post link
                    isset($news->link) ? $link = htmlspecialchars($news->link) : $link = '';

                    //If there's no link provided then link to the individual post
                    if (empty($news->link)) {
                        //Link to individual post
                        if( isset($PostID[1]) ) $link = "https://www.facebook.com/" . $page_id . "/posts/" . $PostID[1];
                    }

                    //If it's an event then check whether the URL contains facebook.com
                    if(isset($news->link)){
                        if( stripos($news->link, "events/") && $cff_post_type == 'event' ){
                            //Facebook changed the event link from absolute to relative, and so if the link isn't absolute then add facebook.com to front
                            ( stripos($link, 'facebook.com') ) ? $link = $link : $link = 'https://facebook.com' . $link;
                        }
                    }

                    //Is it an album?
                    $cff_album = false;
                    $num_photos = 0;

                    //The album check has to be done this way as checking for attachments/subattachments doesn't work as the posts which have the wrong posts IDs (the album ID instead of the post ID - see Facebook bug report) don't have any post attachments in the API even though they do on Facebook.
                    if( isset($news->status_type) ){
                        if( $news->status_type == 'added_photos' ){
                            //Check 'story' to see whether it contains a number
                            (isset($news->story)) ? $str = $news->story : $str = '';
                            
                            //Only matches number with a space after them
                            preg_match('!\d+ !', $str, $matches);


                            (isset($matches[0])) ? $num_photos = $matches[0] : $num_photos = 0;

                            //If the story contains a number...
                            if ( $num_photos > 1 ) {

                                //... and the link is to an album then it most likely has photo attachments
                                if (strpos($link,'photos/a.') !== false){
                                    $albumLinkArr1 = explode('photos/a.', $link);
                                    if( isset($albumLinkArr1[1]) ) $albumLinkArr2 = explode('.', $albumLinkArr1[1]);

                                    //If it has an album link then set the post type to be album
                                    if( isset($albumLinkArr1[1]) ){

                                        $cff_album = true;

                                        //If the post has subattachments then don't change the post ID to the album ID. If it doesn't then change it to the album ID so that we can at least show the photos from the album
                                        if( !isset($news->attachments) ){
                                            //Change the Post ID to be to the post about adding photos to the album
                                            $cff_post_id = $PostID[0] . '_' . $albumLinkArr2[0];
                                        }

                                        //Link to the album instead of the photo
                                        $album_link = str_replace('photo.php?','media/set/?',$link);
                                        $link = "https://www.facebook.com/" . $page_id . "/posts/" . $PostID[1];

                                        //If the album link is a new format then link it to the post
                                        $album_link_check = 'media/set/?';
                                        if( stripos($album_link, $album_link_check) !== true ) $album_link = $link;

                                    }
                                }
                                
                            }
                        }
                    }


                    //Should we show this post or not?
                    $cff_show_post = false;
                    switch ($cff_post_type) {
                        case 'link':
                            if ( $cff_show_links_type ) $cff_show_post = true;
                            break;
                        case 'event':
                            if ( $cff_show_event_type ) $cff_show_post = true;
                            break;
                        case 'video':
                             if ( $cff_show_video_type ) $cff_show_post = true;
                            break;
                        case 'swf':
                             if ( $cff_show_video_type ) $cff_show_post = true;
                            break;
                        case 'photo':
                             if ( $cff_show_photos_type && !$cff_album ) $cff_show_post = true;
                             if ( $cff_show_albums_type && $cff_album ) $cff_show_post = true;
                            break;
                        case 'offer':
                            //Show offer posts if links are shown
                             if ( $cff_show_links_type ) $cff_show_post = true;
                            break;
                        case 'music':
                            //Show music posts if statuses are shown
                             if ( $cff_show_status_type ) $cff_show_post = true;
                            break;
                        case 'status':
                            //Check whether it's a status (author comment or like)
                            if ( $cff_show_status_type && !empty($news->message) ) $cff_show_post = true;
                            break;
                    }
                    //Featured Post extension
                    if( $cff_featured_post_active && !empty($atts['featuredpost']) ) {
                        //Always show the post if using the Featured Post extension
                        $cff_show_post = true;

                        if( $cff_show_links_type ) $cff_post_type = 'link';
                        if( $cff_show_event_type ) $cff_post_type = 'event';
                        if( $cff_show_video_type ) $cff_post_type = 'video';
                        if( $cff_show_photos_type ) $cff_post_type = 'photo';
                        if( $cff_show_albums_type ) $cff_post_type = 'album';
                        if( $cff_show_status_type ) $cff_post_type = 'status';

                        //If it's a status then use full-width layout by default
                        if($cff_post_type == 'status') {
                            $cff_thumb_layout = false;
                            $cff_half_layout = false;
                        }
                    }


                    //ONLY show posts by others
                    if ( $cff_show_only_others ) {
                        //If the post author's ID is the same as the page ID then don't show the post
                        if ( $numeric_page_id == $news->from->id ) $cff_show_post = false;
                    }

                    //Only show posts containing specified string
                    //Get post text
                    $post_text = '';
                    if (!empty($news->story)) $post_text = $news->story;
                    if (!empty($news->message)) $post_text = $news->message;
                    if (!empty($news->name) && empty($news->story) && empty($news->message)) $post_text = $news->name;

                    //Get description text
                    if( isset($news->description) ){
                        $description_text = $news->description;
                    } else {
                        isset( $news->caption ) ? $description_text = $news->caption : $description_text = '';
                    }

                    //Get the filter string
                    $cff_filter_string = $atts[ 'filter' ];

                    if ( $cff_filter_string != '' ){
                        //Explode it into multiples
                        $cff_filter_strings_array = explode(',', $cff_filter_string);
                        //Hide the post if both the post text and description don't contain the string
                        $string_in_post_text = true;
                        $string_in_desc = true;
                        if ( cff_stripos_arr($post_text, $cff_filter_strings_array) === false ) $string_in_post_text = false;
                        if ( cff_stripos_arr($description_text, $cff_filter_strings_array) === false ) $string_in_desc = false;

                        if( $string_in_post_text == false && $string_in_desc == false ) $cff_show_post = false;
                    }

                    $cff_exclude_string = $atts[ 'exfilter' ];
                    if ( $cff_exclude_string != '' ){
                        //Explode it into multiples
                        $cff_exclude_strings_array = explode(',', $cff_exclude_string);
                        //Hide the post if both the post text and description don't contain the string
                        $string_in_post_text = false;
                        $string_in_desc = false;

                        if ( cff_stripos_arr($post_text, $cff_exclude_strings_array) !== false ) $string_in_post_text = true;
                        if ( cff_stripos_arr($description_text, $cff_exclude_strings_array) !== false ) $string_in_desc = true;

                        if( $string_in_post_text == true || $string_in_desc == true ) $cff_show_post = false;
                    }


                    //Is it a duplicate post?
                    if (!isset($prev_post_message)) $prev_post_message = '';
                    if (!isset($prev_post_link)) $prev_post_link = '';
                    if (!isset($prev_post_description)) $prev_post_description = '';
                    isset($news->message) ? $pm = $news->message : $pm = '';
                    isset($news->link) ? $pl = $news->link : $pl = '';
                    isset($news->description) ? $pd = $news->description : $pd = '';

                    if ( ($prev_post_message == $pm) && ($prev_post_link == $pl) && ($prev_post_description == $pd) ) $cff_show_post = false;

                    //ALBUMS ONLY
                    if($cff_albums_only && $cff_albums_source == 'photospage') $cff_show_post = true;

                    //ALBUM EMBED
                    if( $cff_album_active && !empty($cff_album_id) ) $cff_show_post = true;

                    //PHOTOS ONLY
                    if($cff_photos_only) $cff_show_post = true;

                    //VIDEOS ONLY
                    if($cff_videos_only) $cff_show_post = true;

                    //REVIEWS
                    if($cff_reviews) $cff_show_post = true;

                    //Check post type and display post if selected
                    if ( $cff_show_post ) {
                        //If it isn't then create the post

                        $cff_offset_show_post = true;
                        //Offset. If the post index ($i) is less than the offset then don't show the post
                        if( intval($i) < intval($atts['offset']) ){
                            $cff_offset_show_post = false;
                            $i++;
                        }

                        //If there's an offset then show the post until it's set to false above. This has been moved here so that the offset works correctly when only displaying specific post types, as previously it only worked accurately when all posts were shown
                        if($cff_offset_show_post){

                            if( !$cff_ext_multifeed_active ){
                                //Only create posts for the amount of posts specified
                                if( intval($atts['offset']) > 0 ){
                                    //If offset is being used then stop after showing the number of posts + the offset
                                    if ( $i == (intval($show_posts) + intval($atts['offset'])) ) break;
                                } else {
                                    //Else just stop after the number of posts to be displayed is reached, unless it's albums only or photos only
                                    if( ($cff_albums_only && $cff_albums_source == 'photospage') || ( $cff_photos_only && empty($cff_album_id) ) || $cff_videos_only ){
                                        //Keep going
                                    } else {
                                        if ( $i == $show_posts ) break;
                                    }
                                    
                                }
                            }
                            $i++;

                            
                            //********************************//
                            //***COMPILE SECTION VARIABLES***//
                            //********************************//
                            //Change image size based on layout
                            if (!empty($news->picture) && !empty($news->object_id)) {
                                $object_id = $news->object_id;
                                $picture = 'https://graph.facebook.com/'.$object_id.'/picture?type=normal&amp;width=9999&amp;height=9999';
                            }

                            //DATE
                            $cff_date_formatting = $atts[ 'dateformat' ];
                            $cff_date_custom = $atts[ 'datecustom' ];

                            isset($news->created_time) ? $post_time = $news->created_time : $post_time = '';
                            $cff_date = '<p class="cff-date" '.$cff_date_styles.'>'. $cff_date_before . ' ' . cff_getdate(strtotime($post_time), $cff_date_formatting, $cff_date_custom, $cff_date_translate_strings) . ' ' . $cff_date_after;
                            $cff_date .= '</p>';

                            //Only run if NOT only showing photos from the photos page, or albums, or an album embed
                            if( !$cff_photos_only && !$cff_videos_only && !($cff_albums_only && $cff_albums_source == 'photospage') && empty($cff_album_id) && !$cff_reviews ){


                                //POST AUTHOR
                                $cff_author = '<div class="cff-author">';
                                
                                //Author text
                                $cff_author .= '<a href="https://facebook.com/' . $news->from->id . '" '.$target.$cff_nofollow.' title="'.$news->from->name.' on Facebook" '.$cff_author_styles.'><div class="cff-author-text">';

                                if($cff_show_date && $cff_date_position !== 'above' && $cff_date_position !== 'below'){
                                    $cff_author .= '<p class="cff-page-name cff-author-date" '.$cff_author_styles.'>'.$news->from->name.'</p>';
                                    $cff_author .= $cff_date;
                                } else {
                                    $cff_author .= '<span class="cff-page-name">'.$news->from->name.'</span>';
                                }

                                $cff_author .= '</div>';

                                //Author image
                                //Set the author image as a variable. If it already exists then don't query the api for it again.
                                $cff_author_img_var = '$cff_author_img_' . $news->from->id;
                                if ( !isset($$cff_author_img_var) ) $$cff_author_img_var = 'https://graph.facebook.com/' . $news->from->id . '/picture?type=square';
                                $cff_author .= '<div class="cff-author-img"><img src="'.$$cff_author_img_var.'" title="'.$news->from->name.'" alt="'.$news->from->name.'" width=40 height=40></div>';

                                $cff_author .= '</a></div>'; //End .cff-author


                                //POST TEXT
                                $cff_post_text = '<' . $cff_title_format . ' class="cff-post-text" ' . $cff_title_styles . '>';
                                
                                //Get the actual post text
                                //Which content should we use?
                                $post_text = '';
                                $cff_post_text_type = '';
                                $cff_story_raw = '';
                                $cff_message_raw = '';
                                $cff_name_raw = '';
                                $text_tags = '';
                                $post_text_story = '';
                                $post_text_message = '';

                                //STORY TAGS
                                $cff_post_tags = $atts[ 'posttags' ];
                                //If the post tags option doesn't exist yet (ie. on plugin update) then set them as true
                                if ( !array_key_exists( 'cff_post_tags', $options ) ) $cff_post_tags = true;

                                //Use the story
                                if (!empty($news->story)) {
                                    $cff_story_raw = $news->story;
                                    $post_text_story .= htmlspecialchars($cff_story_raw);
                                    $cff_post_text_type = 'story';


                                    //Add message and story tags if there are any and the post text is the message or the story
                                    if( $cff_post_tags && isset($news->story_tags) && !$cff_title_link){
                                        
                                        $text_tags = $news->story_tags;

                                        //Does the Post Text contain any html tags? - the & symbol is the best indicator of this
                                        $cff_html_check_array = array('&lt;', '’', '“', '&quot;', '&amp;', '&gt;&gt;');

                                        //always use the text replace method
                                        if( cff_stripos_arr($post_text_story, $cff_html_check_array) !== false ) {
                                            //Loop through the tags
                                            foreach($text_tags as $message_tag ) {

                                                ( isset($message_tag->id) ) ? $message_tag = $message_tag : $message_tag = $message_tag[0];

                                                $tag_name = $message_tag->name;
                                                $tag_link = '<a href="http://facebook.com/' . $message_tag->id . '">' . $message_tag->name . '</a>';

                                                $post_text_story = str_replace($tag_name, $tag_link, $post_text_story);
                                            }

                                        } else {
                                        //If it doesn't contain HTMl tags then use the offset to replace message tags
                                            $message_tags_arr = array();

                                            $tag = 0;
                                            foreach($text_tags as $message_tag ) {
                                                $tag++;
                                                ( isset($message_tag->id) ) ? $message_tag = $message_tag : $message_tag = $message_tag[0];

                                                isset($message_tag->type) ? $tag_type = $message_tag->type : $tag_type = '';

                                                $message_tags_arr = cff_array_push_assoc(
                                                    $message_tags_arr,
                                                    $tag,
                                                    array(
                                                        'id' => $message_tag->id,
                                                        'name' => $message_tag->name,
                                                        'type' => isset($message_tag->type) ? $message_tag->type : '',
                                                        'offset' => $message_tag->offset,
                                                        'length' => $message_tag->length
                                                    )
                                                );
                                                
                                            }

                                            //Keep track of the offsets so that if two tags have the same offset then only one is used. Need this as API 2.5 update changed the story_tag JSON format. A duplicate offset usually means '__ was with __ and 3 others'. We don't want to link the '3 others' part.
                                            $cff_story_tag_offsets = '';
                                            $cff_story_duplicate_offset = '';

                                            //Check if there are any duplicate offsets. If so, assign to the cff_story_duplicate_offset var.
                                            for($tag = count($message_tags_arr); $tag >= 1; $tag--) {
                                                $c = (string)$message_tags_arr[$tag]['offset'];
                                                if( strpos( $cff_story_tag_offsets, $c ) !== false && $c !== '0' ){
                                                    $cff_story_duplicate_offset = $c;
                                                } else {
                                                    $cff_story_tag_offsets .= $c . ',';  
                                                }
                                                                                          
                                            }

                                            for($tag = count($message_tags_arr); $tag >= 1; $tag--) {

                                                //If the name is blank (aka the story tag doesn't work properly) then don't use it
                                                if( $message_tags_arr[$tag]['name'] !== '' ) {

                                                    //If it's an event tag or it has the same offset as another tag then don't display it
                                                    if( $message_tags_arr[$tag]['type'] == 'event' || $message_tags_arr[$tag]['offset'] == $cff_story_duplicate_offset ){
                                                        //Don't use the story tag in this case otherwise it changes '__ created an event' to '__ created an Name Of Event'
                                                    } else {
                                                        $b = '<a href="http://facebook.com/' . $message_tags_arr[$tag]['id'] . '">' . $message_tags_arr[$tag]['name'] . '</a>';
                                                        $c = $message_tags_arr[$tag]['offset'];
                                                        $d = $message_tags_arr[$tag]['length'];
                                                        $post_text_story = cff_mb_substr_replace( $post_text_story, $b, $c, $d);
                                                    }

                                                }

                                            }
                                            

                                        } // end if/else


                                    } //END STORY TAGS


                                }
                                //Use the message
                                if (!empty($news->message)) {
                                    $cff_message_raw = $news->message;
                                    
                                    $post_text_message = htmlspecialchars($cff_message_raw);
                                    $cff_post_text_type = 'message';

                                    //MESSAGE TAGS
                                    //Add message and story tags if there are any and the post text is the message or the story
                                    if( $cff_post_tags && isset($news->message_tags) && !$cff_title_link){
                                        
                                        $text_tags = $news->message_tags;

                                        //Does the Post Text contain any html tags? - the & symbol is the best indicator of this
                                        $cff_html_check_array = array('&lt;', '’', '“', '&quot;', '&amp;', '&gt;&gt;', '&gt;');

                                        //always use the text replace method
                                        if( cff_stripos_arr($post_text_message, $cff_html_check_array) !== false ) {
                                            //Loop through the tags
                                            foreach($text_tags as $message_tag ) {

                                                ( isset($message_tag->id) ) ? $message_tag = $message_tag : $message_tag = $message_tag[0];

                                                $tag_name = $message_tag->name;
                                                $tag_link = '<a href="http://facebook.com/' . $message_tag->id . '">' . $message_tag->name . '</a>';

                                                $post_text_message = str_replace($tag_name, $tag_link, $post_text_message);
                                            }

                                        } else {
                                        //If it doesn't contain HTMl tags then use the offset to replace message tags
                                            $message_tags_arr = array();

                                            $tag = 0;
                                            foreach($text_tags as $message_tag ) {
                                                $tag++;

                                                ( isset($message_tag->id) ) ? $message_tag = $message_tag : $message_tag = $message_tag[0];

                                                $message_tags_arr = cff_array_push_assoc(
                                                    $message_tags_arr,
                                                    $tag,
                                                    array(
                                                        'id' => $message_tag->id,
                                                        'name' => $message_tag->name,
                                                        'type' => isset($message_tag->type) ? $message_tag->type : '',
                                                        'offset' => $message_tag->offset,
                                                        'length' => $message_tag->length
                                                    )
                                                );
                                            }

                                            //Keep track of the offsets so that if two tags have the same offset then only one is used. Need this as API 2.5 update changed the story_tag JSON format.
                                            $cff_msg_tag_offsets = '';
                                            $cff_msg_duplicate_offset = '';

                                            //Check if there are any duplicate offsets. If so, assign to the cff_duplicate_offset var.
                                            for($tag = count($message_tags_arr); $tag >= 1; $tag--) {
                                                $c = (string)$message_tags_arr[$tag]['offset'];
                                                if( strpos( $cff_msg_tag_offsets, $c ) !== false && $c !== '0' ){
                                                    $cff_msg_duplicate_offset = $c;
                                                } else {
                                                    $cff_msg_tag_offsets .= $c . ',';  
                                                }
                                            }

                                            for($tag = count($message_tags_arr); $tag >= 1; $tag--) {

                                                //If the name is blank (aka the story tag doesn't work properly) then don't use it
                                                if( $message_tags_arr[$tag]['name'] !== '' ) {

                                                    if( $message_tags_arr[$tag]['offset'] == $cff_msg_duplicate_offset ){
                                                        //If it has the same offset as another tag then don't display it
                                                    } else {
                                                        $b = '<a href="http://facebook.com/' . $message_tags_arr[$tag]['id'] . '">' . $message_tags_arr[$tag]['name'] . '</a>';
                                                        $c = $message_tags_arr[$tag]['offset'];
                                                        $d = $message_tags_arr[$tag]['length'];
                                                        $post_text_message = cff_mb_substr_replace( $post_text_message, $b, $c, $d);
                                                    }

                                                }

                                            }   

                                        } // end if/else

                                    } //END MESSAGE TAGS

                                }


                                //Add the story and message together
                                $post_text = '';
                                if(!empty($post_text_story)) $post_text .= '<span class="cff-story">' . $post_text_story;
                                if(!empty($post_text_story) && !empty($post_text_message)) $post_text .= "<br /><br />";
                                if(!empty($post_text_story)) $post_text .= '</span>';

                                //Check to see whether it's an embedded video so that we can show the name above the post text if necessary
                                $cff_soundcloud = false;
                                $cff_is_video_embed = false;
                                if ($news->type == 'video'){
                                    $url = $news->source;
                                    //Embeddable video strings
                                    $youtube = 'youtube';
                                    $youtu = 'youtu';
                                    $vimeo = 'vimeo';
                                    $youtubeembed = 'youtube.com/embed';
                                    $soundcloud = 'player.soundcloud.com';
                                    $swf = '.swf';
                                    //Check whether it's a youtube video
                                    $youtube = stripos($url, $youtube);
                                    $youtu = stripos($url, $youtu);
                                    $youtubeembed = stripos($url, $youtubeembed);
                                    //Check whether it's a SoundCloud embed
                                    $soundcloudembed = stripos($url, $soundcloud);
                                    //Check whether it's a youtube video
                                    if($youtube || $youtu || $youtubeembed || (stripos($url, $vimeo) !== false)) {
                                        $cff_is_video_embed = true;
                                    }
                                    //If it's soundcloud then add it into the shared link box at the bottom of the post
                                    if( $soundcloudembed ) $cff_soundcloud = true;

                                    //If the name exists and it's a non-embedded video then show the name at the top of the post text
                                    if( isset($news->name) && !$cff_is_video_embed ){

                                        if( empty($post_text_message) ) $post_text .= "<br /><br />";

                                        if (!$cff_title_link) $post_text .= '<a href="'.$link.'" '.$target.$cff_nofollow.' style="color: #'.$cff_posttext_link_color.'">';
                                        $post_text .= htmlspecialchars($news->name);
                                        if (!$cff_title_link) $post_text .= '</a>';
                                        $post_text .= '<br />';
                                    }
                                }

                                //Add the message
                                $post_text .= $post_text_message;


                                //Use the name
                                if (!empty($news->name) && empty($news->story) && empty($news->message)) {
                                    $cff_name_raw = $news->name;
                                    $post_text = htmlspecialchars($cff_name_raw);
                                    $cff_post_text_type = 'name';
                                }
                                // if ($cff_album) {
                                //     if (!empty($news->name)) {
                                //         $post_text .= htmlspecialchars($news->name);
                                //         $cff_post_text_type = 'name';
                                //     }
                                //     // if (!empty($news->message) && empty($news->name)) {
                                //     if (!empty($news->message)) {
                                //         $post_text .= htmlspecialchars($news->message);
                                //         $cff_post_text_type = 'message';
                                //     }
                                //     // if ($num_photos > 1)  $post_text .= ' (' . trim($num_photos) . ' '.$cff_translate_photos_text.')';
                                // }


                                //OFFER TEXT
                                if ($cff_post_type == 'offer'){
                                    isset($news->story) ? $post_text = htmlspecialchars($news->story) . '<br /><br />' : $post_text = '';
                                    $post_text .= htmlspecialchars($news->name);
                                    $cff_post_text_type = 'story';
                                }

                                //Start HTML for post text
                                $cff_post_text .= '<span class="cff-text" data-color="'.$cff_posttext_link_color.'">';
                                if ($cff_title_link){
                                    //Link to the Facebook post if it's a link or a video;
                                    ($cff_post_type == 'link' || $cff_post_type == 'video') ? $text_link = "https://www.facebook.com/" . $page_id . "/posts/" . $PostID[1] : $text_link = $link;

                                    $cff_post_text .= '<a class="cff-post-text-link" '.$cff_title_styles.' href="'.$text_link.'" '.$target.$cff_nofollow.'>';
                                }
                                

                                //Replace line breaks in text (needed for IE8)
                                $post_text = preg_replace("/\r\n|\r|\n/",'<br/>', $post_text);

                                //If the text is wrapped in a link then don't hyperlink any text within
                                if ($cff_title_link) {
                                    //Wrap links in a span so we can break the text if it's too long
                                    $cff_post_text .= cff_wrap_span( $post_text ) . ' ';
                                } else {
                                    //Don't use htmlspecialchars for post_text as it's added above so that it doesn't mess up the message_tag offsets
                                    $cff_post_text .= cff_autolink( $post_text ) . ' ';
                                }
                                
                                if ($cff_title_link) $cff_post_text .= '</a>';
                                $cff_post_text .= '</span>';
                                //'See More' link
                                $cff_post_text .= '<span class="cff-expand">... <a href="#" style="color: #'.$cff_posttext_link_color.'"><span class="cff-more">' . $cff_see_more_text . '</span><span class="cff-less">' . $cff_see_less_text . '</span></a></span>';
                                $cff_post_text .= '</' . $cff_title_format . '>';
                                // $cff_post_text .= '</div>';

                                //DESCRIPTION
                                $cff_description = '';
                                if ( !empty($news->description) || !empty($news->caption) ) {
                                    $description_text = '';
                                    if ( !empty($news->description) ) {
                                        $description_text = $news->description;
                                    } else {
                                        $description_text = $news->caption;
                                    }

                                    //If the description is the same as the post text then don't show it
                                    if( $description_text ==  $cff_story_raw || $description_text ==  $cff_message_raw || $description_text ==  $cff_name_raw ){
                                        $cff_description = '';
                                    } else {
                                        //Truncate desc
                                        if (!empty($body_limit)) {
                                            if (strlen($description_text) > $body_limit) $description_text = substr($description_text, 0, $body_limit) . '...';
                                        }
                                        //Add links and create HTML
                                        $cff_description .= '<p class="cff-post-desc" '.$cff_body_styles.'><span>' . cff_autolink( htmlspecialchars($description_text), $link_color=$cff_posttext_link_color )  . ' </span></p>';
                                    }
                                    
                                    if( $cff_post_type == 'event' ) $cff_description = '';
                                }

                                //LINK
                                $cff_shared_link = '';
                                //Display shared link
                                if ($cff_post_type == 'link' || $cff_soundcloud) {

                                    if( $cff_soundcloud ){
                                        //Put this here so that is also hidden when hiding shared links in the Post Layout settings
                                        if($cff_soundcloud) $cff_shared_link .= '<iframe class="cff-soundcloud" width="100%" height="100" scrolling="no" frameborder="no" src="https://w.soundcloud.com/player/?url=' . $news->link . '&amp;auto_play=false&amp;hide_related=true&amp;show_comments=false&amp;show_user=true&amp;show_reposts=false&amp;visual=false"></iframe>';
                                    } else {

                                        $cff_shared_link .= '<div class="cff-shared-link';
                                        if($cff_disable_link_box) $cff_shared_link .= ' cff-no-styles';

                                        if($cff_full_link_images) $cff_shared_link .= ' cff-full-size';

                                        $cff_shared_link .= '" ';

                                        if(!$cff_disable_link_box) $cff_shared_link .= $cff_link_box_styles;
                                        $cff_shared_link .= '>';
                                        $cff_link_image = '';

                                        if ( isset($news->picture) ){

                                            if (!empty($news->picture)) {
                                                $picture = $news->picture;

                                                /*If the image doesn't have a _b version then the URL looks like this:
                                                http://photos-h.ak.fbcdn.net/hphotos-ak-prn1/v/1600273_348160658659104_383135394_s.jpg?oh=23124db338cd899962fa7fb2d7285306&oe=52D5F9BE&__gda__=1389770591_64da0df3e725ca2d1fd026b0e922c58b
                                                So check for this kind of string below and don't replace _s. with _b.
                                                */
                                                $bigjpg = '_s.jpg?';
                                                $bigpng = '_s.png?';
                                                $biggif = '_s.gif?';
                                                $bigbmp = '_s.bmp?';
                                                $bigtjpg = '_t.jpg?';
                                                $bigtpng = '_t.png?';
                                                $bigtgif = '_t.gif?';
                                                $bigtbmp = '_t.bmp?';
                                                $imagecheck1 = stripos($picture, $bigjpg);
                                                $imagecheck2 = stripos($picture, $bigpng);
                                                $imagecheck3 = stripos($picture, $biggif);
                                                $imagecheck4 = stripos($picture, $bigbmp);
                                                $imagecheck5 = stripos($picture, $bigtjpg);
                                                $imagecheck6 = stripos($picture, $bigtpng);
                                                $imagecheck7 = stripos($picture, $bigtgif);
                                                $imagecheck8 = stripos($picture, $bigtbmp);

                                                if ( !($imagecheck1 || $imagecheck2 || $imagecheck3 || $imagecheck4 || $imagecheck5 || $imagecheck6 || $imagecheck7 || $imagecheck8) ) {
                                                    //Show larger image
                                                    $picture = str_replace('_s.','_b.',$picture);
                                                    $picture = str_replace('_q.','_b.',$picture);
                                                    $picture = str_replace('_t.','_b.',$picture);
                                                }

                                                if ( isset($news->picture) && !empty($news->picture) ) $picture = $news->picture;
                                                ( isset($news->full_picture) && !empty($news->full_picture) ) ? $full_picture = $news->full_picture : $full_picture = $picture;

                                                //Set the link image to be the full-size image
                                                if($cff_full_link_images) $picture = $news->full_picture;
                                            }

                                            //Check whether the image is a 1x1 placeholder
                                            $cff_link_image = true;
                                            $cff_one_x_one = '1x1.';
                                            if( stripos($news->picture, $cff_one_x_one) == true || empty($news->picture) ) $cff_link_image = false;

                                            //If there's a picture accompanying the link then display it
                                            if ($cff_link_image && $cff_show_media) {
                                                $cff_shared_link .= '<a class="cff-link" href="'.$link.'" '.$target.$cff_nofollow.' data-full="'.$full_picture.'">';
                                                $cff_shared_link .= '<img src="'. $picture .'" />';
                                                $cff_shared_link .= '</a>';
                                            }
                                        }

                                        //Display link name and description
                                        // if (!empty($news->description)) {
                                        $cff_shared_link .= '<div class="cff-text-link ';
                                        if (!$cff_link_image) $cff_shared_link .= 'cff-no-image';
                                        //The link title:
                                        if( isset($news->name) ) $cff_shared_link .= '"><'.$cff_link_title_format.' class="cff-link-title" '.$cff_link_title_styles.'><a href="'.$link.'" '.$target.$cff_nofollow.' style="color:#' . $cff_link_title_color . ';">'. $news->name . '</a></'.$cff_link_title_format.'>';
                                        //The link source:
                                        (!empty($news->caption)) ? $cff_link_caption = $news->caption : $cff_link_caption = '';
                                        if(!empty($cff_link_caption)) $cff_shared_link .= '<p class="cff-link-caption" style="color:#' . str_replace('#', '', $cff_link_url_color) . ';">'.$cff_link_caption.'</p>';
                                        if ($cff_show_desc) {
                                            if( $description_text != $cff_link_caption ) $cff_shared_link .= $cff_description;
                                        }

                                        $cff_shared_link .= '</div>';
                                        // }

                                        $cff_shared_link .= '</div>';

                                    } //End soundcloud check

                                }

                                //EVENT
                                $cff_event_has_cover_photo = false;
                                $cff_event = '';
                                if ($cff_show_event_title || $cff_show_event_details) {
                                    //Check for media
                                    if ($cff_post_type == 'event') {

                                        //Get the event id from the event URL. eg: http://www.facebook.com/events/123451234512345/
                                        $event_url = parse_url($link);
                                        $url_parts = explode('/', $event_url['path']);
                                        //Get the id from the parts
                                        $eventID = $url_parts[count($url_parts)-2];
                                        
                                        //Facebook changed the event link from absolute to relative, and so if the link isn't absolute then add facebook.com to front
                                        ( stripos($link, 'facebook.com') ) ? $link = $link : $link = 'https://facebook.com' . $link;

                                        //New tokens which are 2.3 and newer don't allow us to get the location or venue of timeline events so use older tokens for timeline events
                                        $access_token = $access_token_array_fql[rand(0, 4)];

                                        //Get the contents of the event
                                        // $event_json_url = 'https://graph.facebook.com/'.$eventID.'?access_token=' . $access_token . $cff_ssl;
                                        $event_json_url = 'https://graph.facebook.com/v2.3/'.$eventID.'?fields=description,cover,location,name,owner,start_time,timezone,venue,id,likes,comments&access_token=' . $access_token . $cff_ssl;
                                        
                                        //Don't use caching if the cache time is set to zero
                                        if ($cff_cache_time != 0){
                                            // Get any existing copy of our transient data
                                            $transient_name = 'cff_tle_' . $eventID;
                                            $transient_name = substr($transient_name, 0, 45);

                                            if ( false === ( $event_json = get_transient( $transient_name ) ) || $event_json === null ) {
                                                //Get the contents of the Facebook page
                                                $event_json = cff_fetchUrl($event_json_url);
                                                //Cache the JSON
                                                set_transient( $transient_name, $event_json, $cache_seconds );
                                            } else {
                                                $event_json = get_transient( $transient_name );
                                                //If we can't find the transient then fall back to just getting the json from the api
                                                if ($event_json == false) $event_json = cff_fetchUrl($event_json_url);
                                            }
                                        } else {
                                            $event_json = cff_fetchUrl($event_json_url);
                                        }

                                        //Interpret data with JSON
                                        $event_object = json_decode($event_json);
                                        //Picture
                                        if( isset($event_object->cover) ){
                                            $cff_timeline_event_image = $event_object->cover->source;
                                            $cff_event_has_cover_photo = true;
                                        } else {
                                            $cff_timeline_event_image = false;
                                        }

                                        $cff_timeline_event_photo = '';
                                        if($cff_show_media && $cff_timeline_event_image){

                                            //Fix Photon (Jetpack) issue
                                            $cff_picture_querystring = '';
                                            if (parse_url($cff_timeline_event_image, PHP_URL_QUERY)){
                                                $picture_url_parts = parse_url($cff_timeline_event_image);
                                                $cff_picture_querystring = $picture_url_parts['query'];
                                            }

                                            //Remove any quotes from event name to use in the image alt tag
                                            (!empty($event_object->name)) ? $cff_event_title = $event_object->name : $cff_event_title = '';
                                            $cff_event_title = str_replace('"', "", $cff_event_title);
                                            $cff_event_title = str_replace("'", "", $cff_event_title);

                                            //Alt text
                                            isset( $cff_event_title ) ? $cff_alt_text = strip_tags($cff_event_title) : $cff_alt_text = $cff_facebook_link_text;

                                            $cff_timeline_event_photo .= '<a title="'.$cff_facebook_link_text.'" class="cff-event-thumb';
                                            if($cff_event_has_cover_photo) $cff_timeline_event_photo .= ' cff-has-cover';
                                            $cff_timeline_event_photo .= '" href="'.$link.'" '.$target.$cff_nofollow.'><img src="'.$cff_timeline_event_image.'" alt="'.$cff_alt_text.'" data-querystring="'.$cff_picture_querystring.'" /></a>';
                                        }

                                        //Event date
                                        isset($event_object->start_time)? $event_time = $event_object->start_time : $event_time = '';
                                        isset($event_object->end_time) ? $event_end_time = ' - <span class="cff-end-date">' . cff_eventdate(strtotime($event_object->end_time), $cff_event_date_formatting, $cff_event_date_custom) . '</span>' : $event_end_time = '';
                                        //If timezone migration is enabled then remove last 5 characters
                                        if ( strlen($event_time) == 24 ) $event_time = substr($event_time, 0, -5);
                                        $cff_event_date = '';
                                        if (!empty($event_time)) $cff_event_date = '<p class="cff-date" '.$cff_event_date_styles.'><span class="cff-start-date">' . cff_eventdate(strtotime($event_time), $cff_event_date_formatting, $cff_event_date_custom) . '</span>' . $event_end_time.'</p>';

                                        //EVENT
                                        //Display the event details
                                        $cff_event .= '<div class="cff-details';
                                        if($cff_event_has_cover_photo) $cff_event .= ' cff-has-cover';
                                        $cff_event .= '">';
                                        //show event date above title
                                        if ($cff_event_date_position == 'above') $cff_event .= $cff_event_date;
                                        //Show event title
                                        if ($cff_show_event_title && !empty($event_object->name)) {
                                            if ($cff_event_title_link) $cff_event .= '<a href="'.$link.'" '.$target.$cff_nofollow.'>';
                                            $cff_event .= '<' . $cff_event_title_format . ' ' . $cff_event_title_styles . '>' . $event_object->name . '</' . $cff_event_title_format . '>';
                                            if ($cff_event_title_link) $cff_event .= '</a>';
                                        }
                                        //show event date below title
                                        if ($cff_event_date_position !== 'above') $cff_event .= $cff_event_date;
                                        //Show event details
                                        if ($cff_show_event_details){
                                            //Location
                                            if (!empty($event_object->location)) $cff_event .= '<p class="cff-where" ' . $cff_event_details_styles . '>' . $event_object->location . '</p>';
                                            //Description
                                            if (!empty($event_object->description)){
                                                $description = $event_object->description;
                                                if (!empty($body_limit)) {
                                                    if (strlen($description) > $body_limit) $description = substr($description, 0, $body_limit) . '...';
                                                }
                                                $cff_event .= '<p class="cff-info" ' . $cff_event_details_styles . '>' . cff_autolink($description, $link_color=$cff_event_link_color) . '</p>';
                                            }
                                        }
                                        $cff_event .= '</div>';
                                        
                                    }
                                }

                                //MEDIA
                                $cff_media = '';
                                //If it's a photo or a Featured post which is an image
                                if ($news->type == 'photo' || $news->type == 'offer' || ( $cff_featured_post_active && !empty($atts['featuredpost']) && isset($news->images) ) ) {
                                    if ($cff_post_type == 'offer' && !empty($news->picture)){
                                        $picture = $news->picture;
                                        /*If the image doesn't have a _b version then the URL looks like this:
                                        http://photos-h.ak.fbcdn.net/hphotos-ak-prn1/v/1600273_348160658659104_383135394_s.jpg?oh=23124db338cd899962fa7fb2d7285306&oe=52D5F9BE&__gda__=1389770591_64da0df3e725ca2d1fd026b0e922c58b
                                        So check for this kind of string below and don't replace _s. with _b.
                                        */
                                        $bigjpg = '_s.jpg?';
                                        $bigpng = '_s.png?';
                                        $biggif = '_s.gif?';
                                        $bigbmp = '_s.bmp?';
                                        $bigtjpg = '_t.jpg?';
                                        $bigtpng = '_t.png?';
                                        $bigtgif = '_t.gif?';
                                        $bigtbmp = '_t.bmp?';
                                        $imagecheck1 = stripos($picture, $bigjpg);
                                        $imagecheck2 = stripos($picture, $bigpng);
                                        $imagecheck3 = stripos($picture, $biggif);
                                        $imagecheck4 = stripos($picture, $bigbmp);
                                        $imagecheck5 = stripos($picture, $bigtjpg);
                                        $imagecheck6 = stripos($picture, $bigtpng);
                                        $imagecheck7 = stripos($picture, $bigtgif);
                                        $imagecheck8 = stripos($picture, $bigtbmp);

                                        if ( !($imagecheck1 || $imagecheck2 || $imagecheck3 || $imagecheck4 || $imagecheck5 || $imagecheck6 || $imagecheck7 || $imagecheck8) ) {
                                            //Show larger image
                                            $picture = str_replace('_s.','_b.',$picture);
                                            $picture = str_replace('_q.','_b.',$picture);
                                            $picture = str_replace('_t.','_b.',$picture);
                                        }
                                    }

                                    //If the full_picture option is available then use that instead of the object ID method
                                    if( isset($news->full_picture) ) $picture = $news->full_picture;

                                    if ($cff_facebook_link_text == '') $cff_facebook_link_text = 'View on Facebook';
                                    $link_text = $cff_facebook_link_text;

                                    $cff_media = '<a title="'.$link_text.'" class="cff-photo';
                                    if($cff_media_position == 'above') $cff_media .= ' cff-media-above';
                                    $cff_media .= '" href="';

                                    //If it's an album then link the photo to the album
                                    if ($cff_album) {
                                        $link = $album_link;
                                    }

                                    //If it's a shared post then change the link to use the Post ID so that it links to the shared post and not the original post that's being shared
                                    if( isset($news->status_type) ){
                                        if( $news->status_type == 'shared_story' ) $link = "https://www.facebook.com/" . $cff_post_id;
                                    }

                                    $cff_media .= $link.'" '.$target.$cff_nofollow.'>';

                                    //Remove any quotes from message
                                    $cff_message_raw = str_replace('"', "", $cff_message_raw);
                                    $cff_message_raw = str_replace("'", "", $cff_message_raw);

                                    //Alt text
                                    isset( $cff_message_raw ) ? $cff_alt_text = strip_tags($cff_message_raw) : $cff_alt_text = $cff_facebook_link_text;

                                    // if ($cff_album && $num_photos > 1) $cff_media .= '<div class="cff-album-icon">'.$num_photos.'</div>';
                                    if($cff_album) $cff_media .= '<div class="cff-album-icon"></div>';

                                    //Fix Photon (Jetpack) issue
                                    $cff_picture_querystring = '';
                                    if (parse_url($picture, PHP_URL_QUERY)){
                                        $picture_url_parts = parse_url($picture);
                                        $cff_picture_querystring = $picture_url_parts['query'];
                                    }

                                    $cff_media .= '<img src="'. $picture .'" alt="'.$cff_alt_text.'" data-querystring="'.$cff_picture_querystring.'" />';
                                    $cff_media .= '</a>';
                                }
                                if ($news->type == 'swf') {

                                    if (!empty($news->picture)) {
                                        $picture = $news->picture;

                                        /*If the image doesn't have a _b version then the URL looks like this:
                                        http://photos-h.ak.fbcdn.net/hphotos-ak-prn1/v/1600273_348160658659104_383135394_s.jpg?oh=23124db338cd899962fa7fb2d7285306&oe=52D5F9BE&__gda__=1389770591_64da0df3e725ca2d1fd026b0e922c58b
                                        So check for this kind of string below and don't replace _s. with _b.
                                        */
                                        $bigjpg = '_s.jpg?';
                                        $bigpng = '_s.png?';
                                        $biggif = '_s.gif?';
                                        $bigbmp = '_s.bmp?';
                                        $bigtjpg = '_t.jpg?';
                                        $bigtpng = '_t.png?';
                                        $bigtgif = '_t.gif?';
                                        $bigtbmp = '_t.bmp?';
                                        $imagecheck1 = stripos($picture, $bigjpg);
                                        $imagecheck2 = stripos($picture, $bigpng);
                                        $imagecheck3 = stripos($picture, $biggif);
                                        $imagecheck4 = stripos($picture, $bigbmp);
                                        $imagecheck5 = stripos($picture, $bigtjpg);
                                        $imagecheck6 = stripos($picture, $bigtpng);
                                        $imagecheck7 = stripos($picture, $bigtgif);
                                        $imagecheck8 = stripos($picture, $bigtbmp);

                                        if ( !($imagecheck1 || $imagecheck2 || $imagecheck3 || $imagecheck4 || $imagecheck5 || $imagecheck6 || $imagecheck7 || $imagecheck8) ) {
                                            //Show larger image
                                            $picture = str_replace('_s.','_b.',$picture);
                                            $picture = str_replace('_q.','_b.',$picture);
                                            $picture = str_replace('_t.','_b.',$picture);
                                        }
                                    }

                                    $cff_swf_url = 'http://www.facebook.com/permalink.php?story_fbid='.$PostID["1"].'&amp;id='.$PostID['0'];
                                    $cff_media = '<a href="'.$cff_swf_url.'" class="cff-photo';
                                    if($cff_media_position == 'above') $cff_media .= ' cff-media-above';
                                    $cff_media .= '" ' . $target . $cff_nofollow.'><img src="' . $picture . '" /></a>';
                                }

                                if ($news->type == 'video' && !$cff_soundcloud) {

                                    if (!empty($news->picture)) {
                                        $picture = $news->picture;

                                        // $object_id = $news->object_id;
                                        // $picture = 'https://graph.facebook.com/'.$object_id.'/picture?type=normal&width=9999&height=9999';

                                        /*If the image doesn't have a _b version then the URL looks like this:
                                        http://photos-h.ak.fbcdn.net/hphotos-ak-prn1/v/1600273_348160658659104_383135394_s.jpg?oh=23124db338cd899962fa7fb2d7285306&oe=52D5F9BE&__gda__=1389770591_64da0df3e725ca2d1fd026b0e922c58b
                                        So check for this kind of string below and don't replace _s. with _b.
                                        */
                                        $bigjpg = '_s.jpg?';
                                        $bigpng = '_s.png?';
                                        $biggif = '_s.gif?';
                                        $bigbmp = '_s.bmp?';
                                        $bigtjpg = '_t.jpg?';
                                        $bigtpng = '_t.png?';
                                        $bigtgif = '_t.gif?';
                                        $bigtbmp = '_t.bmp?';
                                        $imagecheck1 = stripos($picture, $bigjpg);
                                        $imagecheck2 = stripos($picture, $bigpng);
                                        $imagecheck3 = stripos($picture, $biggif);
                                        $imagecheck4 = stripos($picture, $bigbmp);
                                        $imagecheck5 = stripos($picture, $bigtjpg);
                                        $imagecheck6 = stripos($picture, $bigtpng);
                                        $imagecheck7 = stripos($picture, $bigtgif);
                                        $imagecheck8 = stripos($picture, $bigtbmp);

                                        if ( !($imagecheck1 || $imagecheck2 || $imagecheck3 || $imagecheck4 || $imagecheck5 || $imagecheck6 || $imagecheck7 || $imagecheck8) ) {
                                            //Show larger image
                                            $picture = str_replace('_s.','_b.',$picture);
                                            $picture = str_replace('_q.','_b.',$picture);
                                            $picture = str_replace('_t.','_b.',$picture);
                                        }
                                    }

                                    // url of video
                                    $url = $news->source;
                                    
                                    //Check whether it's a youtube video
                                    if($youtube || $youtu || $youtubeembed) {
                                        //Get the unique video id from the url by matching the pattern
                                        if ($youtube || $youtubeembed) {
                                            if (preg_match("/v=([^&]+)/i", $url, $matches)) {
                                                $id = $matches[1];
                                            }   elseif(preg_match("/\/v\/([^&]+)/i", $url, $matches)) {
                                                $id = $matches[1];
                                            }   elseif(preg_match("/\/embed\/([^&]+)/i", $url, $matches)) {
                                                $id = $matches[1];
                                            }
                                        } elseif ($youtu) {
                                            $id = end(explode('/', $url));
                                        }
                                        $id = substr($id, 0, strrpos($id, '?'));
                                        // this is your template for generating embed codes
                                        $code = '<iframe class="youtube-player" type="text/html" src="https://www.youtube.com/embed/{id}" allowfullscreen></iframe>';
                                        // we replace each {id} with the actual ID of the video to get embed code for this particular video
                                        $code = str_replace('{id}', $id, $code);

                                        $cff_media_video = '<div class="cff-iframe-wrap" data-poster="'.$picture.'"';
                                        if(!empty($cff_video_height)) $cff_media_video .= 'style="height: '. $cff_video_height . '"';
                                        $cff_media_video .= '>';

                                        if($cff_video_action == 'facebook') $cff_media_video .= '<a href="http://facebook.com/'.$cff_post_id.'" target="_blank" class="cff-media-overlay"></a>';

                                        $cff_media_video .= $code . '</div>';

                                    //Check whether it's a vimeo
                                    } else if(stripos($url, $vimeo) !== false) {
                                        if (isset($news->source)) {

                                            $clip_id = '';
                                            //http://vimeo.com/moogaloop.swf?clip_id=101557016&autoplay=1
                                            $query = parse_url($news->source, PHP_URL_QUERY);
                                            parse_str($query, $params);
                                            if(isset($params['clip_id'])) $clip_id = $params['clip_id'];

                                            //https://player.vimeo.com/video/116446625?autoplay=1
                                            if( !isset($clip_id) || $clip_id == '' ){
                                                $vimeo_url = strtok($news->source,'?');
                                                $clip_id = end((explode('/', $vimeo_url)));
                                            }

                                            $cff_media_video = '<div class="cff-iframe-wrap" data-poster="'.$picture.'"';
                                            if(!empty($cff_video_height)) $cff_media_video .= 'style="height: '. $cff_video_height . '"';
                                            $cff_media_video .= '>';

                                            if($cff_video_action == 'facebook') $cff_media_video .= '<a href="http://facebook.com/'.$cff_post_id.'" target="_blank" class="cff-media-overlay"></a>';

                                            $cff_media_video .= '<iframe src="https://player.vimeo.com/video/'.$clip_id.'" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe></div>';
                                        }

                                    //Else link to the video file
                                    } else {
                                        //Show play button over video thumbnail
                                        $vid_link = $news->source;
                                        //Check whether the video source contains an mp4, as the HTML5 video player can't play any other type
                                        $cff_mp4_check = stripos($vid_link, '.mp4');

                                        if ($cff_video_action == 'facebook' && $cff_disable_lightbox) $vid_link = $link;

                                        //Title & alt text
                                        isset( $news->name ) ? $vid_title = $news->name : $vid_title = $cff_facebook_link_text;

                                        if (empty($picture)) {
                                            $cff_is_video_embed = true;
                                            $cff_media_video = '<a class="cff-playbtn-solo" title="' . $vid_title . '" href="' . $vid_link . '" target="_blank"><i class="fa fa-play cff-playbtn no-poster"></i></a>';
                                        }

                                        ( isset($news->full_picture) && !empty($news->full_picture) ) ? $poster = $news->full_picture : $poster = $picture;

                                        //Check to see whether it's a swf file and if it is then load it into an iframe in the lightbox
                                        (stripos($url, $swf) !== false) ? $swf_file = true : $swf_file = false;

                                        //If the video action is file then add the HTML5 video tags
                                        $cff_media_video = '';
                                        if ( ($cff_video_action !== 'facebook' && $cff_mp4_check) || !$cff_disable_lightbox ){
                                            $cff_media_video .= '<div class="cff-html5-video';
                                            if( $swf_file ) $cff_media_video .= ' cff-swf';
                                            $cff_media_video .= '"><a href="http://facebook.com/'.$cff_post_id.'" class="cff-html5-play"><i class="fa fa-play cff-playbtn"></i></a><video src="'.$vid_link.'" poster="'.$poster.'" >';
                                        }

                                        //Fix Photon (Jetpack) issue
                                        $cff_picture_querystring = '';
                                        if (parse_url($poster, PHP_URL_QUERY)){
                                            $picture_url_parts = parse_url($poster);
                                            $cff_picture_querystring = $picture_url_parts['query'];
                                        }

                                        $cff_media_video .= '<a title="' . $vid_title . '" class="cff-vidLink" href="' . $link . '" '.$target.$cff_nofollow.'><i class="fa fa-play cff-playbtn"></i><img class="cff-poster" src="' . $poster . '" alt="' . $vid_title . '" data-querystring="'.$cff_picture_querystring.'" /></a>';

                                        if ( ($cff_video_action !== 'facebook' && $cff_mp4_check) || !$cff_disable_lightbox ) $cff_media_video .= '</video></div>';
                                    }
                                    //Add video to HTML
                                    $cff_media = $cff_media_video;


                                    //Add the name to the description if it's a video embed
                                    if($cff_is_video_embed) {
                                        $cff_description = '<div class="cff-desc-wrap ';
                                        if (empty($picture)) $cff_description .= 'cff-no-image';
                                        $cff_description .= '"><'.$cff_link_title_format.' class="cff-link-title" '.$cff_link_title_styles.'><a href="'.$link.'" '.$target.$cff_nofollow.' style="color:#' . $cff_link_title_color . ';">'. $news->name . '</a></'.$cff_link_title_format.'>';

                                        if (!empty($body_limit)) {
                                            if (strlen($description_text) > $body_limit) $description_text = substr($description_text, 0, $body_limit) . '...';
                                        }

                                        $cff_description .= '<p class="cff-post-desc" '.$cff_body_styles.'><span>' . cff_autolink( htmlspecialchars($description_text), $link_color=$cff_posttext_link_color )  . ' </span></p></div>';
                                    }
                                }
                                //META
                                //how many comments are there?
                                $comment_count = 0;
                                $comment_count_display = '0';

                                //Save the original $news object to a variable so can use it after the comments section
                                $news_event = $news;
                                //If it's a timeline event then switch to the event_object variable which contains the comments
                                if( $cff_post_type == 'event' ) $news = $event_object;

                                if (!empty($news->comments)) {
                                    $comment_count = count($news->comments->data);
                                    $comment_count_display = $comment_count;
                                    if ($comment_count > 20){
                                        //If count is more than 20 then it could be in the cached array
                                        $item_arr_name = $cff_post_id . '_comments';

                                        //If the transient doesn't exist
                                        if ( false === ( $cff_cached_meta = get_transient( 'cff_meta' ) ) ) {
                                            $comment_count_display = '<div class="cff-loader fa-spin"></div><span class="cff-replace">20+</span>';
                                        } else {

                                            if ( empty($cff_cached_meta[$item_arr_name]) ){
                                                $comment_count_display = '<div class="cff-loader fa-spin"></div><span class="cff-replace">20+</span>';
                                            } else {
                                                //load corresponding count from array in transient
                                                $comment_count_display = $cff_cached_meta[$item_arr_name];
                                            }
                                            
                                        }

                                    }
                                }

                                $cff_meta_total = '<div class="cff-meta-wrap">';
                                //Check for likes
                                $cff_meta = '';
                                $cff_meta .= '<a href="javaScript:void(0);" class="cff-view-comments" ' . $cff_meta_styles . ' id="'.$orig_post_id.'"><ul class="cff-meta ';
                                $cff_meta .= $cff_icon_style;
                                $cff_meta .= '"><li class="cff-likes"><span class="cff-icon">Likes:</span> <span class="cff-count">';
                                
                                //How many likes are there?
                                if (!empty($news->likes)) {
                                    $like_count = count($news->likes->data);
                                } else {
                                    $like_count = '0';
                                }
                                if( $cff_post_type == 'event' ){
                                    if (!empty($news_event->likes)) {
                                        $like_count = count($news_event->likes->data);
                                    } else {
                                        $like_count = '0';
                                    }
                                }

                                //If there is no likes then display zero
                                if ($like_count == 0) {
                                    $cff_meta .= '0';
                                } else if ($like_count < 25) {
                                    $cff_meta .= $like_count;
                                } else {

                                    //If count is more than 20 then it could be in the cached array
                                    $item_arr_name = $cff_post_id . '_likes';

                                    //If the transient doesn't exist
                                    if ( false === ( $cff_cached_meta = get_transient( 'cff_meta' ) ) ) {
                                        $cff_meta .= '<div class="cff-loader fa-spin"></div><span class="cff-replace">' . $like_count . '+</span>';
                                    } else {
                                        
                                        if ( empty($cff_cached_meta[$item_arr_name]) ){
                                            $cff_meta .= '<div class="cff-loader fa-spin"></div><span class="cff-replace">' . $like_count . '+</span>';
                                        } else {
                                            //load corresponding count from array in transient
                                            //like_count var is also used in the -2 line inside the comments box
                                            $like_count = $cff_cached_meta[$item_arr_name];
                                            $cff_meta .= $like_count;
                                        }
                                        
                                    }
                                    
                                }
                                //Check for shares
                                $cff_meta .= '</span></li><li class="cff-shares"><span class="cff-icon">Shares:</span> <span class="cff-count">';
                                if (empty($news->shares->count)) { $cff_meta .= '0'; }
                                    else { $cff_meta .= $news->shares->count; }
                                //Check for comments
                                $cff_meta .= '</span></li><li class="cff-comments"><span class="cff-icon">Comments:</span> <span class="cff-count">';
                                //How many comments are there?
                                $cff_meta .= $comment_count_display;
                                $cff_meta .= '</span></li></ul></a>';
                                //Display the link to the Facebook post or external link
                                $cff_link = '';
                                //Default link
                                $cff_viewpost_class = 'cff-viewpost-facebook';
                                if ($cff_facebook_link_text == '') $cff_facebook_link_text = 'View on Facebook';
                                $link_text = $cff_facebook_link_text;

                                //Link to the Facebook post if it's a link or a video
                                if($cff_post_type == 'link' || $cff_post_type == 'video') $link = "https://www.facebook.com/" . $page_id . "/posts/" . $PostID[1];


                                //If Featured Post extension then change the $link var based on whether a full or half post ID is used
                                if ($cff_featured_post_active && !empty($atts['featuredpost'])) {
                                    
                                    //If the post type is a link or a video (other link types have the link included in the JSON)
                                    if($cff_post_type == 'link' || $cff_post_type == 'video'){

                                        if ( stripos($cff_post_id, '_') !== false ) {
                                            //If using the full post ID with an underscore then create the link like this:
                                            $link = "https://www.facebook.com/" . $PostID[0] . "/posts/" . $PostID[1];
                                        } else {
                                            //If just using the short ID then create the link like this:
                                            $link = "https://www.facebook.com/" . $cff_post_id;
                                        }
                                    }

                                }

                                //Social media sharing URLs
                                $cff_share_facebook = 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($link);
                                $cff_share_twitter = 'https://twitter.com/intent/tweet?text=' . urlencode($link);
                                $cff_share_google = 'https://plus.google.com/share?url=' . urlencode($link);
                                $cff_share_linkedin = 'https://www.linkedin.com/shareArticle?mini=true&amp;url=' . urlencode($link) . '&amp;title=' . rawurlencode( strip_tags($cff_post_text) );
                                $cff_share_email = 'mailto:?subject=Facebook&amp;body=' . urlencode($link) . '%20-%20' . rawurlencode( strip_tags($cff_post_text) );

                                //If it's a shared post then change the link to use the Post ID so that it links to the shared post and not the original post that's being shared
                                if( isset($news->status_type) ){
                                    if( $news->status_type == 'shared_story' ) $link = "https://www.facebook.com/" . $cff_post_id;
                                }
                                //If there's an object_id then use that as it's more reliable for posts by other people
                                if( isset($news->object_id) ){
                                    $link = "https://www.facebook.com/" . $news->object_id;
                                }

                                //If it's an offer post then change the text
                                if ($cff_post_type == 'offer') $link_text = 'View Offer';

                                //Create post action links HTML
                                $cff_link = '';
                                if($cff_show_facebook_link || $cff_show_facebook_share){
                                    $cff_link .= '<div class="cff-post-links">';

                                    //View on Facebook link
                                    if($cff_show_facebook_link) $cff_link .= '<a class="' . $cff_viewpost_class . '" href="' . $link . '" title="' . $link_text . '" ' . $target . $cff_nofollow.' ' . $cff_link_styles . '>' . $link_text . '</a>';

                                    //Share link
                                    if($cff_show_facebook_share){
                                        $cff_link .= '<div class="cff-share-container">';
                                        //Only show separating dot if both links are enabled
                                        if($cff_show_facebook_link) $cff_link .= '<span class="cff-dot" ' . $cff_link_styles . '>&middot;</span>';
                                        $cff_link .= '<a class="cff-share-link" href="javascript:void(0);" title="' . $cff_facebook_share_text . '" ' . $cff_link_styles . '>' . $cff_facebook_share_text . '</a>';
                                        $cff_link .= "<p class='cff-share-tooltip'><a href='".$cff_share_facebook."' target='_blank' class='cff-facebook-icon'><i class='fa fa-facebook-square'></i></a><a href='".$cff_share_twitter."' target='_blank' class='cff-twitter-icon'><i class='fa fa-twitter'></i></a><a href='".$cff_share_google."' target='_blank' class='cff-google-icon'><i class='fa fa-google-plus'></i></a><a href='".$cff_share_linkedin."' target='_blank' class='cff-linkedin-icon'><i class='fa fa-linkedin'></i></a><a href='".$cff_share_email."' target='_blank' class='cff-email-icon'><i class='fa fa-envelope'></i></a><i class='fa fa-play fa-rotate-90'></i></p></div>";
                                    }
                                    
                                    $cff_link .= '</div>'; 
                                }
                                
                                
                                //Compile the meta and link if included
                                if ($cff_show_link) $cff_meta_total .= $cff_link;
                                if ($cff_show_meta) $cff_meta_total .= $cff_meta;
                                $cff_meta_total .= '</div>';
                                $cff_comments = '';

                                //Get custom text strings
                                $cff_translate_view_previous_comments_text = $atts['previouscommentstext'];
                                $cff_translate_comment_on_facebook_text = $atts['commentonfacebooktext'];
                                $cff_translate_likes_this_text = $atts['likesthistext'];
                                $cff_translate_like_this_text = $atts['likethistext'];
                                $cff_translate_and_text = $atts['andtext'];
                                $cff_translate_other_text = $atts['othertext'];
                                $cff_translate_others_text = $atts['otherstext'];
                                $cff_translate_reply_text = $atts['replytext'];
                                $cff_translate_replies_text = $atts['repliestext'];
                                

                                if (!isset($cff_translate_view_previous_comments_text) || empty($cff_translate_view_previous_comments_text)) $cff_translate_view_previous_comments_text = 'View previous comments';
                                if (!isset($cff_translate_comment_on_facebook_text) || empty($cff_translate_comment_on_facebook_text)) $cff_translate_comment_on_facebook_text = 'Comment on Facebook';
                                if (!isset($cff_translate_likes_this_text) || empty($cff_translate_likes_this_text)) $cff_translate_likes_this_text = 'likes this';
                                if (!isset($cff_translate_like_this_text) || empty($cff_translate_like_this_text)) $cff_translate_like_this_text = 'like this';
                                if (!isset($cff_translate_and_text) || empty($cff_translate_and_text)) $cff_translate_and_text = 'and';
                                if (!isset($cff_translate_other_text) || empty($cff_translate_other_text)) $cff_translate_other_text = 'other';
                                if (!isset($cff_translate_others_text) || empty($cff_translate_others_text)) $cff_translate_others_text = 'others';
                                if (!isset($cff_translate_reply_text) || empty($cff_translate_reply_text)) $cff_translate_reply_text = 'Reply';
                                if (!isset($cff_translate_replies_text) || empty($cff_translate_replies_text)) $cff_translate_replies_text = 'Replies';

                                //Create the comments box
                                $cff_comments .= '<div class="cff-comments-box ' . $cff_icon_style;
                                if( $comment_count == 0 || $cff_comments_num == 0 ) $cff_comments .= ' cff-no-comments';
                                $cff_comments .= '"';

                                //Expand comments box initially
                                if( $cff_expand_comments ) $cff_comments .= ' style="display: block;"';
                                //Number of comments to show initially
                                $cff_comments .= ' data-num="' . $cff_comments_num . '"';
                                $cff_comments .= '>';


                                //If it's a timeline event then change the $news object to be the original news object before it was changed to get the event comment count above
                                if( $cff_post_type == 'event' ) $news = $news_event;
                                
                                //Get the likes
                                if (!empty($news->likes->data)){

                                    $liker_one = '';
                                    $liker_two = ''; 

                                    if ( $news->likes->data[0] ) $liker_one = '<a href="https://facebook.com/'.$news->likes->data[0]->id.'" '.$cff_meta_link_color.' '.$target.$cff_nofollow.'>' . $news->likes->data[0]->name . '</a>';
                                    if ( $like_count > 1 ) $liker_two = '<a href="https://facebook.com/'.$news->likes->data[1]->id.'" '.$cff_meta_link_color.' '.$target.$cff_nofollow.'>' . $news->likes->data[1]->name . '</a>';

                                    if ($like_count > 0) $cff_comments .= '<p class="cff-comment-likes cff-likes" ' . $cff_meta_styles . '><span class="cff-icon"></span>';
                                    if ($like_count == 1){
                                        $cff_comments .= $liker_one.' '.$cff_translate_likes_this_text;
                                    } else if ($like_count == 2){
                                        $cff_comments .= $liker_one.' '.$cff_translate_and_text.' '.$liker_two.' '.$cff_translate_like_this_text;
                                    } else if ($like_count == 3){
                                        $cff_comments .= $liker_one.', '.$liker_two.' '.$cff_translate_and_text.' 1 '.$cff_translate_other_text.' '.$cff_translate_like_this_text;
                                    } else {
                                        $cff_comments .= $liker_one.', '.$liker_two.' '.$cff_translate_and_text.' ';
                                        if ($like_count == 25) $cff_comments .= '<span class="cff-comment-likes-count">';
                                        $cff_comments .= intval($like_count)-2;
                                        if ($like_count == 25) $cff_comments .= '</span>';
                                        $cff_comments .= ' '.$cff_translate_others_text.' '.$cff_translate_like_this_text;
                                    }
                                    if ($like_count > 0) $cff_comments .= '</p>';

                                }

                                //If it's a timeline event then change the $news object to be the event object to get the event comment count above
                                if( $cff_post_type == 'event' ) $news = $event_object;

                                //Show more comments
                                if ( $comment_count > $cff_comments_num ) $cff_comments .= '<p class="cff-comments cff-show-more-comments" ' . $cff_meta_styles . '><a href="javascript:void(0);" '.$cff_meta_link_color.'><span class="cff-icon"></span>'.$cff_translate_view_previous_comments_text.'</a></p>';

                                //Get the comments
                                if (!empty($news->comments->data)){
                                    //Give the comment an index so we know which one it is
                                    $comment_index = 0;

                                    //Loop through comments
                                    foreach ($news->comments->data as $comment_item ) {
                                        $comment_likes = $comment_item->like_count;
                                        $comment = htmlspecialchars($comment_item->message);

                                        //MESSAGE TAGS
                                        if( $cff_post_tags && isset($comment_item->message_tags) ){

                                            //Loop through the tags and use the name to replace them
                                            foreach($comment_item->message_tags as $message_tag ) {
                                                $tag_name = $message_tag->name;
                                                $tag_link = '<a href="http://facebook.com/' . $message_tag->id . '" '.$target.$cff_nofollow.' '.$cff_meta_link_color.'>' . $message_tag->name . '</a>';

                                                $comment = str_replace($tag_name, $tag_link, $comment);
                                            }

                                        } //END MESSAGE TAGS

                                        //Create comments
                                        if( isset( $comment_item->from ) ){
                                            $cff_comment_id = $comment_item->from->id;
                                            $cff_comment_name = $comment_item->from->name;
                                        } else {
                                            $cff_comment_id = '';
                                            $cff_comment_name = '';
                                        }
                                        $cff_comments .= '<div class="cff-comment" id="cff_'.$cff_comment_id.'" data-id="'.$cff_comment_id.'" ' . $cff_meta_styles . '>';

                                        $cff_comments .= '<div class="cff-comment-text-wrapper">';
                                        $cff_comments .= '<div class="cff-comment-text';
                                        if( $cff_hide_comment_avatars ) $cff_comments .= ' cff-no-image';
                                        $cff_comments .= '"><p><a href="https://facebook.com/'. $cff_comment_id .'" class="cff-name" '.$target.$cff_nofollow.' ' . $cff_meta_link_color . '>' . $cff_comment_name . '</a>' . cff_autolink( $comment, $link_color=str_replace('#', '', $atts['sociallinkcolor']) ) . '</p>';

                                        //Add image attachment if exists
                                        if( isset($comment_item->attachment) ){
                                            if( isset($comment_item->attachment->media) ){
                                                $cff_comments .= '<a class="cff-comment-attachment" href="'.$comment_item->attachment->url.'" target="_blank"><img src="'.$comment_item->attachment->media->image->src.'" alt="';
                                                if( isset($comment_item->attachment->title) ){
                                                    $cff_comments .= $comment_item->attachment->title;
                                                } else {
                                                    $cff_comments .= 'Attachment';
                                                }
                                                $cff_comments .='" /></a>';
                                            }
                                        }

                                        $cff_comments .= '<span class="cff-time">';
                                        $cff_comments .= cff_timeSince(strtotime($comment_item->created_time), $cff_date_translate_strings) . ' ' . $cff_date_after;
                                        if ( $comment_likes > 0 ) $cff_comments .= '<span class="cff-comment-likes">&nbsp; &middot; &nbsp;<b></b>' . $comment_likes . '</span>';
                                        $cff_comments .= '</span>';

                                        if( isset( $comment_item->comment_count ) ){
                                            $cff_comment_count = intval($comment_item->comment_count);
                                            if( $cff_comment_count > 0 ){
                                                ($cff_comment_count == 1) ? $cff_replies_text_string = $cff_translate_reply_text : $cff_replies_text_string = $cff_translate_replies_text;
                                                $cff_comments .= '<p class="cff-comment-replies" data-id="'.$comment_item->id.'"><a href="javascript:void(0);" ' . $cff_meta_link_color . '><span class="cff-replies-icon"></span>' . $cff_comment_count . ' '.$cff_replies_text_string.'</a></p><div class="cff-comment-replies-box cff-empty"></div>';
                                            }

                                        }

                                        $cff_comments .= '</div>'; //End .cff-comment-text
                                        $cff_comments .= '</div>'; //End .cff-comment-text-wrapper

                                        $cff_comments .= '<div class="cff-comment-img"><a href="https://facebook.com/'. $cff_comment_id .'" '.$target.$cff_nofollow. '>';

                                        //Only load the comment avatars if they're being displayed initially, otherwise load via JS on click
                                        if( !$cff_hide_comment_avatars ){
                                            if( $cff_expand_comments && ($comment_index >= $comment_count - $cff_comments_num) ) {
                                                $cff_comments .= '<img src="https://graph.facebook.com/'.$cff_comment_id.'/picture" width=32 height=32  alt="'.$cff_comment_name.'">';
                                            } else {
                                                $cff_comments .= '<img src="'.plugins_url( '/img/avatar.png' , __FILE__ ).'" width=32 height=32 alt="Avatar">';
                                            }
                                        }

                                        $cff_comments .= '</a></div>';
                                        $cff_comments .= '</div>'; //End .cff-comment

                                        $comment_index++;
                                    }
                                    
                                }
                                $cff_comments .= '<p class="cff-comments cff-comment-on-facebook" ' . $cff_meta_styles . '><a href="'.$link.'" '.$target.$cff_nofollow.' '.$cff_meta_link_color.'><span class="cff-icon"></span>'.$cff_translate_comment_on_facebook_text.'</a></p>';
                                $cff_comments .= '</div>';
                                
                                //Compile comments if meta is included
                                if ($cff_show_meta) $cff_meta_total .= $cff_comments;

                                //If it's an event then set the $news object back to the original posts data rather than the new event data object used to get the comments for the event
                                if( $cff_post_type == 'event' ) $news = $news_event;

                                //**************************//
                                //***CREATE THE POST HTML***//
                                //**************************//
                                //Start the container
                                $cff_post_item .= '<div class="cff-item ';
                                if ($cff_post_type == 'link') $cff_post_item .= 'cff-link-item';
                                if ($cff_post_type == 'event') $cff_post_item .= 'cff-timeline-event';
                                if ($cff_post_type == 'photo') $cff_post_item .= 'cff-photo-post';
                                if ($cff_post_type == 'video' && !$cff_soundcloud) $cff_post_item .= 'cff-video-post';
                                if ($cff_soundcloud) $cff_post_item .= 'cff-audio-post';

                                if ($cff_is_video_embed) $cff_post_item .= ' cff-embedded-video';
                                if ($cff_post_type == 'swf') $cff_post_item .= 'cff-swf-post';
                                if ($cff_post_type == 'status') $cff_post_item .= 'cff-status-post';
                                if ($cff_post_type == 'offer') $cff_post_item .= 'cff-offer-post';
                                if ($cff_album) $cff_post_item .= ' cff-album';
                                if ($cff_post_bg_color_check) $cff_post_item .= ' cff-box';
                                $cff_post_item .= ' author-';
                                if(isset($news->from->name)) $cff_post_item .= cff_to_slug($news->from->name);
                                $cff_post_item .= '" id="cff_'. $cff_post_id .'" ' . $cff_item_styles . '>';

                                //POST AUTHOR
                                $cff_is_video_embed = false;
                                if($cff_is_video_embed){
                                    if($cff_show_author) $cff_post_item .= $cff_author;
                                    //DATE ABOVE
                                    if ($cff_show_date && $cff_date_position == 'above') $cff_post_item .= $cff_date;
                                    //If embedded video then show post text above the wrapper
                                    if($cff_show_text) $cff_post_item .= $cff_post_text;
                                    
                                    $cff_post_item .= '<div class="cff-embed-wrap">';
                                }


                                //Start text wrapper
                                if ( ($cff_thumb_layout || $cff_half_layout) && (!empty($news->picture) || ($cff_post_type == 'event' && $cff_event_has_cover_photo) ) ) $cff_post_item .= '<div class="cff-text-wrapper">';
                                    
                                    //POST AUTHOR
                                    if($cff_show_author && !$cff_is_video_embed) $cff_post_item .= $cff_author;
                                    //MEDIA
                                    if($cff_show_media && $cff_media_position == 'above'){
                                        if( $cff_post_type == 'event' ) $cff_media = $cff_timeline_event_photo;
                                        $cff_post_item .= $cff_media;
                                    }
                                    //DATE ABOVE
                                    if ($cff_show_date && $cff_date_position == 'above' && !$cff_is_video_embed) $cff_post_item .= $cff_date;
                                    //POST TEXT
                                    if($cff_show_text && !$cff_is_video_embed) $cff_post_item .= $cff_post_text;

                                    //EVENT
                                    if($cff_show_event_title || $cff_show_event_details) $cff_post_item .= $cff_event;

                                    //DESCRIPTION
                                    if($cff_show_desc && $cff_post_type != 'offer' && $cff_post_type != 'link') $cff_post_item .= $cff_description;
                                    //LINK
                                    if($cff_show_shared_links) $cff_post_item .= $cff_shared_link;
                                    //DATE BELOW
                                    if ( (!$cff_show_author && $cff_date_position == 'author') || $cff_show_date && $cff_date_position == 'below' && !$cff_is_video_embed ) {
                                        if($cff_show_date && $cff_post_type !== 'event') $cff_post_item .= $cff_date;
                                    }

                                //End text wrapper
                                if ( ($cff_thumb_layout || $cff_half_layout) && (!empty($news->picture) || ($cff_post_type == 'event' && $cff_event_has_cover_photo) ) ) $cff_post_item .= '</div>';
                                
                                
                                //MEDIA
                                if($cff_show_media && $cff_media_position !== 'above') {
                                    if( $cff_post_type == 'event' ) $cff_media = $cff_timeline_event_photo;
                                    $cff_post_item .= $cff_media;
                                    if($cff_is_video_embed) $cff_post_item .= '</div>';
                                }
                                //DATE BELOW
                                if ($cff_show_date && $cff_date_position == 'below' && $cff_is_video_embed) $cff_post_item .= $cff_date;
                                if($cff_show_date && $cff_post_type == 'event' && ($cff_date_position == 'below' || ($cff_date_position == 'author' && !$cff_show_author) ) ){
                                    $cff_post_item .= $cff_date;
                                }
                                //META
                                if($cff_show_meta || $cff_show_link) $cff_post_item .= $cff_meta_total;
                                //End the post item
                                $cff_post_item .= '</div>';
                                // $cff_post_item .= '<div class="cff-clear"></div>';

                            } // End !$cff_photos_only || albums only || album embed


                            //REVIEWS
                            if($cff_reviews){
                                $cff_post_item = cff_ext_reviews($news, $cff_reviews_string, $atts, $page_id, $target, $cff_nofollow, $cff_author_styles, $cff_show_date, $cff_date_position, $cff_title_format, $cff_title_styles, $cff_posttext_link_color, $cff_see_more_text, $cff_date, $cff_title_link, $cff_see_less_text, $cff_show_facebook_link, $cff_post_bg_color_check, $post_time, $cff_item_styles, $cff_show_author, $cff_show_link, $cff_post_type, $link, $cff_link_styles, $cff_show_text, $cff_show_post);
                            }


                            //ALBUMS ONLY
                            if($cff_albums_only && $cff_albums_source == 'photospage'){

                                isset($news->link) ? $cff_album_link = $news->link : $cff_album_link = '';
                                isset($news->name) ? $cff_album_name = $news->name : $cff_album_name = '';
                                //Don't put this in for now as the description sometimes has @ markup in it which looks bad. eg: "on behalf of @[38494804824:274:Breakthrough Breast Cancer]."
                                // isset($news->description) ? $cff_album_description = $news->description : $cff_album_description = $cff_album_name;

                                $cff_show_post = true;
                                //Get the filter string
                                $cff_filter_string = $atts[ 'filter' ];

                                if ( $cff_filter_string != '' ){
                                    //Explode it into multiples
                                    $cff_filter_strings_array = explode(',', $cff_filter_string);
                                    //Hide the post if both the post text and description don't contain the string
                                    $string_in_post_text = true;
                                    $string_in_desc = true;
                                    if ( cff_stripos_arr($cff_album_name, $cff_filter_strings_array) === false ) $cff_show_post = false;
                                }

                                $cff_exclude_string = $atts[ 'exfilter' ];
                                if ( $cff_exclude_string != '' ){
                                    //Explode it into multiples
                                    $cff_exclude_strings_array = explode(',', $cff_exclude_string);
                                    //Hide the post if both the post text and description don't contain the string
                                    $string_in_post_text = false;
                                    $string_in_desc = false;
                                    if ( cff_stripos_arr($cff_album_name, $cff_exclude_strings_array) !== false ) $cff_show_post = false;
                                }

                                if( $cff_show_post ){

                                    $cff_cover_photos_available = true;

                                    //ALBUMS ONLY
                                    if($cff_is_group){ //Groups need to use token in the request:
                                        if( isset($news->cover_photo->id) ){
                                            $thumb = 'https://graph.facebook.com/' . $news->cover_photo->id . '/picture?access_token='.$access_token;
                                        } else {
                                            $thumb = '';
                                            $cff_cover_photos_available = false;
                                        }
                                    } else {
                                        if( isset($news->cover_photo->id) ){
                                            $thumb = 'https://graph.facebook.com/' . $news->cover_photo->id . '/picture';
                                        } else if( isset($news->cover_photo) ){
                                            $thumb = 'https://graph.facebook.com/' . $news->cover_photo . '/picture';
                                        } else {
                                            $thumb = '';
                                            $cff_cover_photos_available = false;
                                        }
                                    }


                                    isset($news->count) ? $cff_album_count = $news->count : $cff_album_count = '';

                                    //Cover photos aren't available for group albums unless using a User Access Token
                                    isset( $atts['columnscompatible'] ) ? $columns_compatible = $atts['columnscompatible'] : $columns_compatible = true;
                                    // Certain extensions are incompatible with the columns feature. This
                                    // filter can be used to disable it
                                    if( $columns_compatible ) {
                                        $cff_post_item = '<div class="cff-album-item cff-albums-only cff-col-';
                                        $cff_post_item .= $cff_album_cols;
                                    } else {
                                        $cff_post_item = '<div class="cff-album-item cff-albums-only';
                                    }
                                    $cff_post_item .= '" id="cff_'. $news->id .'">';

                                    //Fix Photon (Jetpack) issue
                                    $cff_picture_querystring = '';
                                    if (parse_url($thumb, PHP_URL_QUERY)){
                                        $picture_url_parts = parse_url($thumb);
                                        $cff_picture_querystring = $picture_url_parts['query'];
                                    }

                                    if( $cff_cover_photos_available ) $cff_post_item .= '<a href="' . $cff_album_link . '" class="cff-album-cover" '.$target.$cff_nofollow.'><img src="'.$thumb.'" alt="' . $cff_album_name . '" data-querystring="'.$cff_picture_querystring.'" /></a>';
                                    if($cff_show_album_title || $cff_show_album_number) $cff_post_item .= '<div class="cff-album-info">';
                                    if($cff_show_album_title) $cff_post_item .= '<h4><a href="' . $cff_album_link . '" '.$target.$cff_nofollow.'>' . $cff_album_name . '</a></h4>';
                                    if( $cff_show_album_number && isset($news->count) ) $cff_post_item .= '<p>' . $cff_album_count . ' '. $cff_translate_photos_text . '</p>';
                                    if($cff_show_album_title || $cff_show_album_number) $cff_post_item .= '</div>';
                                    $cff_post_item .= '</div>';

                                    //Group albums use 'created' instead of 'created_time' like other posts
                                    if($cff_is_group){
                                        ( isset($news->created) ) ? $post_time = $news->created : $post_time = $news->created_time;
                                    } else {
                                        //If there's no photos in the album then don't show it
                                        if( !isset($news->cover_photo) ) $cff_post_item = '';
                                    }

                                }
                                
                            }

                            //ALBUM EMBED
                            if( $cff_album_active && !empty($cff_album_id) ){

                                isset($news->name) ? $cff_album_desc = $news->name : $cff_album_desc = '';

                                $cff_show_post = true;
                                //Get the filter string
                                $cff_filter_string = $atts[ 'filter' ];

                                if ( $cff_filter_string != '' ){
                                    //Explode it into multiples
                                    $cff_filter_strings_array = explode(',', $cff_filter_string);
                                    //Hide the post if both the post text and description don't contain the string
                                    $string_in_post_text = true;
                                    $string_in_desc = true;
                                    if ( cff_stripos_arr($cff_album_desc, $cff_filter_strings_array) === false ) $cff_show_post = false;
                                }

                                $cff_exclude_string = $atts[ 'exfilter' ];
                                if ( $cff_exclude_string != '' ){
                                    //Explode it into multiples
                                    $cff_exclude_strings_array = explode(',', $cff_exclude_string);
                                    //Hide the post if both the post text and description don't contain the string
                                    $string_in_post_text = false;
                                    $string_in_desc = false;
                                    if ( cff_stripos_arr($cff_album_desc, $cff_exclude_strings_array) !== false ) $cff_show_post = false;
                                }

                                // Certain extensions are incompatible with the columns feature. This
                                // filter can be used to disable it
                                isset( $atts['columnscompatible'] ) ? $columns_compatible = $atts['columnscompatible'] : $columns_compatible = true;

                                if( $cff_show_post ){
                                    if( $columns_compatible ) {
                                        $cff_post_item = '<div class="cff-album-item cff-col-';
                                        $cff_post_item .= $cff_album_cols;
                                    } else {
                                        $cff_post_item = '<div class="cff-album-item cff-albums-only';
                                    }

                                    //Fix Photon (Jetpack) issue
                                    $cff_picture_querystring = '';
                                    if (parse_url($news->source, PHP_URL_QUERY)){
                                        $picture_url_parts = parse_url($news->source);
                                        $cff_picture_querystring = $picture_url_parts['query'];
                                    }

                                    $cff_post_item .= '" id="cff_'. $news->id .'">';
                                    $cff_post_item .= '<a href="https://facebook.com/'.$news->id.'" class="cff-album-cover" '.$target.$cff_nofollow.'><img src="'. $news->source .'" alt="'.$cff_album_desc.'" data-querystring="'.$cff_picture_querystring.'" /></a>';
                                    $cff_post_item .= '</div>';
                                    $post_time = $i;
                                }
                            }


                            //VIDEOS ONLY
                            if($cff_videos_only){
                                $cff_post_item = '';

                                isset($news->description) ? $description_text = $news->description : $description_text = '';
                                isset($news->title) ? $video_name = $news->title : $video_name = '';

                                $cff_filter_string = $atts[ 'filter' ];
                                $cff_show_post = true;

                                if ( $cff_filter_string != '' ){
                                    //Explode it into multiples
                                    $cff_filter_strings_array = explode(',', $cff_filter_string);
                                    //Hide the post if both the post text and description don't contain the string
                                    $string_in_post_text = true;
                                    $string_in_desc = true;
                                    if ( cff_stripos_arr($description_text, $cff_filter_strings_array) === false ) $cff_show_post = false;
                                }

                                $cff_exclude_string = $atts[ 'exfilter' ];
                                if ( $cff_exclude_string != '' ){
                                    //Explode it into multiples
                                    $cff_exclude_strings_array = explode(',', $cff_exclude_string);
                                    //Hide the post if both the post text and description don't contain the string
                                    $string_in_post_text = false;
                                    $string_in_desc = false;
                                    if ( cff_stripos_arr($description_text, $cff_exclude_strings_array) !== false ) $cff_show_post = false;
                                }

                                if( $cff_show_post ){

                                    foreach ($news->format as $value) {
                                        //If there's a large image then use it
                                        if( isset( $value->picture ) ){
                                            $poster = $value->picture;
                                        //Otherwise use the small one
                                        } else if( isset( $news->picture ) ) {
                                            $poster = $news->picture;
                                        } else {
                                            $poster = '';
                                        }
                                    }

                                    isset($news->description) ? $description_text = $news->description : $description_text = '';
                                    isset($news->title) ? $video_name = $news->title : $video_name = '';

                                    $poster_alt = $video_name;
                                    if( !empty($video_name) && !empty($description_text) ) $poster_alt .= ' - ';
                                    $poster_alt .= $description_text;

                                    // Certain extensions are incompatible with the columns feature. This
                                    // filter can be used to disable it
                                    isset( $atts['columnscompatible'] ) ? $columns_compatible = $atts['columnscompatible'] : $columns_compatible = true;

                                    if( $cff_show_post ) {

                                        //Fix Photon (Jetpack) issue
                                        $cff_picture_querystring = '';
                                        if (parse_url($poster, PHP_URL_QUERY)){
                                            $picture_url_parts = parse_url($poster);
                                            $cff_picture_querystring = $picture_url_parts['query'];
                                        }

                                        if ($columns_compatible) {
                                            $cff_post_item .= '<div class="cff-album-item cff-col-' . $cff_video_cols . '" id="cff_' . $news->id . '">';
                                            $cff_post_item .= '<a href="http://facebook.com/' . $news->id . '" class="cff-album-cover cff-video" ' . $target . $cff_nofollow . ' id="' . $news->id . '" data-source="' . $news->source . '"><i class="fa fa-play cff-playbtn"></i><img src="' . $poster . '" alt="' . $poster_alt . '" data-querystring="'.$cff_picture_querystring.'" /></a>';
                                        } else {
                                            $cff_post_item .= '<div class="cff-album-item" id="cff_' . $news->id . '">';
                                            $cff_post_item .= '<a href="" class="cff-album-cover cff-video" ' . $target . $cff_nofollow . ' id="' . $news->id . '" data-source="' . $news->source . '"><i class="fa fa-play cff-playbtn"></i><img src="' . $poster . '" alt="' . $poster_alt . '" data-querystring="'.$cff_picture_querystring.'" /></a>';
                                        }
                                    }

                                    if($cff_show_video_name) $cff_post_item .= '<div class="cff-album-info">';
                                        if( $cff_show_video_name && !empty($video_name) ) $cff_post_item .= '<h4><a href="http://facebook.com/' . $news->id . '" '.$target.$cff_nofollow.'>' . $video_name . '</a></h4>';
                                        
                                        if($cff_show_video_desc){
                                            $cff_post_item .= '<p>' . substr($description_text, 0, 50);
                                            if( strlen($description_text) > 50 ) $cff_post_item .= '...';
                                            $cff_post_item .= '</p>';
                                        }

                                    if($cff_show_video_name) $cff_post_item .= '</div>';

                                    $cff_post_item .= '</div>';
                                    $post_time = $i;
                                }
                            }


                            //PHOTOS ONLY
                            if($cff_photos_only && empty($cff_album_id)){

                                //Get the caption
                                if($cff_is_group){
                                    //Still using FQL. Can remove this after August 8th.
                                    !empty($news->caption) ? $cff_caption = htmlspecialchars($news->caption) : $cff_caption = ' ';
                                    $id = $news->pid;
                                    $picture = $news->src_big;
                                } else {
                                    !empty($news->name) ? $cff_caption = htmlspecialchars($news->name) : $cff_caption = ' ';
                                    $id = $news->id;
                                    $picture = $news->picture;
                                }

                                $cff_filter_string = $atts[ 'filter' ];
                                $cff_show_post = true;

                                if ( $cff_filter_string != '' ){
                                    //Explode it into multiples
                                    $cff_filter_strings_array = explode(',', $cff_filter_string);
                                    //Hide the post if both the post text and description don't contain the string
                                    $string_in_post_text = true;
                                    $string_in_desc = true;
                                    if ( cff_stripos_arr($cff_caption, $cff_filter_strings_array) === false ) $cff_show_post = false;
                                }

                                $cff_exclude_string = $atts[ 'exfilter' ];
                                if ( $cff_exclude_string != '' ){
                                    //Explode it into multiples
                                    $cff_exclude_strings_array = explode(',', $cff_exclude_string);
                                    //Hide the post if both the post text and description don't contain the string
                                    $string_in_post_text = false;
                                    $string_in_desc = false;
                                    if ( cff_stripos_arr($cff_caption, $cff_exclude_strings_array) !== false ) $cff_show_post = false;
                                }

                                $cff_post_item = '';

                                // Certain extensions are incompatible with the columns feature. This
                                // filter can be used to disable it
                                isset( $atts['columnscompatible'] ) ? $columns_compatible = $atts['columnscompatible'] : $columns_compatible = true;

                                if( $cff_show_post ){

                                    //Fix Photon (Jetpack) issue
                                    $cff_picture_querystring = '';
                                    if (parse_url($picture, PHP_URL_QUERY)){
                                        $picture_url_parts = parse_url($picture);
                                        $cff_picture_querystring = $picture_url_parts['query'];
                                    }

                                    //Get full size image (if not a group)
                                    if (!empty($picture) && !$cff_is_group) $picture = 'https://graph.facebook.com/'.$id.'/picture?type=normal&width=9999&height=9999';

                                    if( $columns_compatible ) {
                                        $cff_post_item .= '<div class="cff-album-item cff-col-'.$cff_photos_cols.'" id="cff_'. $id .'">';
                                        $cff_post_item .= '<a href="'.$news->link.'" class="cff-album-cover" '.$target.$cff_nofollow.'><img src="'. $picture .'" alt="'.$cff_caption.'" data-querystring="'.$cff_picture_querystring.'" /></a>';
                                        $cff_post_item .= '</div>';
                                    } else {
                                        $cff_post_item .= '<div class="cff-album-item" id="cff_'. $id .'">';
                                        $cff_post_item .= '<a href="'.$news->link.'" class="cff-album-cover" '.$target.$cff_nofollow.'><img src="'. $picture .'" alt="'.$cff_caption.'" data-querystring="'.$cff_picture_querystring.'" /></a>';
                                        $cff_post_item .= '</div>';
                                    }

                                }

                                if($cff_is_group){
                                    //FOR GROUPS
                                    $post_time = $news->created;
                                    $cff_posts_array = cff_array_push_assoc_photos($cff_posts_array, $i, $cff_post_item, $post_time);
                                } else {
                                    //FOR PAGES
                                    if( $i <= $show_posts ) $cff_content .= $cff_post_item;
                                }                        

                            } else {
                                //PUSH POSTS TO ARRAY
                                if(!$cff_is_group){
                                    $cff_posts_array = cff_array_push_assoc($cff_posts_array, $post_time, $cff_post_item);
                                } else {
                                    $cff_posts_array = cff_array_push_assoc($cff_posts_array, $i, $cff_post_item);
                                }
                            }

                        } // End offset
                        
                    } // End post type check

                    if (isset($news->message)) $prev_post_message = $news->message;
                    if (isset($news->link))  $prev_post_link = $news->link;
                    if (isset($news->description))  $prev_post_description = $news->description;
                } // End the loop

            } // End if($fbdata_string) check
            
            if($cff_photos_only && empty($cff_album_id)){
                //PHOTOS ONLY
                usort($cff_posts_array, 'cffSortByOrder');
            } else if( $cff_album_active && !empty($cff_album_id) || $cff_videos_only || $cff_albums_only ) {
                //ALBUM EMBED
                //Don't sort array. Display posts in their native order.
            } else {
                //Sort the array in reverse order (newest first)
                if(!$cff_is_group) krsort($cff_posts_array);
            }

        } // End ALL POSTS

    } // END PAGE_IDS LOOP


    if ($cff_events_only && $cff_events_source == 'eventspage'){
        //EVENTS ONLY OFFSET - Use offset to remove items from the array which shouldn't be shown
        if( !empty($atts['offset']) ) $cff_posts_array = array_slice($cff_posts_array, intval($atts['offset']));

        //If no events then add notice
        if ( empty($cff_posts_array) ) $cff_posts_array = cff_array_push_assoc($cff_posts_array, 1, '<p class="cff-no-events">'.$cff_no_events_text.'</p>');
    }

    //Output the posts array
    if($cff_photos_only && empty($cff_album_id)){
        //PHOTOS ONLY
        $p = 0;
        foreach ($cff_posts_array as $post ) {
            if ( $p == $show_posts ) break;
            $cff_content .= $post['post'];
            $p++;
        }
    } else {
        $p = 0;
        foreach ($cff_posts_array as $post ) {
            if ( $p == $show_posts ) break;
            $cff_content .= $post;
            $p++;
        }
    }

    //Reset the timezone
    date_default_timezone_set( $cff_orig_timezone );
    //Add the Like Box inside
    if ($cff_like_box_position == 'bottom' && $cff_show_like_box && !$cff_like_box_outside) $cff_content .= $like_box;
    /* Credit link */
    $cff_show_credit = $atts['credit'];
    ($cff_show_credit == 'true' || $cff_show_credit == 'on') ? $cff_show_credit = true : $cff_show_credit = false;

    if($cff_show_credit) $cff_content .= '<p class="cff-credit"><a href="https://smashballoon.com/custom-facebook-feed/" target="_blank" style="color: #'.$link_color=$cff_posttext_link_color.'" title="Smash Balloon Custom Facebook Feed WordPress Plugin"><img src="'.plugins_url( '/img/smashballoon-tiny.png' , __FILE__ ).'" />The Custom Facebook Feed plugin</a></p>';
    //End the feed
    $cff_content .= '</div>';
    $cff_content .= '<div class="cff-clear"></div>';
    //Add the Like Box outside
    if ($cff_like_box_position == 'bottom' && $cff_show_like_box && $cff_like_box_outside) $cff_content .= $like_box;

    //If the feed is loaded via Ajax then put the scripts into the shortcode itself
    $ajax_theme = $atts['ajax'];
    ( $ajax_theme == 'on' || $ajax_theme == 'true' || $ajax_theme == true ) ? $ajax_theme = true : $ajax_theme = false;
    if( $atts[ 'ajax' ] == 'false' ) $ajax_theme = false;
    if ($ajax_theme) {
        $url = plugins_url();
        $path = urlencode(ABSPATH);
        $cff_link_hashtags = $atts['linkhashtags'];
        ($cff_link_hashtags == 'true' || $cff_link_hashtags == 'on') ? $cff_link_hashtags = 'true' : $cff_link_hashtags = 'false';
        if($cff_title_link == 'true' || $cff_title_link == 'on') $cff_link_hashtags = 'false';
        $cff_content .= '<script type="text/javascript">var cffsiteurl = "' . $url . '", cfflinkhashtags = "' . $cff_link_hashtags . '";</script>';
        $cff_content .= '<script type="text/javascript" src="' . plugins_url( '/js/cff-scripts.js?ver='.CFFVER , __FILE__ ) . '"></script>';
    }
    $cff_content .= '</div>';

    if( isset( $cff_posttext_link_color ) && !empty( $cff_posttext_link_color ) ) $cff_content .= '<style>#cff .cff-post-text a{ color: #'.$cff_posttext_link_color.'; }</style>';

    //Hook to perform actions before returning $cff_content
    do_action( 'cff_before_return_content', $atts );

    //Return our feed HTML to display
    return $cff_content;
}

function cffSortByOrder($a, $b) {
    return $b['post_time'] - $a['post_time'];
}

//***FUNCTIONS***
function cff_cache_meta() {
    global $wpdb;

    $cff_cache_time = get_option('cff_cache_time');
    $cff_cache_time_unit = get_option('cff_cache_time_unit');

    //Don't allow cache time to be zero - set to 1 minute instead to minimize API requests
    if(!isset($cff_cache_time) || $cff_cache_time == '0'){
        $cff_cache_time = 1;
        $cff_cache_time_unit = 'minutes';
    }
    if($cff_cache_time == 'none') $cff_cache_time = 0;

    //Calculate the cache time in seconds
    if($cff_cache_time_unit == 'minutes') $cff_cache_time_unit = 60;
    if($cff_cache_time_unit == 'hours') $cff_cache_time_unit = 60*60;
    if($cff_cache_time_unit == 'days') $cff_cache_time_unit = 60*60*24;
    $cache_seconds = $cff_cache_time * $cff_cache_time_unit;

    $transient_name = 'cff_meta';
    isset($_POST['count']) ? $meta_data = $_POST['count'] : $meta_data = '';

    set_transient( $transient_name, $meta_data, $cache_seconds );
}
add_action('wp_ajax_cache_meta', 'cff_cache_meta');
add_action('wp_ajax_nopriv_cache_meta', 'cff_cache_meta');


function cff_mb_substr_replace($string, $replacement, $start, $length=NULL) {
    if (is_array($string)) {
        $num = count($string);
        // $replacement
        $replacement = is_array($replacement) ? array_slice($replacement, 0, $num) : array_pad(array($replacement), $num, $replacement);
        // $start
        if (is_array($start)) {
            $start = array_slice($start, 0, $num);
            foreach ($start as $key => $value)
                $start[$key] = is_int($value) ? $value : 0;
        }
        else {
            $start = array_pad(array($start), $num, $start);
        }
        // $length
        if (!isset($length)) {
            $length = array_fill(0, $num, 0);
        }
        elseif (is_array($length)) {
            $length = array_slice($length, 0, $num);
            foreach ($length as $key => $value)
                $length[$key] = isset($value) ? (is_int($value) ? $value : $num) : 0;
        }
        else {
            $length = array_pad(array($length), $num, $length);
        }
        // Recursive call
        return array_map(__FUNCTION__, $string, $replacement, $start, $length);
    }
    preg_match_all('/./us', (string)$string, $smatches);
    preg_match_all('/./us', (string)$replacement, $rmatches);
    if ($length === NULL) $length = mb_strlen($string);
    array_splice($smatches[0], $start, $length, $rmatches[0]);
    return join($smatches[0]);
}

//Get JSON object of feed data
function cff_fetchUrl($url){

    //Style options
    $options = get_option('cff_style_settings');
    isset( $options['cff_request_method'] ) ? $cff_request_method = $options['cff_request_method'] : $cff_request_method = 'auto';

    if($cff_request_method == '1'){
        //Use cURL
        if(is_callable('curl_init')){
            $ch = curl_init();
            // Use global proxy settings
            if (defined('WP_PROXY_HOST')) {
              curl_setopt($ch, CURLOPT_PROXY, WP_PROXY_HOST);
            }
            if (defined('WP_PROXY_PORT')) {
              curl_setopt($ch, CURLOPT_PROXYPORT, WP_PROXY_PORT);
            }
            if (defined('WP_PROXY_USERNAME')){
              $auth = WP_PROXY_USERNAME;
              if (defined('WP_PROXY_PASSWORD')){
                $auth .= ':' . WP_PROXY_PASSWORD;
              }
              curl_setopt($ch, CURLOPT_PROXYUSERPWD, $auth);
            }
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_ENCODING, '');
            $feedData = curl_exec($ch);
            curl_close($ch);
        }
    } else if($cff_request_method == '2') {
        //Use file_get_contents
        if ( (ini_get('allow_url_fopen') == 1 || ini_get('allow_url_fopen') === TRUE ) && in_array('https', stream_get_wrappers()) ){
            $feedData = @file_get_contents($url);
        }
    } else if($cff_request_method == '3'){
        //Use the WP HTTP API
        $request = new WP_Http;
        $response = $request->request($url, array('timeout' => 60, 'sslverify' => false));
        if( is_wp_error( $response ) ) {
            //Don't display an error, just use the Server config Error Reference message
            $FBdata = null;
        } else {
            $feedData = wp_remote_retrieve_body($response);
        }
    } else {
        //Auto detect
        if(is_callable('curl_init')){
            $ch = curl_init();
            // Use global proxy settings
            if (defined('WP_PROXY_HOST')) {
              curl_setopt($ch, CURLOPT_PROXY, WP_PROXY_HOST);
            }
            if (defined('WP_PROXY_PORT')) {
              curl_setopt($ch, CURLOPT_PROXYPORT, WP_PROXY_PORT);
            }
            if (defined('WP_PROXY_USERNAME')){
              $auth = WP_PROXY_USERNAME;
              if (defined('WP_PROXY_PASSWORD')){
                $auth .= ':' . WP_PROXY_PASSWORD;
              }
              curl_setopt($ch, CURLOPT_PROXYUSERPWD, $auth);
            }
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_ENCODING, '');
            $feedData = curl_exec($ch);
            curl_close($ch);
        } elseif ( (ini_get('allow_url_fopen') == 1 || ini_get('allow_url_fopen') === TRUE ) && in_array('https', stream_get_wrappers()) ) {
            $feedData = @file_get_contents($url);
        } else {
            $request = new WP_Http;
            $response = $request->request($url, array('timeout' => 60, 'sslverify' => false));
            if( is_wp_error( $response ) ) {
                $FBdata = null;
            } else {
                $feedData = wp_remote_retrieve_body($response);
            }
        }
    }
    
    return $feedData;
}

//Make links into span instead when the post text is made clickable
function cff_wrap_span($text) {
    $pattern  = '#\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#';
    return preg_replace_callback($pattern, 'cff_wrap_span_callback', $text);
}
function cff_wrap_span_callback($matches) {
    $max_url_length = 50;
    $max_depth_if_over_length = 2;
    $ellipsis = '&hellip;';
    $target = 'target="_blank"';
    $url_full = $matches[0];
    $url_short = '';
    if (strlen($url_full) > $max_url_length) {
        $parts = parse_url($url_full);
        if(isset($parts['scheme']) && isset($parts['host'])) $url_short = $parts['scheme'] . '://' . preg_replace('/^www\./', '', $parts['host']) . '/';
        $path_components = explode('/', trim($parts['path'], '/'));
        foreach ($path_components as $dir) {
            $url_string_components[] = $dir . '/';
        }
        if (!empty($parts['query'])) {
            $url_string_components[] = '?' . $parts['query'];
        }
        if (!empty($parts['fragment'])) {
            $url_string_components[] = '#' . $parts['fragment'];
        }
        for ($k = 0; $k < count($url_string_components); $k++) {
            $curr_component = $url_string_components[$k];
            if ($k >= $max_depth_if_over_length || strlen($url_short) + strlen($curr_component) > $max_url_length) {
                if ($k == 0 && strlen($url_short) < $max_url_length) {
                    // Always show a portion of first directory
                    $url_short .= substr($curr_component, 0, $max_url_length - strlen($url_short));
                }
                $url_short .= $ellipsis;
                break;
            }
            $url_short .= $curr_component;
        }
    } else {
        $url_short = $url_full;
    }
    return "<span class='cff-break-word'>$url_short</span>";
}

//2013-04-28T21:06:56+0000
//Time stamp function - used for posts
function cff_getdate($original, $date_format, $custom_date, $cff_date_translate_strings) {

    switch ($date_format) {
        
        case '2':
            $print = date_i18n('F jS, g:i a', $original);
            break;
        case '3':
            $print = date_i18n('F jS', $original);
            break;
        case '4':
            $print = date_i18n('D F jS', $original);
            break;
        case '5':
            $print = date_i18n('l F jS', $original);
            break;
        case '6':
            $print = date_i18n('D M jS, Y', $original);
            break;
        case '7':
            $print = date_i18n('l F jS, Y', $original);
            break;
        case '8':
            $print = date_i18n('l F jS, Y - g:i a', $original);
            break;
        case '9':
            $print = date_i18n("l M jS, 'y", $original);
            break;
        case '10':
            $print = date_i18n('m.d.y', $original);
            break;
        case '11':
            $print = date_i18n('m/d/y', $original);
            break;
        case '12':
            $print = date_i18n('d.m.y', $original);
            break;
        case '13':
            $print = date_i18n('d/m/y', $original);
            break;
        default:

            $cff_second = $cff_date_translate_strings['cff_translate_second'];
            $cff_seconds = $cff_date_translate_strings['cff_translate_seconds'];
            $cff_minute = $cff_date_translate_strings['cff_translate_minute'];
            $cff_minutes = $cff_date_translate_strings['cff_translate_minutes'];
            $cff_hour = $cff_date_translate_strings['cff_translate_hour'];
            $cff_hours = $cff_date_translate_strings['cff_translate_hours'];
            $cff_day = $cff_date_translate_strings['cff_translate_day'];
            $cff_days = $cff_date_translate_strings['cff_translate_days'];
            $cff_week = $cff_date_translate_strings['cff_translate_week'];
            $cff_weeks = $cff_date_translate_strings['cff_translate_weeks'];
            $cff_month = $cff_date_translate_strings['cff_translate_month'];
            $cff_months = $cff_date_translate_strings['cff_translate_months'];
            $cff_year = $cff_date_translate_strings['cff_translate_years'];
            $cff_years = $cff_date_translate_strings['cff_translate_years'];
            $cff_ago = $cff_date_translate_strings['cff_translate_ago'];

            
            $periods = array($cff_second, $cff_minute, $cff_hour, $cff_day, $cff_week, $cff_month, $cff_year, "decade");
            $periods_plural = array($cff_seconds, $cff_minutes, $cff_hours, $cff_days, $cff_weeks, $cff_months, $cff_years, "decade");

            $lengths = array("60","60","24","7","4.35","12","10");
            $now = time();
            
            // is it future date or past date
            if($now > $original) {    
                $difference = $now - $original;
                $tense = $cff_ago;
            } else {
                $difference = $original - $now;
                $tense = $cff_ago;
            }
            for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
                $difference /= $lengths[$j];
            }
            
            $difference = round($difference);
            
            if($difference != 1) {
                $periods[$j] = $periods_plural[$j];
            }
            $print = "$difference $periods[$j] {$tense}";
            break;
        
    }
    if ( !empty($custom_date) ){
        $print = date_i18n($custom_date, $original);
    }
    return $print;
}
function cff_eventdate($original, $date_format, $custom_date) {
    switch ($date_format) {
        
        case '2':
            $print = date_i18n('<k>F jS, </k>g:ia', $original);
            break;
        case '3':
            $print = date_i18n('g:ia<k> - F jS</k>', $original);
            break;
        case '4':
            $print = date_i18n('g:ia<k>, F jS</k>', $original);
            break;
        case '5':
            $print = date_i18n('<k>l F jS - </k> g:ia', $original);
            break;
        case '6':
            $print = date_i18n('<k>D M jS, Y, </k>g:iA', $original);
            break;
        case '7':
            $print = date_i18n('<k>l F jS, Y, </k>g:iA', $original);
            break;
        case '8':
            $print = date_i18n('<k>l F jS, Y - </k>g:ia', $original);
            break;
        case '9':
            $print = date_i18n("<k>l M jS, 'y</k>", $original);
            break;
        case '10':
            $print = date_i18n('<k>m.d.y - </k>g:iA', $original);
            break;
        case '11':
            $print = date_i18n('<k>m/d/y, </k>g:ia', $original);
            break;
        case '12':
            $print = date_i18n('<k>d.m.y - </k>g:iA', $original);
            break;
        case '13':
            $print = date_i18n('<k>d/m/y, </k>g:ia', $original);
            break;
        case '14':
            $print = date_i18n('<k>M j, </k>g:ia', $original);
            break;
        case '15':
            $print = date_i18n('<k>M j, </k>G:i', $original);
            break;
        default:
            $print = date_i18n('<k>F j, Y, </k>g:ia', $original);
            break;
    }
    if ( !empty($custom_date) ){
        $print = date_i18n($custom_date, $original);
    }


    return $print;
}
//Time stamp function - used for comments
function cff_timesince($original, $cff_date_translate_strings) {

    $cff_second = $cff_date_translate_strings['cff_translate_second'];
    $cff_seconds = $cff_date_translate_strings['cff_translate_seconds'];
    $cff_minute = $cff_date_translate_strings['cff_translate_minute'];
    $cff_minutes = $cff_date_translate_strings['cff_translate_minutes'];
    $cff_hour = $cff_date_translate_strings['cff_translate_hour'];
    $cff_hours = $cff_date_translate_strings['cff_translate_hours'];
    $cff_day = $cff_date_translate_strings['cff_translate_day'];
    $cff_days = $cff_date_translate_strings['cff_translate_days'];
    $cff_week = $cff_date_translate_strings['cff_translate_week'];
    $cff_weeks = $cff_date_translate_strings['cff_translate_weeks'];
    $cff_month = $cff_date_translate_strings['cff_translate_month'];
    $cff_months = $cff_date_translate_strings['cff_translate_months'];
    $cff_year = $cff_date_translate_strings['cff_translate_years'];
    $cff_years = $cff_date_translate_strings['cff_translate_years'];
    $cff_ago = $cff_date_translate_strings['cff_translate_ago'];

    
    $periods = array($cff_second, $cff_minute, $cff_hour, $cff_day, $cff_week, $cff_month, $cff_year, "decade");
    $periods_plural = array($cff_seconds, $cff_minutes, $cff_hours, $cff_days, $cff_weeks, $cff_months, $cff_years, "decade");

    $lengths = array("60","60","24","7","4.35","12","10");
    $now = time();
    
    // is it future date or past date
    if($now > $original) {    
        $difference = $now - $original;
        $tense = $cff_ago;;
    } else {
        $difference = $original - $now;
        $tense = $cff_ago;
    }
    for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
        $difference /= $lengths[$j];
    }
    
    $difference = round($difference);
    
    if($difference != 1) {
        $periods[$j] = $periods_plural[$j];
    }
    return "$difference $periods[$j] {$tense}";
            
}
//Use custom stripos function if it's not available (only available in PHP 5+)
if(!is_callable('stripos')){
    function stripos($haystack, $needle){
        return strpos($haystack, stristr( $haystack, $needle ));
    }
}
function cff_stripos_arr($haystack, $needle) {
    if(!is_array($needle)) $needle = array($needle);
    foreach($needle as $what) {
        if(($pos = stripos($haystack, ltrim($what) ))!==false) return $pos;
    }
    return false;
}

//Push to assoc array
function cff_array_push_assoc($array, $key, $value){
    $array[$key] = $value;
    return $array;
}
//Push to assoc array
function cff_array_push_assoc_photos($array, $key, $value, $post_time){
    $array[$key]['post'] = $value;
    $array[$key]['post_time'] = $post_time;

    return $array;
}
//Convert string to slug
function cff_to_slug($string){
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
}


//Enqueue stylesheet
add_action( 'wp_enqueue_scripts', 'cff_add_my_stylesheet' );
function cff_add_my_stylesheet() {
    // Respects SSL, Style.css is relative to the current file
    wp_register_style( 'cff', plugins_url('css/cff-style.css', __FILE__), array(), CFFVER );
    wp_enqueue_style( 'cff' );

    $options = get_option('cff_style_settings');
    
    if( !isset( $options[ 'cff_font_source' ] ) ){
        wp_enqueue_style( 'cff-font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css', array(), '4.5.0' );
    } else {

        if( $options[ 'cff_font_source' ] == 'none' ){
            //Do nothing
        } else if( $options[ 'cff_font_source' ] == 'local' ){
            wp_enqueue_style( 'cff-font-awesome', plugins_url('css/font-awesome.min.css', __FILE__), array(), '4.5.0' );
        } else {
            wp_enqueue_style( 'cff-font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css', array(), '4.5.0' );
        }

    }

}
//Enqueue scripts
add_action( 'wp_enqueue_scripts', 'cff_scripts_method' );
function cff_scripts_method() {
    //Register the script to make it available
    wp_register_script( 'cffscripts', plugins_url( '/js/cff-scripts.js' , __FILE__ ), array('jquery'), CFFVER, true );

    //Enqueue it to load it onto the page
    wp_enqueue_script('cffscripts');
}

//Allows shortcodes in theme
add_filter('widget_text', 'do_shortcode');

add_action( 'wp_head', 'cff_custom_css' );
function cff_custom_css() {
    $options = get_option('cff_style_settings');
    isset($options[ 'cff_custom_css' ]) ? $cff_custom_css = $options[ 'cff_custom_css' ] : $cff_custom_css = '';
    if( !empty($cff_custom_css) ) echo '<!-- Custom Facebook Feed Custom CSS -->';
    if( !empty($cff_custom_css) ) echo "\r\n";
    if( !empty($cff_custom_css) ) echo '<style type="text/css">';
    if( !empty($cff_custom_css) ) echo "\r\n";
    if( !empty($cff_custom_css) ) echo stripslashes($cff_custom_css);
    if( !empty($cff_custom_css) ) echo "\r\n";
    if( !empty($cff_custom_css) ) echo '</style>';
    if( !empty($cff_custom_css) ) echo "\r\n";

    //Link hashtags?
    isset($options[ 'cff_link_hashtags' ]) ? $cff_link_hashtags = $options[ 'cff_link_hashtags' ] : $cff_link_hashtags = '';
    isset($options[ 'cff_title_link' ]) ? $cff_title_link = $options[ 'cff_title_link' ] : $cff_title_link = '';
    ($cff_link_hashtags == 'true' || $cff_link_hashtags == 'on') ? $cff_link_hashtags = 'true' : $cff_link_hashtags = 'false';
    if($cff_title_link == 'true' || $cff_title_link == 'on') $cff_link_hashtags = 'false';

    //Ajax caching?
    //Does the transient exist?
    ( false === ( $cff_cached_meta = get_transient( 'cff_meta' ) ) ) ? $cff_cached_meta = true : $cff_cached_meta = false;
    //Is the user disabling ajax caching?
    isset($options[ 'cff_disable_ajax_cache' ]) ? $cff_disable_ajax_cache = $options[ 'cff_disable_ajax_cache' ] : $cff_disable_ajax_cache = '';
    if( $cff_disable_ajax_cache ) $cff_cached_meta = false;

    echo '<!-- Custom Facebook Feed JS vars -->';
    echo "\r\n";
    echo '<script type="text/javascript">';
    echo "\r\n";
    echo 'var cffsiteurl = "' . plugins_url() . '";';
    echo "\r\n";
    echo 'var cffajaxurl = "' . admin_url('admin-ajax.php') . '";';
    echo "\r\n";
    echo ( $cff_cached_meta ) ? 'var cffmetatrans = "false";' : 'var cffmetatrans = "true";';
    echo "\r\n";
    echo 'var cfflinkhashtags = "' . $cff_link_hashtags . '";';
    echo "\r\n";
    echo '</script>';
    echo "\r\n";
}

add_action( 'wp_footer', 'cff_js' );
function cff_js() {
    $options = get_option('cff_style_settings');
    isset($options[ 'cff_custom_js' ]) ? $cff_custom_js = $options[ 'cff_custom_js' ] : $cff_custom_js = '';

    if( !empty($cff_custom_js) ) echo '<!-- Custom Facebook Feed JS -->';
    if( !empty($cff_custom_js) ) echo "\r\n";
    if( !empty($cff_custom_js) ) echo '<script type="text/javascript">';
    if( !empty($cff_custom_js) ) echo "\r\n";
    if( !empty($cff_custom_js) ) echo "jQuery( document ).ready(function($) {";
    if( !empty($cff_custom_js) ) echo "\r\n";
    if( !empty($cff_custom_js) ) echo stripslashes($cff_custom_js);
    if( !empty($cff_custom_js) ) echo "\r\n";
    if( !empty($cff_custom_js) ) echo "});";
    if( !empty($cff_custom_js) ) echo "\r\n";
    if( !empty($cff_custom_js) ) echo '</script>';
    if( !empty($cff_custom_js) ) echo "\r\n";
}




add_action('init', 'cff_group_photos_notice_dismiss');
function cff_group_photos_notice_dismiss() {
    global $current_user;
        $user_id = $current_user->ID;
        if ( isset($_GET['cff_group_photos_notice_dismiss']) && '0' == $_GET['cff_group_photos_notice_dismiss'] ) {
             add_user_meta($user_id, 'cff_group_photos_notice_dismiss', 'true', true);
    }
}


?>