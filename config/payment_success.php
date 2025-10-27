<?php
    if(json_decode(file_get_contents("php://input"), true))
    {
        $str = file_get_contents("php://input");
        $json = json_decode($str);
        $field = array();
        $field['id'] = $con->create_id('payment_confirm','id','ID');
        $id = $field['id'];

        $response = array();
        $response["resCode"] = "00";
        $response["resDesc"] = "success";
        $response["transactionId"] = $json->transactionId;
        $response["confirmId"] = $id;
        $display_response = json_encode($response);
        echo $display_response;

        $target_move = ROOT.DS."webroot".DS."files".DS."payment".DS;
        $filename = $target_move.date("Y-m-d")."-Confirm.txt";
        $file = fopen($filename, "a+");
        fwrite($file, $display_response);
        fclose($file);

        $field['payeeProxyId'] = $json->payeeProxyId;
        $field['payeeProxyType'] = $json->payeeProxyType;
        $field['payeeAccountNumber'] = $json->payeeAccountNumber;
        $field['payeeName'] = $json->payeeName;
        $field['payerProxyId'] = $json->payerProxyId;
        $field['payerProxyType'] = $json->payerProxyType;
        $field['payerAccountNumber'] = $json->payerAccountNumber;
        $field['payerName'] = $json->payerName;
        $field['sendingBankCode'] = $json->sendingBankCode;
        $field['receivingBankCode'] = $json->receivingBankCode;
        $field['amount'] = $json->amount;
        $field['channelCode'] = $json->channelCode;
        $field['transactionId'] = $json->transactionId;
        $field['transactionDateandTime'] = $json->transactionDateandTime;
        $field['billPaymentRef1'] = $json->billPaymentRef1;
        $field['billPaymentRef2'] = $json->billPaymentRef2;
        $field['billPaymentRef3'] = $json->billPaymentRef3;
        $field['currencyCode'] = $json->currencyCode;
        $field['transactionType'] = $json->transactionType;
        $field['date_update'] = date("Y-m-d H:i:s");
        $rslt = $con->insert_sql('payment_confirm',$field);

        if( !$rslt )
        {
            $target_move = ROOT.DS."webroot".DS."files".DS."payment".DS;
            $filename = $target_move.date("Y-m-d")."-Error.txt";
            $file = fopen($filename, "a+");
            $data = file_get_contents("php://input");
            fwrite($file, $data);
            fclose($file);
        }
        else
        {
            $target_move = ROOT.DS."webroot".DS."files".DS."payment".DS;
            $filename = $target_move.date("Y-m-d").".txt";
            $file = fopen($filename, "a+");
            $data = file_get_contents("php://input");
            fwrite($file, $data);
            fclose($file);
        }
    }
