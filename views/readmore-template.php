<?php
    $data = get_query_var('data');
    $url = $data['url'];
    $display_type = $data['display_type'];
    $entityId = $data['entityId'];

?>

<p>
	<a class="sm-readmore-link sm-display-modal" data-entity-id="<?php echo ($entityId); ?>" data-fancybox-type="iframe" href="<?php echo ($url); ?>" target="_blank" data-display-type="">
		Lire la suite
	</a>
</p>


