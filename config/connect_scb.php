<?php
    /*$apikey = "l7fd234225cbce44e984bde1f935eefc4b";
    $secretkey = "302adb737b21408884f1cad1a583e2fb";
    $biller_id = "635338345596197";*/
    $apikey = "l745e72ba2a39e4a3288aa432b407419dc";
    $secretkey = "453b2f26c25f404c8c2184a76bbdb105";
    $biller_id = "099400058086003";
    $cs_merchant_id = "";
    $cs_terminal_id = "";

    $pay = new ScbPayment($apikey, $secretkey, $biller_id, $cs_merchant_id, $cs_terminal_id);
?>