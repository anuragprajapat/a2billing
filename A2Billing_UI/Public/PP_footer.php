<?php
	if (isset($HD_Form->DBHandle))
		DbDisconnect($HD_Form->DBHandle);
	
	if (!isset($displayfooter) || $displayfooter){
	
	if (!isset($disp_printable) || (!$disp_printable)){
    		include_once (dirname(__FILE__)."/../lib/company_info.php");
?>


<br></br>
<div id="kiblue"><div class="w1"><?php  echo COPYRIGHT; ?></div></div>
<br>

<?php } ?>

</div>
</body>
</html>
<?php }
?>