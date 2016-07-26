var cff_js_exists = (typeof cff_js_exists !== 'undefined') ? true : false;
if(!cff_js_exists){

	(function($){ 

		//Toggle comments
		jQuery(document).on('click', '#cff a.cff-view-comments', function(){
			var $commentsBox = jQuery(this).closest('.cff-item').find('.cff-comments-box');
			
			$commentsBox.slideToggle();

			//Add comment avatars
			$commentsBox.find('.cff-comment:visible').each(function(){
				var $thisComment = jQuery(this);
				$thisComment.find('.cff-comment-img').last().find('img').attr('src', 'https://graph.facebook.com/'+$thisComment.attr("data-id")+'/picture');
			});

		});

		//Set paths for query.php
		if (typeof cffsiteurl === 'undefined' || cffsiteurl == '') cffsiteurl = window.location.host + '/wp-content/plugins';
		var locatefile = true,
			url = cffsiteurl + "/custom-facebook-feed-pro/query.php";

		//Create meta data array for caching likes and comments
		metaArr = {};

		//Loop through the feeds on the page and add a unique attribute to each to use for lightbox groups
		var lb = 0;
		jQuery('#cff.cff-lb').each(function(){
			lb++;
			$(this).attr('data-cff-lb', lb);
		});
		
		//Loop through each item
		jQuery('#cff .cff-item, #cff .cff-album-item').each(function(){

			var $self = jQuery(this);

			//Wpautop fix
			if( $self.find('.cff-viewpost-link, .cff-viewpost-facebook, .cff-viewpost').parent('p').length ){
				//Don't unwrap event only viewpost link
				if( !$self.hasClass('event') ) $self.find('.cff-viewpost-link, .cff-viewpost-facebook, .cff-viewpost').unwrap('p');
			}
			if( $self.find('.cff-photo').parent('p').length ){
				$self.find('p .cff-photo').unwrap('p');
				$self.find('.cff-album-icon').appendTo('.cff-photo:last');
			}
			if( $self.find('.cff-event-thumb').parent('p').length ){
				$self.find('.cff-event-thumb').unwrap('p');
			}
			if( $self.find('.cff-vidLink').parent('p').length ){
				$self.find('.cff-vidLink').unwrap('p');
			}
			if( $self.find('.cff-link').parent('p').length ){
				$self.find('.cff-link').unwrap('p');
			}
			if( $self.find('.cff-viewpost-link').parent('p').length ){
				$self.find('.cff-viewpost-link').unwrap('p');
			}
			if( $self.find('.cff-viewpost-facebook').parent('p').length ){
				$self.find('.cff-viewpost-facebook').unwrap('p');
			}

			if( $self.find('iframe').parent('p').length ){
				$self.find('iframe').unwrap('p');
			}
			if( $self.find('.cff-author').parent('p').length ){
				$self.find('.cff-author').eq(1).unwrap('p');
				$self.find('.cff-author').eq(1).remove();
			}
			if( $self.find('.cff-view-comments').parent('p').length ){
				$self.find('.cff-meta-wrap > p').remove();
				$self.find('.cff-view-comments').eq(1).remove();
				//Move meta ul inside the link element
				var $cffMeta = $self.find('.cff-meta'),
					cffMetaClasses = $cffMeta.attr('class');
				$cffMeta.find('.cff-view-comments').unwrap().wrapInner('<ul class="'+cffMetaClasses+'">');
			}
			if( $self.find('.cff-photo').siblings('.cff-photo').length ){
				$self.find('.cff-photo').slice(0,2).remove();
			}

			//Expand post
			var	expanded = false;
			if( $self.hasClass('cff-event') ){
				var $post_text = $self.find('.cff-desc .cff-desc-text'),
					text_limit = $post_text.parent().attr('data-char');
			} else {
				var $post_text = $self.find('.cff-post-text .cff-text'),
					text_limit = $self.closest('#cff').attr('data-char');
			}

			if (typeof text_limit === 'undefined' || text_limit == '') text_limit = 99999;
			
			//If the text is linked then use the text within the link
			if ( $post_text.find('a.cff-post-text-link').length ) $post_text = $self.find('.cff-post-text .cff-text a');
			var	full_text = $post_text.html();
			if(full_text == undefined) full_text = '';
			var short_text = full_text.substring(0,text_limit);

			//If the short text cuts off in the middle of a <br> tag then remove the stray '<' which is displayed
			var lastChar = short_text.substr(short_text.length - 1);
			if(lastChar == '<') short_text = short_text.substring(0, short_text.length - 1);

			//Cut the text based on limits set
			$post_text.html( short_text );
			//Show the 'See More' link if needed
			if (full_text.length > text_limit) $self.find('.cff-expand').show();
			//Click function
			$self.find('.cff-expand a').unbind('click').bind('click', function(e){
				e.preventDefault();
				var $expand = jQuery(this),
					$more = $expand.find('.cff-more'),
					$less = $expand.find('.cff-less');
				if (expanded == false){
					$post_text.html( full_text );
					expanded = true;
					$more.hide();
					$less.show();
				} else {
					$post_text.html( short_text );
					expanded = false;
					$more.show();
					$less.hide();			
				}
				cffLinkHashtags();
				//Add target to links in text when expanded
				$post_text.find('a').attr('target', '_blank');
			});
			//Add target attr to post text links via JS so aren't included in char count
			$post_text.find('a').add( $self.find('.cff-post-desc a') ).attr({
				'target' : '_blank',
				'rel' : 'nofollow'
			});


			//AJAX
			//Set the path to query.php
			//This is the modified Post ID - so if the post is an album post then this could be the album ID which is used to get the lightbox thumbs
			var post_id = $self.attr('id').substring(4),
				//This is the original post ID which is used to get the number of likes and comments for the timeline post
				post_id_orig = $self.find('.cff-view-comments').attr('id'),
				url = cffsiteurl + "/custom-facebook-feed-pro/query.php?id=" + post_id_orig;
				
			//If the file can be found then load in likes and comments
			if (locatefile == true){
				var $likesCountSpan = $self.find('.cff-likes .cff-count'),
					$commentsCountSpan = $self.find('.cff-comments .cff-count');

				//If the likes or comment counts are above 25 then replace them with the query.php values
				if( $likesCountSpan.find('.cff-replace').length ) $likesCountSpan.load(url + '&type=likes body', function(response){

					//If a number is not returned then display 25+
					if( isNaN(response) ){
						$likesCountSpan.html('25+');
					} else {
						//Display the count number
						$likesCountSpan.html(response);
						//Add to cache array
						metaArr[ post_id_orig + '_likes' ] = response;

						cffCacheMeta(metaArr);
					}

					//__, __ and 2 others like this
					var $likesCount = $self.find('.cff-comment-likes .cff-comment-likes-count');
					if( $likesCount.length ) {
						if( isNaN(response) ){
							//If the count is returned as 25+ from query.php then change to 23+ to account for -2
							$likesCount.text( '23+' );
						} else {
							$likesCount.text( response -2 );
						}
					}
				});

				if( $commentsCountSpan.find('.cff-replace').length ) $commentsCountSpan.load(url + '&type=comments body', function(response){
					//If a number is not returned then display 25+
					if( isNaN(response) ){
						$commentsCountSpan.html('25+');
					} else {
						//Display the count number
						$commentsCountSpan.html(response);
						//Add to cache array
						metaArr[ post_id_orig + '_comments' ] = response;

						cffCacheMeta(metaArr);
					}
				});


			} else {
				$self.find('.cff-replace').show();
				$self.find('.cff-loader').hide();
				$self.find('.cff-lightbox-thumbs-holder').css('min-height', 0);
			}


			//Only show 4 latest comments
			var $showMoreComments = $self.find('.cff-show-more-comments'),
				$comment = $self.find('.cff-comment');

			if ( $showMoreComments.length ) {
				$comment.hide();
				var commentCount = $comment.length,
					commentShow = parseInt( $self.find('.cff-comments-box').attr('data-num') );

				//Show latest few comments based on the number set by the user (data-num on the comments box)
				$comment.slice(commentCount - commentShow).show();
				//Show all on click
				jQuery(document).on('click', '.cff-show-more-comments', function(){
					//Hide 'Show previous comments' link
					jQuery(this).hide();

					//Show comments and add comment avatars
					jQuery(this).siblings('.cff-comment').show().each(function(){
						var $thisComment = jQuery(this);
						$thisComment.find('.cff-comment-img img').attr('src', 'https://graph.facebook.com/'+$thisComment.attr("data-id")+'/picture');
					});

				});
			}


			//Remove event end date day if the same as the start date
			if( $self.hasClass('cff-timeline-event') || $self.hasClass('cff-event') ){
				if( $(this).find('.cff-date .cff-start-date k').text() !== $(this).find('.cff-date .cff-end-date k').text() ) $(this).find('.cff-date .cff-end-date k').show();
			}


			//Replace Photon (Jetpack CDN) images with the originals again
			var $cffPhotoImg = $self.find('.cff-photo img, .cff-event-thumb img, .cff-poster, .cff-album-cover img'),
				cffPhotoImgSrc = $cffPhotoImg.attr('src'),
				cffImgStringAttr = $cffPhotoImg.attr('data-querystring');

			if( typeof cffPhotoImgSrc == 'undefined' ) cffPhotoImgSrc = '';

			if( cffPhotoImgSrc.indexOf('i0.wp.com') > -1 || cffPhotoImgSrc.indexOf('i1.wp.com') > -1 || cffPhotoImgSrc.indexOf('i2.wp.com') > -1 || cffPhotoImgSrc.indexOf('i3.wp.com') > -1 || cffPhotoImgSrc.indexOf('i4.wp.com') > -1 || cffPhotoImgSrc.indexOf('i5.wp.com') > -1 ){
				
				//Create new src. Single slash in https is intentional as one is left over from removing i_.wp.com
				var photonSrc = $cffPhotoImg.attr('src').substring(0, $cffPhotoImg.attr('src').indexOf('?')),
					newSrc = photonSrc.replace('http://', 'https:/').replace(/i0.wp.com|i1.wp.com|i2.wp.com|i3.wp.com|i4.wp.com|i5.wp.com/gi, '') + '?' + cffImgStringAttr;

				$cffPhotoImg.attr('src', newSrc);

				// if( $cffPhotoImg.hasClass('cff-poster') ) $self.find('.cff-lightbox-link').attr('href', );
			}


			//I don't need this any more as a picture isn't included in the API anymore if there isn't one for the link:
			//If a shared link image is 1x1 (after it's loaded) then hide it and add class (as php check for 1x1 doesn't always work)
			// $self.find('.cff-link img').each(function() {
			// 	var $cffSharedLink = $self.find('.cff-link');
			// 	if( $cffSharedLink.find('img').width() < 10 ) {
			// 		$cffSharedLink.hide().siblings('.cff-text-link').addClass('cff-no-image');
			// 	}
			// });

			function cffLinkHashtags(){
				//Link hashtags
				var cffTextStr = $self.find('.cff-text').html(),
					cffDescStr = $self.find('.cff-post-desc').html(),
					regex = /(^|\s)#(\w*[\u0041-\u005A\u0061-\u007A\u00AA\u00B5\u00BA\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u02C1\u02C6-\u02D1\u02E0-\u02E4\u02EC\u02EE\u0370-\u0374\u0376\u0377\u037A-\u037D\u0386\u0388-\u038A\u038C\u038E-\u03A1\u03A3-\u03F5\u03F7-\u0481\u048A-\u0527\u0531-\u0556\u0559\u0561-\u0587\u05D0-\u05EA\u05F0-\u05F2\u0620-\u064A\u066E\u066F\u0671-\u06D3\u06D5\u06E5\u06E6\u06EE\u06EF\u06FA-\u06FC\u06FF\u0710\u0712-\u072F\u074D-\u07A5\u07B1\u07CA-\u07EA\u07F4\u07F5\u07FA\u0800-\u0815\u081A\u0824\u0828\u0840-\u0858\u08A0\u08A2-\u08AC\u0904-\u0939\u093D\u0950\u0958-\u0961\u0971-\u0977\u0979-\u097F\u0985-\u098C\u098F\u0990\u0993-\u09A8\u09AA-\u09B0\u09B2\u09B6-\u09B9\u09BD\u09CE\u09DC\u09DD\u09DF-\u09E1\u09F0\u09F1\u0A05-\u0A0A\u0A0F\u0A10\u0A13-\u0A28\u0A2A-\u0A30\u0A32\u0A33\u0A35\u0A36\u0A38\u0A39\u0A59-\u0A5C\u0A5E\u0A72-\u0A74\u0A85-\u0A8D\u0A8F-\u0A91\u0A93-\u0AA8\u0AAA-\u0AB0\u0AB2\u0AB3\u0AB5-\u0AB9\u0ABD\u0AD0\u0AE0\u0AE1\u0B05-\u0B0C\u0B0F\u0B10\u0B13-\u0B28\u0B2A-\u0B30\u0B32\u0B33\u0B35-\u0B39\u0B3D\u0B5C\u0B5D\u0B5F-\u0B61\u0B71\u0B83\u0B85-\u0B8A\u0B8E-\u0B90\u0B92-\u0B95\u0B99\u0B9A\u0B9C\u0B9E\u0B9F\u0BA3\u0BA4\u0BA8-\u0BAA\u0BAE-\u0BB9\u0BD0\u0C05-\u0C0C\u0C0E-\u0C10\u0C12-\u0C28\u0C2A-\u0C33\u0C35-\u0C39\u0C3D\u0C58\u0C59\u0C60\u0C61\u0C85-\u0C8C\u0C8E-\u0C90\u0C92-\u0CA8\u0CAA-\u0CB3\u0CB5-\u0CB9\u0CBD\u0CDE\u0CE0\u0CE1\u0CF1\u0CF2\u0D05-\u0D0C\u0D0E-\u0D10\u0D12-\u0D3A\u0D3D\u0D4E\u0D60\u0D61\u0D7A-\u0D7F\u0D85-\u0D96\u0D9A-\u0DB1\u0DB3-\u0DBB\u0DBD\u0DC0-\u0DC6\u0E01-\u0E30\u0E32\u0E33\u0E40-\u0E46\u0E81\u0E82\u0E84\u0E87\u0E88\u0E8A\u0E8D\u0E94-\u0E97\u0E99-\u0E9F\u0EA1-\u0EA3\u0EA5\u0EA7\u0EAA\u0EAB\u0EAD-\u0EB0\u0EB2\u0EB3\u0EBD\u0EC0-\u0EC4\u0EC6\u0EDC-\u0EDF\u0F00\u0F40-\u0F47\u0F49-\u0F6C\u0F88-\u0F8C\u1000-\u102A\u103F\u1050-\u1055\u105A-\u105D\u1061\u1065\u1066\u106E-\u1070\u1075-\u1081\u108E\u10A0-\u10C5\u10C7\u10CD\u10D0-\u10FA\u10FC-\u1248\u124A-\u124D\u1250-\u1256\u1258\u125A-\u125D\u1260-\u1288\u128A-\u128D\u1290-\u12B0\u12B2-\u12B5\u12B8-\u12BE\u12C0\u12C2-\u12C5\u12C8-\u12D6\u12D8-\u1310\u1312-\u1315\u1318-\u135A\u1380-\u138F\u13A0-\u13F4\u1401-\u166C\u166F-\u167F\u1681-\u169A\u16A0-\u16EA\u1700-\u170C\u170E-\u1711\u1720-\u1731\u1740-\u1751\u1760-\u176C\u176E-\u1770\u1780-\u17B3\u17D7\u17DC\u1820-\u1877\u1880-\u18A8\u18AA\u18B0-\u18F5\u1900-\u191C\u1950-\u196D\u1970-\u1974\u1980-\u19AB\u19C1-\u19C7\u1A00-\u1A16\u1A20-\u1A54\u1AA7\u1B05-\u1B33\u1B45-\u1B4B\u1B83-\u1BA0\u1BAE\u1BAF\u1BBA-\u1BE5\u1C00-\u1C23\u1C4D-\u1C4F\u1C5A-\u1C7D\u1CE9-\u1CEC\u1CEE-\u1CF1\u1CF5\u1CF6\u1D00-\u1DBF\u1E00-\u1F15\u1F18-\u1F1D\u1F20-\u1F45\u1F48-\u1F4D\u1F50-\u1F57\u1F59\u1F5B\u1F5D\u1F5F-\u1F7D\u1F80-\u1FB4\u1FB6-\u1FBC\u1FBE\u1FC2-\u1FC4\u1FC6-\u1FCC\u1FD0-\u1FD3\u1FD6-\u1FDB\u1FE0-\u1FEC\u1FF2-\u1FF4\u1FF6-\u1FFC\u2071\u207F\u2090-\u209C\u2102\u2107\u210A-\u2113\u2115\u2119-\u211D\u2124\u2126\u2128\u212A-\u212D\u212F-\u2139\u213C-\u213F\u2145-\u2149\u214E\u2183\u2184\u2C00-\u2C2E\u2C30-\u2C5E\u2C60-\u2CE4\u2CEB-\u2CEE\u2CF2\u2CF3\u2D00-\u2D25\u2D27\u2D2D\u2D30-\u2D67\u2D6F\u2D80-\u2D96\u2DA0-\u2DA6\u2DA8-\u2DAE\u2DB0-\u2DB6\u2DB8-\u2DBE\u2DC0-\u2DC6\u2DC8-\u2DCE\u2DD0-\u2DD6\u2DD8-\u2DDE\u2E2F\u3005\u3006\u3031-\u3035\u303B\u303C\u3041-\u3096\u309D-\u309F\u30A1-\u30FA\u30FC-\u30FF\u3105-\u312D\u3131-\u318E\u31A0-\u31BA\u31F0-\u31FF\u3400-\u4DB5\u4E00-\u9FCC\uA000-\uA48C\uA4D0-\uA4FD\uA500-\uA60C\uA610-\uA61F\uA62A\uA62B\uA640-\uA66E\uA67F-\uA697\uA6A0-\uA6E5\uA717-\uA71F\uA722-\uA788\uA78B-\uA78E\uA790-\uA793\uA7A0-\uA7AA\uA7F8-\uA801\uA803-\uA805\uA807-\uA80A\uA80C-\uA822\uA840-\uA873\uA882-\uA8B3\uA8F2-\uA8F7\uA8FB\uA90A-\uA925\uA930-\uA946\uA960-\uA97C\uA984-\uA9B2\uA9CF\uAA00-\uAA28\uAA40-\uAA42\uAA44-\uAA4B\uAA60-\uAA76\uAA7A\uAA80-\uAAAF\uAAB1\uAAB5\uAAB6\uAAB9-\uAABD\uAAC0\uAAC2\uAADB-\uAADD\uAAE0-\uAAEA\uAAF2-\uAAF4\uAB01-\uAB06\uAB09-\uAB0E\uAB11-\uAB16\uAB20-\uAB26\uAB28-\uAB2E\uABC0-\uABE2\uAC00-\uD7A3\uD7B0-\uD7C6\uD7CB-\uD7FB\uF900-\uFA6D\uFA70-\uFAD9\uFB00-\uFB06\uFB13-\uFB17\uFB1D\uFB1F-\uFB28\uFB2A-\uFB36\uFB38-\uFB3C\uFB3E\uFB40\uFB41\uFB43\uFB44\uFB46-\uFBB1\uFBD3-\uFD3D\uFD50-\uFD8F\uFD92-\uFDC7\uFDF0-\uFDFB\uFE70-\uFE74\uFE76-\uFEFC\uFF21-\uFF3A\uFF41-\uFF5A\uFF66-\uFFBE\uFFC2-\uFFC7\uFFCA-\uFFCF\uFFD2-\uFFD7\uFFDA-\uFFDC]+\w*)/gi,

					linkcolor = $self.find('.cff-text').attr('data-color');

				function replacer(hash){
					//Remove white space at beginning of hash
					var replacementString = jQuery.trim(hash);
					//If the hash is a hex code then don't replace it with a link as it's likely in the style attr, eg: "color: #ff0000"
					if ( /^#[0-9A-F]{6}$/i.test( replacementString ) ){
						return replacementString;
					} else {
						return ' <a href="https://www.facebook.com/hashtag/'+ replacementString.substring(1) +'" target="_blank" rel="nofollow" style="color:#' + linkcolor + '">' + replacementString + '</a>';
					}
				}

				if(cfflinkhashtags == 'true'){
					//Replace hashtags in text
					var $cffText = $self.find('.cff-text');
					
					if($cffText.length > 0){
						//Add a space after all <br> tags so that #hashtags immediately after them are also converted to hashtag links. Without the space they aren't captured by the regex.
						cffTextStr = cffTextStr.replace(/<br>/g, "<br> ");
						$cffText.html( cffTextStr.replace( regex , replacer ) );
					}
				}

				//Replace hashtags in desc
				if( $self.find('.cff-post-desc').length > 0 ) $self.find('.cff-post-desc').html( cffDescStr.replace( regex , replacer ) );
			}
			cffLinkHashtags();

			//Add target attr to post text links via JS so aren't included in char count
			$self.find('.cff-text a').attr('target', '_blank');


			//Add lightbox tile link to photos
			if( $self.closest('#cff').hasClass('cff-lb') ){
				$self.find('.cff-photo, .cff-album-cover, .cff-event-thumb, .cff-html5-video, .cff-iframe-wrap').each(function(){
					var $photo = $(this),
						postId = post_id,
						cffLightboxTitle = '',
						cffShowThumbs = false,
						postType = '',
						cffgroupalbums = '';


					// if( $self.hasClass('cff-album') || $self.hasClass('cff-albums-only') ) cffShowThumbs = true;
					cffShowThumbs = true;

					function cffFormatCaption(text){
						return String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/\n/g, "<br/>");
					}

					//Set the caption/title
					if( $self.hasClass('cff-albums-only') ){
						postType = 'albumsonly';
						cffLightboxTitle = cffFormatCaption( $self.find('img').attr('alt') );
						
						//Check whether there's an absolute path attr and if there is then add it to the query
		      	var dataGroup = $self.closest('#cff').attr('data-group');
						if( typeof dataGroup !== 'undefined' ) cffgroupalbums = 'data-cffgroupalbums="true"';

					} else if( $self.hasClass('cff-timeline-event') ) {
						cffLightboxTitle = cffFormatCaption( $self.find('.cff-details p').eq(0).text() + ' - ' + $self.find('.cff-details p').eq(1).text() );
					} else if ( $self.hasClass('cff-event') ) {
						cffLightboxTitle = cffFormatCaption( $self.find('.cff-date').text() );
					} else if( $self.hasClass('cff-album-item') ) {
						cffLightboxTitle = cffFormatCaption( $self.find('img').attr('alt') );
					} else {
						// cffLightboxTitle = $self.find('.cff-text').text();
						cffLightboxTitle = cffFormatCaption( full_text );
					}

					if(cffLightboxTitle.length > 1) cffLightboxTitle = cffLightboxTitle.replace(/"/g, '&quot;');


					//Create the lightbox link
					//Add the hover tile
					var cffLightboxTile = '<a class="cff-lightbox-link" rel="nofollow" ';

					//If it's a YouTube or Vimeo then set the poster image to use in the lightbox
					if( $photo.hasClass('cff-iframe-wrap') ){
						cffLightboxTile += 'href="'+cffsiteurl+'/custom-facebook-feed-pro/img/video-lightbox.png" data-iframe="'+$photo.find('iframe').attr('src')+'" ';
					//If it's a swf then display it in an iframe
					} else if( $photo.hasClass('cff-swf') ) {
						cffLightboxTile += 'href="'+cffsiteurl+'/custom-facebook-feed-pro/img/video-lightbox.png" data-iframe="'+$photo.find('video').attr('src')+'" ';
					} else {
						cffLightboxTile += 'href="'+$photo.find('img').attr('src')+'" data-iframe="" ';
					}

					//No nav
					// cffLightboxTile += 'data-cff-lightbox="'+postId+'" data-title="'+cffLightboxTitle+'" data-id="'+postId+'" data-thumbs="'+cffShowThumbs+'" ';
					cffLightboxTile += 'data-cff-lightbox="cff-lightbox-'+$self.closest("#cff").attr("data-cff-lb")+'" data-title="'+cffLightboxTitle+'" data-id="'+postId+'" data-thumbs="'+cffShowThumbs+'" '+cffgroupalbums+' ';

					//If it's an HTML5 video then set the data-video attr
					if( $photo.hasClass('cff-html5-video') ){

						if($photo.hasClass('cff-swf')){
							cffLightboxTile += 'data-url="'+$photo.find('.cff-html5-play').attr('href')+'" data-video="';
						} else {
							cffLightboxTile += 'data-url="'+$photo.find('.cff-html5-play').attr('href')+'" data-video="'+$photo.find('video').attr('src');
						}

					//Videos only:
					} else if( $photo.hasClass('cff-video') ) {
						cffLightboxTile += 'data-url="http://facebook.com/'+$photo.attr('id')+'" data-video="'+$photo.attr('data-source');

					} else if( $photo.hasClass('cff-iframe-wrap') ) {
						cffLightboxTile += 'data-url="http://facebook.com/'+post_id+'" data-video="';
					} else {
						cffLightboxTile += 'data-url="'+$photo.attr('href')+'" data-video="';
					}

					cffLightboxTile += '" data-type="'+postType+'"><div class="cff-photo-hover"><i class="fa fa-arrows-alt"></i></div></a>';

					//Add the link to the photos/videos in the feed
					$photo.prepend(cffLightboxTile);

					//Fade in links on hover
					$photo.hover(function(){
						$self.find('.cff-photo-hover').fadeIn(200);
					}, function(){
						$self.find('.cff-photo-hover').stop().fadeOut(600);
					});
				});
			}

			//Share toolip function
			// jQuery(document).on('click', '.cff-share-link', function(){
	  		//	 $(this).siblings('.cff-share-tooltip').toggle();
	  		// });
			$self.find('.cff-share-link').unbind().bind('click', function(){
	            $self.find('.cff-share-tooltip').toggle();
	        });

			//Reviews
			// $self.find('.fa-star').css('height', $self.find('.cff-rating-num').innerHeight());


		}); //End .cff-item each

		

		//Load comment replies
		$('.cff-comment-replies a').on('click', function(){
			var $commentReplies = $(this).parent(),
				$commentRepliesBox = $commentReplies.siblings('.cff-comment-replies-box'),
				comments_url = cffsiteurl + "/custom-facebook-feed-pro/comments.php?id=" + $commentReplies.attr('data-id');

			if( $commentReplies.hasClass('cff-hide') ){

				$commentRepliesBox.hide();
				$commentReplies.removeClass('cff-hide');

			} else {

				$commentRepliesBox.show();
				$commentReplies.addClass('cff-hide');

				//If the replies haven't been retrieved yet then get them, otherwise just show the existing ones again
				if( $commentRepliesBox.hasClass('cff-empty') ){

					$.ajax({
			      method: "GET",
			      url: comments_url,
			      success: function(data) {

			      	//Convert string of data received from comments.php to a JSON object
			      	var data = jQuery.parseJSON( data ),
			      		allComments = '';

			    		$.each(data.comments.data, function(i, commentItem) {
							  allComments += '<div class="cff-comment-reply" id="cff_'+commentItem.id+'"><div class="cff-comment-text-wrapper"><div class="cff-comment-text"><p><a href="http://facebook.com/'+commentItem.from.id+'" class="cff-name" target="_blank" rel="nofollow" style="color:#;">'+commentItem.from.name+'</a>'+commentItem.message+'</p>';

							  //Add image attachment if exists
								if( typeof commentItem.attachment !== 'undefined' ) allComments += '<a class="cff-comment-attachment" href="'+commentItem.attachment.url+'" target="_blank"><img src="'+commentItem.attachment.media.image.src+'" alt="'+commentItem.attachment.title+'" /></a>';

			       		//Show like count if exists
							  if(parseInt(commentItem.like_count) > 0) allComments += '<span class="cff-time"><span class="cff-comment-likes"><b></b>'+commentItem.like_count+'</span></span>';

							  allComments += '</div></div><div class="cff-comment-img"><a href="http://facebook.com/'+commentItem.from.id+'" target="_blank" rel="nofollow"><img src="https://graph.facebook.com/'+commentItem.from.id+'/picture" width="20" height="20" alt="Avatar"></a></div></div>';
							});

			    		$commentReplies.siblings('.cff-comment-replies-box').html(allComments);

			    		$commentRepliesBox.removeClass('cff-empty');

			    	} //End success

					}); //End ajax

				}

			}

			

		}); //End click event



		$('.cff-wrapper').each(function(){
			var $cff = $(this).find('#cff');

			//Allow us to make some tweaks when the feed is narrow
			function cffCheckWidth(){
				if( $cff.innerWidth() < 400 ){
					if( !$cff.hasClass('cff-disable-narrow') ){
						$cff.addClass('narrow');
						//Use full-size shared link images on narrow layout, unless setting is unchecked
						$('.cff-shared-link .cff-link').each(function(){
							//$(this).find('img').attr('src', $(this).attr('data-full') );
						});
					}
				} else {
					$cff.removeClass('narrow');
				}
			}
			cffCheckWidth();

			function cffActionLinksPos(){
				if( $cff.innerWidth() < (160 + $('.cff-post-links').innerWidth() ) ){
					$cff.find('.cff-post-links').addClass('cff-left')
				} else {
					$cff.find('.cff-post-links').removeClass('cff-left');
				}
			}
			cffActionLinksPos();

			//Only check the width once the resize event is over
			var cffdelay = (function(){
				var cfftimer = 0;
					return function(cffcallback, cffms){
					clearTimeout (cfftimer);
					cfftimer = setTimeout(cffcallback, cffms);
				};
			})();
			window.addEventListener('resize', function(event){
				cffdelay(function(){
			    	cffCheckWidth();
			    	cffActionLinksPos();
			    	cffResizeAlbum();
			    }, 500);
			});

			//Albums only
			//Resize image height
			function cffResizeAlbum(){
				var cffAlbumWidth = $cff.find('.cff-album-item').eq(0).find('a').innerWidth();
				$cff.find('.cff-album-item a').css('height', cffAlbumWidth);
				//Crops event images when selected
				$cff.find('.cff-photo.cff-crop').css( 'height', $cff.find('.cff-photo.cff-crop').width() );
			}
			cffResizeAlbum();

		});


		//HTML5 Video play button
		$(document).on('click', '#cff .cff-html5-video .cff-html5-play', function(e){
			e.preventDefault();

			var $self = $(this),
				$videoWrapper = $self.closest('.cff-html5-video'),
				video = $self.siblings('video')[0];
			video.play();
			$self.hide();

			//Show controls when the play button is clicked
			if (video.hasAttribute("controls")) {
			    video.removeAttribute("controls")   
			} else {
			    video.setAttribute("controls","controls")   
			}

			if($videoWrapper.innerWidth() < 150 && !$videoWrapper.hasClass('cff-no-video-expand')) {
				$videoWrapper.css('width','100%').closest('.cff-item').find('.cff-text-wrapper').css('width','100%');
			}
		});



		//Cache the likes and comments counts by sending an array via ajax to the main plugin file which then stores it in a transient
		function cffCacheMeta(metaArr){

			//If the transient doesn't exist (set in head JS vars) then cache the data
			if(cffmetatrans == 'false'){
				var cffTimesCached = 0,
					cffCacheDelay = setTimeout(function() {
						var cffCacheInterval = setInterval(function(){
							
							//Send the data to DB via ajax every 3 seconds for 3 attempts
							$.ajax(opts);

							cffTimesCached++;
							if(cffTimesCached == 3) clearInterval(cffCacheInterval);
						}, 3000);

						//Send the data to DB initially via ajax after a 0.5 second delay
						$.ajax(opts);
					}, 500);
			}

		    opts = {
		        url: cffajaxurl,
		        type: 'POST',
		        async: true,
		        cache: false,
		        data:{
		            action: 'cache_meta', // Tell WordPress how to handle this ajax request
		            count: metaArr // Passes array of meta data to WP to cache

		            //set the cache time to be always 10 mins or use cache time from the db/shortcode?

		        },
		        success: function(response) {
		            return; 
		        },
		        error: function(xhr,textStatus,e) {  // This can be expanded to provide more information
		            return; 
		        }
		    };
		    
		}



	})(jQuery);








	/*!
	imgLiquid v0.9.944 / 03-05-2013
	https://github.com/karacas/imgLiquid
	*/

	var imgLiquid = imgLiquid || {VER: '0.9.944'};
	imgLiquid.bgs_Available = false;
	imgLiquid.bgs_CheckRunned = false;
	imgLiquid.injectCss = '.cff-album-cover img, .cff-photo.cff-crop img {visibility:hidden}';


	(function ($) {

		// ___________________________________________________________________

		function checkBgsIsavailable() {
			if (imgLiquid.bgs_CheckRunned) return;
			else imgLiquid.bgs_CheckRunned = true;

			var spanBgs = $('<span style="background-size:cover" />');
			$('body').append(spanBgs);

			!function () {
				var bgs_Check = spanBgs[0];
				if (!bgs_Check || !window.getComputedStyle) return;
				var compStyle = window.getComputedStyle(bgs_Check, null);
				if (!compStyle || !compStyle.backgroundSize) return;
				imgLiquid.bgs_Available = (compStyle.backgroundSize === 'cover');
			}();

			spanBgs.remove();
		}


		// ___________________________________________________________________

		$.fn.extend({
			imgLiquid: function (options) {

				this.defaults = {
					fill: true,
					verticalAlign: 'center',			//	'top'	//	'bottom' // '50%'  // '10%'
					horizontalAlign: 'center',			//	'left'	//	'right'  // '50%'  // '10%'
					useBackgroundSize: true,
					useDataHtmlAttr: true,

					responsive: true,					/* Only for use with BackgroundSize false (or old browsers) */
					delay: 0,							/* Only for use with BackgroundSize false (or old browsers) */
					fadeInTime: 0,						/* Only for use with BackgroundSize false (or old browsers) */
					removeBoxBackground: true,			/* Only for use with BackgroundSize false (or old browsers) */
					hardPixels: true,					/* Only for use with BackgroundSize false (or old browsers) */
					responsiveCheckTime: 500,			/* Only for use with BackgroundSize false (or old browsers) */ /* time to check div resize */
					timecheckvisibility: 500,			/* Only for use with BackgroundSize false (or old browsers) */ /* time to recheck if visible/loaded */

					// CALLBACKS
					onStart: null,						// no-params
					onFinish: null,						// no-params
					onItemStart: null,					// params: (index, container, img )
					onItemFinish: null,					// params: (index, container, img )
					onItemError: null					// params: (index, container, img )
				};


				checkBgsIsavailable();
				var imgLiquidRoot = this;

				// Extend global settings
				this.options = options;
				this.settings = $.extend({}, this.defaults, this.options);

				// CallBack
				if (this.settings.onStart) this.settings.onStart();


				// ___________________________________________________________________

				return this.each(function ($i) {

					// MAIN >> each for image

					var settings = imgLiquidRoot.settings,
					$imgBoxCont = $(this),
					$img = $('img:first',$imgBoxCont);
					if (!$img.length) {onError(); return;}


					// Extend settings
					if (!$img.data('imgLiquid_settings')) {
						// First time
						settings = $.extend({}, imgLiquidRoot.settings, getSettingsOverwrite());
					} else {
						// Recall
						// Remove Classes
						$imgBoxCont.removeClass('imgLiquid_error').removeClass('imgLiquid_ready');
						settings = $.extend({}, $img.data('imgLiquid_settings'), imgLiquidRoot.options);
					}
					$img.data('imgLiquid_settings', settings);


					// Start CallBack
					if (settings.onItemStart) settings.onItemStart($i, $imgBoxCont, $img); /* << CallBack */


					// Process
					if (imgLiquid.bgs_Available && settings.useBackgroundSize)
						processBgSize();
					else
						processOldMethod();


					// END MAIN <<

					// ___________________________________________________________________

					function processBgSize() {

						// Check change img src
						if ($imgBoxCont.css('background-image').indexOf(encodeURI($img.attr('src'))) === -1) {
							// Change
							$imgBoxCont.css({'background-image': 'url("' + encodeURI($img.attr('src')) + '")'});
						}

						$imgBoxCont.css({
							'background-size':		(settings.fill) ? 'cover' : 'contain',
							'background-position':	(settings.horizontalAlign + ' ' + settings.verticalAlign).toLowerCase(),
							'background-repeat':	'no-repeat'
						});

						$('a:first', $imgBoxCont).css({
							'display':	'block',
							'width':	'100%',
							'height':	'100%'
						});

						$('img', $imgBoxCont).css({'display': 'none'});

						if (settings.onItemFinish) settings.onItemFinish($i, $imgBoxCont, $img); /* << CallBack */

						$imgBoxCont.addClass('imgLiquid_bgSize');
						$imgBoxCont.addClass('imgLiquid_ready');
						checkFinish();
					}

					// ___________________________________________________________________

					function processOldMethod() {

						// Check change img src
						if ($img.data('oldSrc') && $img.data('oldSrc') !== $img.attr('src')) {

							/* Clone & Reset img */
							var $imgCopy = $img.clone().removeAttr('style');
							$imgCopy.data('imgLiquid_settings', $img.data('imgLiquid_settings'));
							$img.parent().prepend($imgCopy);
							$img.remove();
							$img = $imgCopy;
							$img[0].width = 0;

							// Bug ie with > if (!$img[0].complete && $img[0].width) onError();
							setTimeout(processOldMethod, 10);
							return;
						}


						// Reproceess?
						if ($img.data('imgLiquid_oldProcessed')) {
							makeOldProcess(); return;
						}


						// Set data
						$img.data('imgLiquid_oldProcessed', false);
						$img.data('oldSrc', $img.attr('src'));


						// Hide others images
						$('img:not(:first)', $imgBoxCont).css('display', 'none');


						// CSSs
						$imgBoxCont.css({'overflow': 'hidden'});
						$img.fadeTo(0, 0).removeAttr('width').removeAttr('height').css({
							'visibility': 'visible',
							'max-width': 'none',
							'max-height': 'none',
							'width': 'auto',
							'height': 'auto',
							'display': 'block'
						});


						// CheckErrors
						$img.on('error', onError);
						$img[0].onerror = onError;


						// loop until load
						function onLoad() {
							if ($img.data('imgLiquid_error') || $img.data('imgLiquid_loaded') || $img.data('imgLiquid_oldProcessed')) return;
							if ($imgBoxCont.is(':visible') && $img[0].complete && $img[0].width > 0 && $img[0].height > 0) {
								$img.data('imgLiquid_loaded', true);
								setTimeout(makeOldProcess, $i * settings.delay);
							} else {
								setTimeout(onLoad, settings.timecheckvisibility);
							}
						}


						onLoad();
						checkResponsive();
					}

					// ___________________________________________________________________

					function checkResponsive() {

						/* Only for oldProcessed method (background-size dont need) */

						if (!settings.responsive && !$img.data('imgLiquid_oldProcessed')) return;
						if (!$img.data('imgLiquid_settings')) return;

						settings = $img.data('imgLiquid_settings');

						$imgBoxCont.actualSize = $imgBoxCont.get(0).offsetWidth + ($imgBoxCont.get(0).offsetHeight / 10000);
						if ($imgBoxCont.sizeOld && $imgBoxCont.actualSize !== $imgBoxCont.sizeOld) makeOldProcess();

						$imgBoxCont.sizeOld = $imgBoxCont.actualSize;
						setTimeout(checkResponsive, settings.responsiveCheckTime);
					}

					// ___________________________________________________________________

					function onError() {
						$img.data('imgLiquid_error', true);
						$imgBoxCont.addClass('imgLiquid_error');
						if (settings.onItemError) settings.onItemError($i, $imgBoxCont, $img); /* << CallBack */
						checkFinish();
					}

					// ___________________________________________________________________

					function getSettingsOverwrite() {
						var SettingsOverwrite = {};

						if (imgLiquidRoot.settings.useDataHtmlAttr) {
							var dif = $imgBoxCont.attr('data-imgLiquid-fill'),
							ha =  $imgBoxCont.attr('data-imgLiquid-horizontalAlign'),
							va =  $imgBoxCont.attr('data-imgLiquid-verticalAlign');

							if (dif === 'true' || dif === 'false') SettingsOverwrite.fill = Boolean (dif === 'true');
							if (ha !== undefined && (ha === 'left' || ha === 'center' || ha === 'right' || ha.indexOf('%') !== -1)) SettingsOverwrite.horizontalAlign = ha;
							if (va !== undefined && (va === 'top' ||  va === 'bottom' || va === 'center' || va.indexOf('%') !== -1)) SettingsOverwrite.verticalAlign = va;
						}

						if (imgLiquid.isIE && imgLiquidRoot.settings.ieFadeInDisabled) SettingsOverwrite.fadeInTime = 0; //ie no anims
						return SettingsOverwrite;
					}

					// ___________________________________________________________________

					function makeOldProcess() { /* Only for old browsers, or useBackgroundSize seted false */

						// Calculate size
						var w, h, wn, hn, ha, va, hdif, vdif,
						margT = 0,
						margL = 0,
						$imgCW = $imgBoxCont.width(),
						$imgCH = $imgBoxCont.height();


						// Save original sizes
						if ($img.data('owidth')	=== undefined) $img.data('owidth',	$img[0].width);
						if ($img.data('oheight') === undefined) $img.data('oheight', $img[0].height);


						// Compare ratio
						if (settings.fill === ($imgCW / $imgCH) >= ($img.data('owidth') / $img.data('oheight'))) {
							w = '100%';
							h = 'auto';
							wn = Math.floor($imgCW);
							hn = Math.floor($imgCW * ($img.data('oheight') / $img.data('owidth')));
						} else {
							w = 'auto';
							h = '100%';
							wn = Math.floor($imgCH * ($img.data('owidth') / $img.data('oheight')));
							hn = Math.floor($imgCH);
						}

						// Align X
						ha = settings.horizontalAlign.toLowerCase();
						hdif = $imgCW - wn;
						if (ha === 'left') margL = 0;
						if (ha === 'center') margL = hdif * 0.5;
						if (ha === 'right') margL = hdif;
						if (ha.indexOf('%') !== -1){
							ha = parseInt (ha.replace('%',''), 10);
							if (ha > 0) margL = hdif * ha * 0.01;
						}


						// Align Y
						va = settings.verticalAlign.toLowerCase();
						vdif = $imgCH - hn;
						if (va === 'left') margT = 0;
						if (va === 'center') margT = vdif * 0.5;
						if (va === 'bottom') margT = vdif;
						if (va.indexOf('%') !== -1){
							va = parseInt (va.replace('%',''), 10);
							if (va > 0) margT = vdif * va * 0.01;
						}


						// Add Css
						if (settings.hardPixels) {w = wn; h = hn;}
						$img.css({
							'width': w,
							'height': h,
							'margin-left': Math.floor(margL),
							'margin-top': Math.floor(margT)
						});


						// FadeIn > Only first time
						if (!$img.data('imgLiquid_oldProcessed')) {
							$img.fadeTo(settings.fadeInTime, 1);
							$img.data('imgLiquid_oldProcessed', true);
							if (settings.removeBoxBackground) $imgBoxCont.css('background-image', 'none');
							$imgBoxCont.addClass('imgLiquid_nobgSize');
							$imgBoxCont.addClass('imgLiquid_ready');
						}


						if (settings.onItemFinish) settings.onItemFinish($i, $imgBoxCont, $img); /* << CallBack */
						checkFinish();
					}

					// ___________________________________________________________________

					function checkFinish() { /* Check callBack */
						if ($i === imgLiquidRoot.length - 1) if (imgLiquidRoot.settings.onFinish) imgLiquidRoot.settings.onFinish();
					}


				});
			}
		});
	})(jQuery);


	// Inject css styles ______________________________________________________
	!function () {
		var css = imgLiquid.injectCss,
		head = document.getElementsByTagName('head')[0],
		style = document.createElement('style');
		style.type = 'text/css';
		if (style.styleSheet) {
			style.styleSheet.cssText = css;
		} else {
			style.appendChild(document.createTextNode(css));
		}
		head.appendChild(style);
	}();
	jQuery(".cff-album-cover, .cff-photo.cff-crop").imgLiquid({fill:true});



// Used for linking text in captions
/* JavaScript Linkify - v0.3 - 6/27/2009 - http://benalman.com/projects/javascript-linkify/ */
window.cffLinkify=(function(){var k="[a-z\\d.-]+://",h="(?:(?:[0-9]|[1-9]\\d|1\\d{2}|2[0-4]\\d|25[0-5])\\.){3}(?:[0-9]|[1-9]\\d|1\\d{2}|2[0-4]\\d|25[0-5])",c="(?:(?:[^\\s!@#$%^&*()_=+[\\]{}\\\\|;:'\",.<>/?]+)\\.)+",n="(?:ac|ad|aero|ae|af|ag|ai|al|am|an|ao|aq|arpa|ar|asia|as|at|au|aw|ax|az|ba|bb|bd|be|bf|bg|bh|biz|bi|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|cat|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|coop|com|co|cr|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gg|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|im|info|int|in|io|iq|ir|is|it|je|jm|jobs|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|me|mg|mh|mil|mk|ml|mm|mn|mobi|mo|mp|mq|mr|ms|mt|museum|mu|mv|mw|mx|my|mz|name|na|nc|net|ne|nf|ng|ni|nl|no|np|nr|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pro|pr|ps|pt|pw|py|qa|re|ro|rs|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tel|tf|tg|th|tj|tk|tl|tm|tn|to|tp|travel|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|xn--0zwm56d|xn--11b5bs3a9aj6g|xn--80akhbyknj4f|xn--9t4b11yi5a|xn--deba0ad|xn--g6w251d|xn--hgbk6aj7f53bba|xn--hlcj6aya9esc7a|xn--jxalpdlp|xn--kgbechtv|xn--zckzah|ye|yt|yu|za|zm|zw)",f="(?:"+c+n+"|"+h+")",o="(?:[;/][^#?<>\\s]*)?",e="(?:\\?[^#<>\\s]*)?(?:#[^<>\\s]*)?",d="\\b"+k+"[^<>\\s]+",a="\\b"+f+o+e+"(?!\\w)",m="mailto:",j="(?:"+m+")?[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@"+f+e+"(?!\\w)",l=new RegExp("(?:"+d+"|"+a+"|"+j+")","ig"),g=new RegExp("^"+k,"i"),b={"'":"`",">":"<",")":"(","]":"[","}":"{","B;":"B+","b:":"b9"},i={callback:function(q,p){return p?'<a href="'+p+'" title="'+p+'" target="_blank">'+q+"</a>":q},punct_regexp:/(?:[!?.,:;'"]|(?:&|&amp;)(?:lt|gt|quot|apos|raquo|laquo|rsaquo|lsaquo);)$/};return function(u,z){z=z||{};var w,v,A,p,x="",t=[],s,E,C,y,q,D,B,r;for(v in i){if(z[v]===undefined){z[v]=i[v]}}while(w=l.exec(u)){A=w[0];E=l.lastIndex;C=E-A.length;if(/[\/:]/.test(u.charAt(C-1))){continue}do{y=A;r=A.substr(-1);B=b[r];if(B){q=A.match(new RegExp("\\"+B+"(?!$)","g"));D=A.match(new RegExp("\\"+r,"g"));if((q?q.length:0)<(D?D.length:0)){A=A.substr(0,A.length-1);E--}}if(z.punct_regexp){A=A.replace(z.punct_regexp,function(F){E-=F.length;return""})}}while(A.length&&A!==y);p=A;if(!g.test(p)){p=(p.indexOf("@")!==-1?(!p.indexOf(m)?"":m):!p.indexOf("irc.")?"irc://":!p.indexOf("ftp.")?"ftp://":"http://")+p}if(s!=C){t.push([u.slice(s,C)]);s=E}t.push([A,p])}t.push([u.substr(s)]);for(v=0;v<t.length;v++){x+=z.callback.apply(window,t[v])}return x||u}})();

//Link #hashtags
function cffReplaceHashtags(hash){
    //Remove white space at beginning of hash
    var replacementString = jQuery.trim(hash);
    //If the hash is a hex code then don't replace it with a link as it's likely in the style attr, eg: "color: #ff0000"
    if ( /^#[0-9A-F]{6}$/i.test( replacementString ) ){
        return replacementString;
    } else {
        return '<a href="https://www.facebook.com/hashtag/'+ replacementString.substring(1) +'" target="_blank" rel="nofollow">' + replacementString + '</a>';
    }
}
//Link @tags
function cffReplaceTags(tag){
    var replacementString = jQuery.trim(tag);
    return '<a href="https://www.facebook.com/'+ replacementString.substring(1) +'" target="_blank" rel="nofollow">' + replacementString + '</a>';
}
var hashRegex = /[#]+[A-Za-z0-9-_]+/g,
		tagRegex = /[@]+[A-Za-z0-9-_]+/g;
// End caption linking functions



	function cffLightbox(){
		/**
		 * Lightbox v2.7.1
		 * by Lokesh Dhakar - http://lokeshdhakar.com/projects/lightbox2/
		 *
		 * @license http://creativecommons.org/licenses/by/2.5/
		 * - Free for use in both personal and commercial projects
		 * - Attribution requires leaving author name, author link, and the license info intact
		 */

		(function() {
		  // Use local alias
		  var $ = jQuery;

		  var LightboxOptions = (function() {
		    function LightboxOptions() {
		      this.fadeDuration                = 300;
		      this.fitImagesInViewport         = true;
		      this.resizeDuration              = 400;
		      this.positionFromTop             = 50;
		      this.showImageNumberLabel        = true;
		      this.alwaysShowNavOnTouchDevices = false;
		      this.wrapAround                  = false;
		    }
		    
		    // Change to localize to non-english language
		    LightboxOptions.prototype.albumLabel = function(curImageNum, albumSize) {
		      return curImageNum + " / " + albumSize;
		    };

		    return LightboxOptions;
		  })();


		  var Lightbox = (function() {
		    function Lightbox(options) {
		      this.options           = options;
		      this.album             = [];
		      this.currentImageIndex = void 0;
		      this.init();
		    }

		    Lightbox.prototype.init = function() {
		      this.enable();
		      this.build();
		    };

		    // Loop through anchors and areamaps looking for either data-lightbox attributes or rel attributes
		    // that contain 'lightbox'. When these are clicked, start lightbox.
		    Lightbox.prototype.enable = function() {
		      var self = this;
		      $('body').on('click', 'a[data-cff-lightbox], area[data-cff-lightbox]', function(event) {
		        self.start($(event.currentTarget));
		        return false;
		      });
		    };

		    // Build html for the lightbox and the overlay.
		    // Attach event handlers to the new DOM elements. click click click
		    Lightbox.prototype.build = function() {
		      var self = this;
		      $("<div id='cff-lightbox-overlay' class='cff-lightbox-overlay'></div><div id='cff-lightbox-wrapper' class='cff-lightbox-wrapper'><div class='cff-lightbox-outerContainer'><div class='cff-lightbox-container'><video class='cff-lightbox-video' src='' poster='' controls></video><iframe type='text/html' src='' allowfullscreen frameborder='0' webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe><img class='cff-lightbox-image' src='' /><div class='cff-lightbox-nav'><a class='cff-lightbox-prev' href=''></a><a class='cff-lightbox-next' href=''></a></div><div class='cff-lightbox-loader'><a class='cff-lightbox-cancel'></a></div></div></div><div class='cff-lightbox-dataContainer'><div class='cff-lightbox-data'><div class='cff-lightbox-details'><p class='cff-lightbox-caption'><span class='cff-lightbox-caption-text'></span><a class='cff-lightbox-facebook' href=''>"+$('#cff').attr('data-fb-text')+"</a></p><div class='cff-lightbox-thumbs'><div class='cff-lightbox-thumbs-holder'></div></div></div><div class='cff-lightbox-closeContainer'><a class='cff-lightbox-close'><i class='fa fa-times'></i></a></div></div></div></div>").appendTo($('body'));
		      
		      // Cache jQuery objects
		      this.$lightbox       = $('#cff-lightbox-wrapper');
		      this.$overlay        = $('#cff-lightbox-overlay');
		      this.$outerContainer = this.$lightbox.find('.cff-lightbox-outerContainer');
		      this.$container      = this.$lightbox.find('.cff-lightbox-container');

		      // Store css values for future lookup
		      this.containerTopPadding = parseInt(this.$container.css('padding-top'), 10);
		      this.containerRightPadding = parseInt(this.$container.css('padding-right'), 10);
		      this.containerBottomPadding = parseInt(this.$container.css('padding-bottom'), 10);
		      this.containerLeftPadding = parseInt(this.$container.css('padding-left'), 10);
		      
		      // Attach event handlers to the newly minted DOM elements
		      this.$overlay.hide().on('click', function() {
		        self.end();
		        if( cff_supports_video() ) $('#cff-lightbox-wrapper video.cff-lightbox-video')[0].pause();
		        $('#cff-lightbox-wrapper iframe').attr('src', '');
		        return false;
		      });


		      this.$lightbox.hide().on('click', function(event) {
		        if ($(event.target).attr('id') === 'cff-lightbox-wrapper') {
		          self.end();
			        if( cff_supports_video() ) $('#cff-lightbox-wrapper video.cff-lightbox-video')[0].pause();
			        $('#cff-lightbox-wrapper iframe').attr('src', '');
		        }
		        return false;
		      });
		      this.$outerContainer.on('click', function(event) {
		        if ($(event.target).attr('id') === 'cff-lightbox-wrapper') {
		          self.end();
		          if( cff_supports_video() ) $('#cff-lightbox-wrapper video.cff-lightbox-video')[0].pause();
		        	$('#cff-lightbox-wrapper iframe').attr('src', '');
		        }
		        return false;
		      });


		      this.$lightbox.find('.cff-lightbox-prev').on('click', function() {
		        if (self.currentImageIndex === 0) {
		          self.changeImage(self.album.length - 1);
		        } else {
		          self.changeImage(self.currentImageIndex - 1);
		        }
		        if( cff_supports_video() ) $('#cff-lightbox-wrapper video.cff-lightbox-video')[0].pause();
		        $('#cff-lightbox-wrapper iframe').attr('src', '');
		        return false;
		      });

		      this.$lightbox.find('.cff-lightbox-next').on('click', function() {
		        if (self.currentImageIndex === self.album.length - 1) {
		          self.changeImage(0);
		        } else {
		          self.changeImage(self.currentImageIndex + 1);
		        }
		        if( cff_supports_video() ) $('#cff-lightbox-wrapper video.cff-lightbox-video')[0].pause();
		        $('#cff-lightbox-wrapper iframe').attr('src', '');
		        return false;
		      });


		      //CHANGE IMAGE ON THUMB CLICK
		      $('.cff-lightbox-thumbs').on('click', '.cff-lightbox-attachment', function (){
		      	var $thumb = $(this),
		      		$thumbImg = $thumb.find('img'),
							captionText = $thumb.attr('data-caption');

						if(captionText == '' || captionText == 'undefined') captionText = $thumb.attr('orig-caption');

		      	//Pass image URL, width and height to the change image function
		      	//We don't know the imageNumber here so pass in 'same' so that it stays the same
		        self.changeImage('same', $thumb.attr('href'), $thumbImg.attr('width'), $thumbImg.attr('height'), $thumb.attr('data-facebook'), captionText);
		        return false;
		      });


		      this.$lightbox.find('.cff-lightbox-loader, .cff-lightbox-close').on('click', function() {
		        self.end();
		        if( cff_supports_video() ) $('#cff-lightbox-wrapper video.cff-lightbox-video')[0].pause();
		        $('#cff-lightbox-wrapper iframe').attr('src', '');
		        return false;
		      });
		    };

		    // Show overlay and lightbox. If the image is part of a set, add siblings to album array.
		    Lightbox.prototype.start = function($link) {
		      var self    = this;
		      var $window = $(window);

		      $window.on('resize', $.proxy(this.sizeOverlay, this));

		      $('select, object, embed').css({
		        visibility: "hidden"
		      });

		      this.sizeOverlay();

		      this.album = [];
		      var imageNumber = 0;

		      function addToAlbum($link) {
		        self.album.push({
		          link: $link.attr('href'),
		          title: $link.attr('data-title') || $link.attr('title'),
		          postid: $link.attr('data-id'),
		          showthumbs: $link.attr('data-thumbs'),
		          facebookurl: $link.attr('data-url'),
		          video: $link.attr('data-video'),
		          iframe: $link.attr('data-iframe'),
		          type: $link.attr('data-type'),
		          cffgroupalbums: $link.attr('data-cffgroupalbums'),
		        });
		      }

		      // Support both data-lightbox attribute and rel attribute implementations
		      var dataLightboxValue = $link.attr('data-cff-lightbox');
		      var $links;

		      if (dataLightboxValue) {
		        $links = $($link.prop("tagName") + '[data-cff-lightbox="' + dataLightboxValue + '"]');
		        for (var i = 0; i < $links.length; i = ++i) {
		          addToAlbum($($links[i]));
		          if ($links[i] === $link[0]) {
		            imageNumber = i;
		          }
		        }
		      } else {
		        if ($link.attr('rel') === 'lightbox') {
		          // If image is not part of a set
		          addToAlbum($link);
		        } else {
		          // If image is part of a set
		          $links = $($link.prop("tagName") + '[rel="' + $link.attr('rel') + '"]');
		          for (var j = 0; j < $links.length; j = ++j) {
		            addToAlbum($($links[j]));
		            if ($links[j] === $link[0]) {
		              imageNumber = j;
		            }
		          }
		        }
		      }
		      
		      // Position Lightbox
		      var top  = $window.scrollTop() + this.options.positionFromTop;
		      var left = $window.scrollLeft();
		      this.$lightbox.css({
		        top: top + 'px',
		        left: left + 'px'
		      }).fadeIn(this.options.fadeDuration);

		      this.changeImage(imageNumber);
		    };

		    // Hide most UI elements in preparation for the animated resizing of the lightbox.
		    Lightbox.prototype.changeImage = function(imageNumberVal, imageUrl, imgWidth, imgHeight, facebookLink, captionText) {
		      var self = this,
		      	isThumb = false,
		      	bottomPadding = 120;

		      	if(imageNumberVal == 'same'){
		      		imageNumber = imageNumber;
		      	} else {
		      		imageNumber = imageNumberVal;
		      	}

		      //Is this a thumb being clicked?
		      if(typeof imageUrl !== 'undefined') isThumb = true;

		      this.disableKeyboardNav();
		      var $image = this.$lightbox.find('.cff-lightbox-image');

		      this.$overlay.fadeIn(this.options.fadeDuration);

		      $('.cff-lightbox-loader').fadeIn('slow');
		      this.$lightbox.find('.cff-lightbox-image, .cff-lightbox-nav, .cff-lightbox-prev, .cff-lightbox-next, .cff-lightbox-dataContainer, .cff-lightbox-numbers, .cff-lightbox-caption').hide();

		      this.$outerContainer.addClass('animating');


		      // When image to show is preloaded, we send the width and height to sizeContainer()
		      var preloader = new Image();
		      preloader.onload = function() {
		        var $preloader, imageHeight, imageWidth, maxImageHeight, maxImageWidth, windowHeight, windowWidth;
		        
		        $image.attr('src', self.album[imageNumber].link);

		        /*** THUMBS ***/
		        //Change the main image when the thumb is clicked
		        if(isThumb){
		        	$image.attr('src', imageUrl);
		        	$('.cff-lightbox-facebook').attr('href', facebookLink);
		        	$('.cff-lightbox-caption .cff-lightbox-caption-text').html(captionText);

		        	//Set width and height of image when thumb is clicked
		        	preloader.width = imgWidth;
		        	preloader.height = imgHeight;

		        	//Increase bottom padding to make room for at least one row of thumbs
		        	bottomPadding = 180;
		        }
		        /*** THUMBS ***/

		        $preloader = $(preloader);

		        $image.width(preloader.width);
		        $image.height(preloader.height);
		        
		        if (self.options.fitImagesInViewport) {
		          // Fit image inside the viewport.
		          // Take into account the border around the image and an additional 10px gutter on each side.

		          windowWidth    = $(window).width();
		          windowHeight   = $(window).height();
		          maxImageWidth  = windowWidth - self.containerLeftPadding - self.containerRightPadding - 20;
		          maxImageHeight = windowHeight - self.containerTopPadding - self.containerBottomPadding - bottomPadding;

		          // Is there a fitting issue?
		          if ((preloader.width > maxImageWidth) || (preloader.height > maxImageHeight)) {
		            if ((preloader.width / maxImageWidth) > (preloader.height / maxImageHeight)) {
		              imageWidth  = maxImageWidth;
		              imageHeight = parseInt(preloader.height / (preloader.width / imageWidth), 10);
		              $image.width(imageWidth);
		              $image.height(imageHeight);
		            } else {
		              imageHeight = maxImageHeight;
		              imageWidth = parseInt(preloader.width / (preloader.height / imageHeight), 10);
		              $image.width(imageWidth);
		              $image.height(imageHeight);
		            }
		          }
		        }

		        //Pass the width and height of the main image
		        self.sizeContainer($image.width(), $image.height());

		      };

		      preloader.src          = this.album[imageNumber].link;
		      this.currentImageIndex = imageNumber;
		    };

		    // Stretch overlay to fit the viewport
		    Lightbox.prototype.sizeOverlay = function() {
		      this.$overlay
		        .width($(window).width())
		        .height($(document).height());
		    };

		    // Animate the size of the lightbox to fit the image we are showing
		    Lightbox.prototype.sizeContainer = function(imageWidth, imageHeight) {
		      var self = this;
		      
		      var oldWidth  = this.$outerContainer.outerWidth();
		      var oldHeight = this.$outerContainer.outerHeight();
		      var newWidth  = imageWidth + this.containerLeftPadding + this.containerRightPadding;
		      var newHeight = imageHeight + this.containerTopPadding + this.containerBottomPadding;
		      
		      function postResize() {
		        self.$lightbox.find('.cff-lightbox-dataContainer').width(newWidth);
		        self.$lightbox.find('.cff-lightbox-prevLink').height(newHeight);
		        self.$lightbox.find('.cff-lightbox-nextLink').height(newHeight);
		        self.showImage();
		      }

		      if (oldWidth !== newWidth || oldHeight !== newHeight) {
		        this.$outerContainer.animate({
		          width: newWidth,
		          height: newHeight
		        }, this.options.resizeDuration, 'swing', function() {
		          postResize();
		        });
		      } else {
		        postResize();
		      }
		    };

		    // Display the image and it's details and begin preload neighboring images.
		    Lightbox.prototype.showImage = function() {
		      this.$lightbox.find('.cff-lightbox-loader').hide();
		      this.$lightbox.find('.cff-lightbox-image').fadeIn('slow');
		    
		      this.updateNav();
		      this.updateDetails();
		      this.preloadNeighboringImages();
		      this.enableKeyboardNav();
		    };

		    // Display previous and next navigation if appropriate.
		    Lightbox.prototype.updateNav = function() {
		      // Check to see if the browser supports touch events. If so, we take the conservative approach
		      // and assume that mouse hover events are not supported and always show prev/next navigation
		      // arrows in image sets.
		      var alwaysShowNav = false;
		      try {
		        document.createEvent("TouchEvent");
		        alwaysShowNav = (this.options.alwaysShowNavOnTouchDevices)? true: false;
		      } catch (e) {}

		      this.$lightbox.find('.cff-lightbox-nav').show();

		      if (this.album.length > 1) {
		        if (this.options.wrapAround) {
		          if (alwaysShowNav) {
		            this.$lightbox.find('.cff-lightbox-prev, .cff-lightbox-next').css('opacity', '1');
		          }
		          this.$lightbox.find('.cff-lightbox-prev, .cff-lightbox-next').show();
		        } else {
		          if (this.currentImageIndex > 0) {
		            this.$lightbox.find('.cff-lightbox-prev').show();
		            if (alwaysShowNav) {
		              this.$lightbox.find('.cff-lightbox-prev').css('opacity', '1');
		            }
		          }
		          if (this.currentImageIndex < this.album.length - 1) {
		            this.$lightbox.find('.cff-lightbox-next').show();
		            if (alwaysShowNav) {
		              this.$lightbox.find('.cff-lightbox-next').css('opacity', '1');
		            }
		          }
		        }
		      }
		    };

		    var thumbsArr = {};

		    // Display caption, image number, and closing button.
		    Lightbox.prototype.updateDetails = function() {
		    	var self = this;
		    	var origCaption = '';

		    	this.$lightbox.find('.cff-lightbox-nav, .cff-lightbox-nav a').show();

		      	/** NEW PHOTO ACTION **/
		      	//Switch video when either a new popup or navigating to new one
	            if( cff_supports_video() ){
	                $('#cff-lightbox-wrapper').removeClass('cff-has-video');
	                if( this.album[this.currentImageIndex].video.length ){
	                	$('#cff-lightbox-wrapper').addClass('cff-has-video');
		                $('.cff-lightbox-video').attr({
		                	'src' : this.album[this.currentImageIndex].video,
		                	'poster' : this.album[this.currentImageIndex].link,
		                	'autoplay' : 'true'
		                });
		            }
		        }

	            $('#cff-lightbox-wrapper').removeClass('cff-has-iframe');
		        if( this.album[this.currentImageIndex].iframe.length ){
		        	var videoURL = this.album[this.currentImageIndex].iframe;
	            	$('#cff-lightbox-wrapper').addClass('cff-has-iframe');

	            	//If it's a swf then don't add the autoplay parameter. This is only for embedded videos like YouTube or Vimeo.
	            	if( videoURL.indexOf(".swf") > -1 ){
	            		var autoplayParam = '';
	            	} else {
	            		var autoplayParam = '?autoplay=1';
	            	}

	            	//Add a slight delay before adding the URL else it doesn't autoplay on Firefox
		            var vInt = setTimeout(function() {
						$('#cff-lightbox-wrapper iframe').attr({
		                	'src' : videoURL + autoplayParam
		                });
					}, 500);
	            }


		      	//Remove existing thumbs
		      	$('.cff-lightbox-thumbs-holder').empty();

		      	//Change the link on the Facebook icon to be the link to the Facebook post only if it's the first image in the lightbox and one of the thumbs hasn't been clicked
		      	if( this.album[this.currentImageIndex].link == $('.cff-lightbox-image').attr('src') ){
		      		$('.cff-lightbox-facebook').attr('href', this.album[this.currentImageIndex].facebookurl);
		      	}

		      	//Show thumbs area if there are thumbs
		     	if( this.album[this.currentImageIndex].showthumbs == 'true' ){
		      		$('.cff-lightbox-thumbs').show();
		      		// $('.cff-lightbox-thumbs .cff-loader').show();

		      		//Get the post ID
		      		var thisPostId = this.album[this.currentImageIndex].postid,
		      			albumInfo = '',
				      	albumThumbs = '';


			      	if( typeof thumbsArr[thisPostId] !== 'undefined' ){

			      		//load them in from array
			      		$.each(thumbsArr[thisPostId], function(i, thumb) {
			      		  var origCaption = thumb[5].replace(/"/g, '&quot;');
						  albumThumbs += '<a href="'+thumb[0]+'" class="cff-lightbox-attachment" data-facebook="'+thumb[3]+'" data-caption="'+thumb[4]+'" orig-caption="'+origCaption+'"><img src="'+thumb[0]+'" width="'+thumb[1]+'" height="'+thumb[2]+'" /></a>';
						});

			      		//Add thumbs to the page
		            	$('.cff-lightbox-thumbs-holder').append( '<div style="margin-top: 10px;">' + albumThumbs + '</div>' );

		            	//Liquidfill the thumbs
		            	jQuery(".cff-lightbox-thumbs-holder a").imgLiquid({fill:true});

		            	//Hide the loader
		            	$('.cff-loader').hide();
						$('.cff-lightbox-thumbs-holder').css('min-height', 0);

			      	} else {
			      		//Use ajax to get them from Facebook API

			      		//Set paths for thumbs.php
					  	if (typeof cffsiteurl === 'undefined' || cffsiteurl == '') cffsiteurl = window.location.host + '/wp-content/plugins';

					  	//AJAX
					  	var cffAttachmentsUrl = cffsiteurl + "/custom-facebook-feed-pro/thumbs.php?id=" + thisPostId,
				      		thumbsData = [],
				      		albumsonly = false;

				      	//If this is an albums only item and the thumbs will
				      	if( this.album[this.currentImageIndex].type == 'albumsonly' ){
				      		albumsonly = true;
				      		cffAttachmentsUrl = cffAttachmentsUrl + '&albumsonly=true';
				      		$('.cff-lightbox-thumbs-holder').css('min-height', 45).after('<div class="cff-loader fa-spin"></div>');
				      	}

				      	//If it's a group album then add the absolute path so we can get the User Access Token from the DB
				      	var cffgroupalbums = this.album[this.currentImageIndex].cffgroupalbums;
				      	if( cffgroupalbums ) cffAttachmentsUrl = cffAttachmentsUrl + '&cffgroupalbums=' + cffgroupalbums;

				      	$.ajax({
			            method: "GET",
			            url: cffAttachmentsUrl,
			            // dataType: "jsonp",
			            success: function(data) {
			            	// albumInfo = '<h4>' + data.attachments.data[0].title + '</h4>';
			            	// albumInfo += '<p><a href="' + data.attachments.data[0].url + '" target="_blank">View album</a></p>';

			            	//Convert string of data received from thumbs.php to a JSON object
			            	data = jQuery.parseJSON( data );

			            	if(albumsonly){
			            		//Compile the thumbs
					      		$.each(data.data, function(i, photoItem) {
					      		  var dataCaption = '';
					      		  if( photoItem.name ) dataCaption = photoItem.name;
					      		  // origCaption = String(origCaption).replace(/"/g, '&quot;');

					      		//Format the caption and add links
					      		dataCaption = cffLinkify(dataCaption);
                		dataCaption = dataCaption.replace( hashRegex , cffReplaceHashtags );
                		// dataCaption = dataCaption.replace( tagRegex , cffReplaceTags ); - causes an issue with email address linking
										dataCaption = String(dataCaption).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/\n/g, "<br/>");

										origCaption = String(origCaption).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/\n/g, "<br/>");

								  albumThumbs += '<a href="'+photoItem.source+'" class="cff-lightbox-attachment" data-facebook="http://facebook.com/'+photoItem.id+'" data-caption="'+dataCaption+'" orig-caption="'+origCaption+'"><img src="'+photoItem.source+'" width="'+photoItem.width+'" height="'+photoItem.height+'" /></a>';

								  thumbsData.push([photoItem.source, photoItem.width, photoItem.height, 'http://facebook.com/'+photoItem.id, dataCaption, origCaption]);
								});
			            	} else {
			            		//Compile the thumbs
		            			$.each(data.attachments.data[0].subattachments.data, function(i, subattachment) {
		            			  var dataCaption = '';
					      		  if( subattachment.description ) dataCaption = subattachment.description;
					      		  origCaption = String(origCaption).replace(/"/g, '&quot;');

					      		//Format the caption and add links
					      		dataCaption = cffLinkify(dataCaption);
					      		dataCaption = dataCaption.replace( hashRegex , cffReplaceHashtags );
                		// dataCaption = dataCaption.replace( tagRegex , cffReplaceTags ); - causes an issue with email address linking
										dataCaption = String(dataCaption).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/\n/g, "<br/>");

								  albumThumbs += '<a href="'+subattachment.media.image.src+'" class="cff-lightbox-attachment" data-facebook="'+subattachment.url+'" data-caption="'+dataCaption+'" orig-caption="'+origCaption+'"><img src="'+subattachment.media.image.src+'" width="'+subattachment.media.image.width+'" height="'+subattachment.media.image.height+'" /></a>';

								  thumbsData.push([subattachment.media.image.src, subattachment.media.image.width, subattachment.media.image.height, subattachment.url, dataCaption, origCaption]);
								});
					      		
			            	}

							//Add thumbs to the page
			            	$('.cff-lightbox-thumbs-holder').append( '<div style="margin-top: 10px;">' + albumThumbs + '</div>' );

			            	//Liquidfill the thumbs
			            	jQuery(".cff-lightbox-thumbs-holder .cff-lightbox-attachment").imgLiquid({fill:true});

			            	//Hide the loader
			            	$('.cff-loader').hide();

							$('.cff-lightbox-thumbs-holder').css('min-height', 0);

			            	//Add the thumbs to the thumbs array to store them
			            	thumbsArr[ thisPostId ] = thumbsData;

			            	//Add a 'See More' link to thumbs which are more than 12. Use custom "See More" text for the link. Add an option to load more thumbs instead?
			            	// if( $('.cff-lightbox-attachment').length == 12 ) $('.cff-lightbox-thumbs-holder').append('<p><a href="https://facebook.com/'+data.id+'" style="width: 100%; height: auto;"">See More</a></p>');

				          }
				        });

			      	}


		      	} else {
		      		//If there are no thumbs then hide the thumbs area
		      		$('.cff-lightbox-thumbs').hide();
		      	}

		      	//Add a class to the selected thumb
		      	$(".cff-lightbox-attachment[href='"+$('.cff-lightbox-image').attr('src')+"']").addClass('cff-selected');

		      /** END NEW PHOTO ACTION **/

		      // Enable anchor clicks in the injected caption html.
		      // Thanks Nate Wright for the fix. @https://github.com/NateWr
		      if (typeof this.album[this.currentImageIndex].title !== 'undefined' && this.album[this.currentImageIndex].title !== "") {
		        
		      	//If it's the first image in the lightbox then set the caption to be the text from the post. For all subsequent images the caption is changed on the fly based elsehwere in the code based on an attr from the thumb that's clicked
		      	var origCaption = this.album[this.currentImageIndex].title;

		      	//Add hashtag and tag links
		      	// origCaption = cffLinkify(origCaption); - Caused issues with @tag links in regular lightbox popup
						origCaption = origCaption.replace( hashRegex , cffReplaceHashtags );
          	// origCaption = origCaption.replace( tagRegex , cffReplaceTags ); - causes an issue with email address linking

		      	if( this.album[this.currentImageIndex].link == $('.cff-lightbox-image').attr('src') ) this.$lightbox.find('.cff-lightbox-caption .cff-lightbox-caption-text').html( origCaption );

		        this.$lightbox.find('.cff-lightbox-caption').fadeIn('fast');
		        this.$lightbox.find('.cff-lightbox-facebook, .cff-lightbox-caption-text a').unbind().on('click', function(event){
		            window.open(
		            	$(this).attr('href'),
		            	'_blank'
		            )
		          });

		      }

		    
		      if (this.album.length > 1 && this.options.showImageNumberLabel) {
		        this.$lightbox.find('.cff-lightbox-number').text(this.options.albumLabel(this.currentImageIndex + 1, this.album.length)).fadeIn('fast');
		      } else {
		        this.$lightbox.find('.cff-lightbox-number').hide();
		      }
		    
		      this.$outerContainer.removeClass('animating');
		    
		      this.$lightbox.find('.cff-lightbox-dataContainer').fadeIn(this.options.resizeDuration, function() {
		        return self.sizeOverlay();
		      });
		    };

		    // Preload previous and next images in set.
		    Lightbox.prototype.preloadNeighboringImages = function() {
		      if (this.album.length > this.currentImageIndex + 1) {
		        var preloadNext = new Image();
		        preloadNext.src = this.album[this.currentImageIndex + 1].link;
		      }
		      if (this.currentImageIndex > 0) {
		        var preloadPrev = new Image();
		        preloadPrev.src = this.album[this.currentImageIndex - 1].link;
		      }
		    };

		    Lightbox.prototype.enableKeyboardNav = function() {
		      $(document).on('keyup.keyboard', $.proxy(this.keyboardAction, this));
		    };

		    Lightbox.prototype.disableKeyboardNav = function() {
		      $(document).off('.keyboard');
		    };

		    Lightbox.prototype.keyboardAction = function(event) {
				  var KEYCODE_ESC        = 27;
				  var KEYCODE_LEFTARROW  = 37;
				  var KEYCODE_RIGHTARROW = 39;

				  var keycode = event.keyCode;
				  var key     = String.fromCharCode(keycode).toLowerCase();
				  if (keycode === KEYCODE_ESC || key.match(/x|o|c/)) {
				  	if( cff_supports_video() ) $('#cff-lightbox-wrapper video.cff-lightbox-video')[0].pause();
				    $('#cff-lightbox-wrapper iframe').attr('src', '');
				  	
				    this.end();
				  } else if (key === 'p' || keycode === KEYCODE_LEFTARROW) {
				    if (this.currentImageIndex !== 0) {
				      this.changeImage(this.currentImageIndex - 1);
				    } else if (this.options.wrapAround && this.album.length > 1) {
				      this.changeImage(this.album.length - 1);
				    }

				    if( cff_supports_video() ) $('#cff-lightbox-wrapper video.cff-lightbox-video')[0].pause();
				    $('#cff-lightbox-wrapper iframe').attr('src', '');

				  } else if (key === 'n' || keycode === KEYCODE_RIGHTARROW) {
				    if (this.currentImageIndex !== this.album.length - 1) {
				      this.changeImage(this.currentImageIndex + 1);
				    } else if (this.options.wrapAround && this.album.length > 1) {
				      this.changeImage(0);
				    }

				    if( cff_supports_video() ) $('#cff-lightbox-wrapper video.cff-lightbox-video')[0].pause();
				    $('#cff-lightbox-wrapper iframe').attr('src', '');

				  }
				};

		    // Closing time. :-(
		    Lightbox.prototype.end = function() {
		      this.disableKeyboardNav();
		      $(window).off("resize", this.sizeOverlay);
		      this.$lightbox.fadeOut(this.options.fadeDuration);
		      this.$overlay.fadeOut(this.options.fadeDuration);
		      $('select, object, embed').css({
		        visibility: "visible"
		      });
		    };

		    return Lightbox;

		  })();

		  $(function() {
		    var options  = new LightboxOptions();
		    var lightbox = new Lightbox(options);
		  });

		}).call(this);

		//Checks whether browser support HTML5 video element
		function cff_supports_video() {
		  return !!document.createElement('video').canPlayType;
		}


	} //End cffLightbox function

	//Only call the lightbox if the class is on at least one feed on the page
	if( jQuery('#cff.cff-lb').length ) cffLightbox();

} //End cff_js_exists check