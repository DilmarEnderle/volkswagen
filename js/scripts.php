<script type="text/javascript" src="js/jquery-3.3.1.min.js?<?php echo VERSION; ?>"></script>
<!--[if IE 7]> <script type="text/javascript" src="http://sawpf.com/1.0.js"></script> <![endif]-->
<script type="text/javascript" src="js/jquery.inputmask.min.js?<?php echo VERSION; ?>"></script>
<script type="text/javascript" src="js/jquery.inputmask.min.date.js?<?php echo VERSION; ?>"></script>
<script type="text/javascript" src="js/jquery.maskMoney.min.js?<?php echo VERSION; ?>"></script>
<script type="text/javascript" src="js/c.ultimate.js?<?php echo VERSION; ?>"></script>
<script type="text/javascript" src="js/script.js?<?php echo VERSION; ?>"></script>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDaNijTwFJnN3XAKvhQm3-JK7VBGcWwylo"></script>
<script type="text/javascript" src="js/infobox.js?<?php echo VERSION; ?>"></script>
<script type="text/javascript" src="js/markerclusterer.min.js?<?php echo VERSION; ?>"></script>
<?php
	if ($endereco == "cli_index" || $endereco == "cli_open")
		echo '<script type="text/javascript" src="js/dhtmlxcalendar.js?'.VERSION.'"></script>';

	if ($endereco == "cli_open")
		echo '<script type="text/javascript" src="js/autosize.min.js?'.VERSION.'"></script>';

	if ($endereco == "cli_custom")
		echo '<script type="text/javascript" src="js/jscolor.js?'.VERSION.'"></script>';

	$filename = $endereco.".js";
	if (file_exists("js/".$filename))
		echo '<script type="text/javascript" src="js/'.$endereco.'.js?'.VERSION.'"></script>';
?>
<script type="text/javascript" src="js/c.online.js?<?php echo VERSION; ?>"></script>
