<html>
<center>
<?php

//include ("http://localhost/~areski/svn/a2billing/trunk/A2Billing_UI/api/display_ratecard.php?ratecardid=6&key=0951aa29a67836b860b0865bc495225c&page_url=localhost/~areski/svn/a2billing/trunk/A2Billing_UI/api/test_api.php&field_to_display=t1.destination,t5.countryprefix,t1.dialprefix,t1.rateinitial&column_name=Destination,Country,Prefix,Rate/Min&field_type=,,money&".$_SERVER['QUERY_STRING']);

include ("http://localhost/~areski/svn/a2billing/trunk/A2Billing_UI/api/display_ratecard.php?key=0951aa29a67836b860b0865bc495225c&field_to_display=t1.destination,t1.dialprefix,t1.rateinitial&column_name=Destination,Prefix,Rate/Min&field_type='null,null,money'&".$_SERVER['QUERY_STRING']."&tariffgroupid=1&merge_form=1");
?> 
</center>
</html>
