<?php
    $data = get_query_var('data');
    $url = $data['url'];
    $display_type = $data['display_type'];

?>

<p>
	<link rel="canonical" href="<?php echo ($url); ?>" />

	<a class="sm-readmore-link sm-display-modal" data-article-link=""
	data-fancybox-type="iframe" href="<?php echo ($url); ?>" target="_blank" data-display-type="<?php echo ($display_type); ?>">
			Lire la suite
	</a>
</p>


