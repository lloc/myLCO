<div class="wrap">
<h2>{title}</h2>
{message}
{menu}
{content}
</div>
<script type="text/javascript">
//<![CDATA[
jQuery(document).ready( function($) {
	$("img.set_pr").each(function() {
		var loaderimg = $(this);
		var data = {
			action: "mylco_pagerank",
			url: loaderimg.attr("alt")
		};
		jQuery.post(ajaxurl, data, function(response) {
			loaderimg.hide().after(response);
		});
	});
	$("img.set_alexa").each(function() {
		var loaderimg = $(this);
		var data = {
			action: "mylco_alexa",
			url: loaderimg.attr("alt")
		};
		jQuery.post(ajaxurl, data, function(response) {
			loaderimg.hide().after(response);
		});
	});
});
//]]>
</script>
