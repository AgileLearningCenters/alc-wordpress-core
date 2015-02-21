/* init variables */
var itro_is_preview;
var itro_cookie_expiration;
var itro_age_restriction;

/* manage fade in animation */
function itro_enter_anim()
{
	if( document.cookie.indexOf("popup_cookie") == -1 || itro_is_preview === true )
	{
		itro_popup.style.visibility = '';
		itro_opaco.style.visibility = '';
		itro_popup.style.display = 'none';
		itro_opaco.style.display = 'none';
		jQuery("#itro_opaco").fadeIn(function()
		{
			jQuery("#itro_popup").fadeIn();
			if( itro_age_restriction === false )
			{
				itro_set_cookie("popup_cookie","one_time_popup", itro_cookie_expiration);
			}
		});
	}
	
}

/* function for automatic top margin refresh, to center the popup vertically */
function marginRefresh()
{	
	if( typeof( window.innerWidth ) == 'number' ) 
	{
		/* Non-IE */
		browserWidth = window.innerWidth;
		browserHeight = window.innerHeight;
	} else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) 
	{
		/* IE 6+ in 'standards compliant mode' */
		browserWidth = document.documentElement.clientWidth;
		browserHeight = document.documentElement.clientHeight;
	} else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) 
	{
		/* IE 4 compatible */
		browserWidth = document.body.clientWidth;
		browserHeight = document.body.clientHeight;
	}
	popupHeight = document.getElementById('itro_popup').offsetHeight ; 			/* get the actual px size of popup div */
	document.getElementById('itro_popup').style.top = (browserHeight - popupHeight)/2 + "px"; /* update the top margin of popup					 */
}

/* function for countdown to show popup when the delay is set */
function popup_delay() 
{ 
	delay--;
	if(delay <= 0) 
	{
		clearInterval(interval_id_delay);
		itro_enter_anim();
	}
}

/* countdown for automatic closing */
function popTimer()
{
	if (popTime>0)
	{
		document.getElementById("timer").innerHTML=popTime;
		popTime--;
	}
	else
	{
		clearInterval(interval_id);
		jQuery("#itro_popup").fadeOut(function() {itro_opaco.style.visibility='Hidden';});
	}
}

/* function use to set the cookie for next visualization time */
function itro_set_cookie(c_name,value,exhours)
{
	var exdate=new Date();
	exdate.setTime(exdate.getTime() + (exhours * 3600 * 1000));
	var c_value=escape(value) + ((exhours==null) ? "" : "; expires="+exdate.toUTCString());
	document.cookie=c_name + "=" + c_value + "; path=/";
}