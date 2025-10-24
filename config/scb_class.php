<?php
	class ScbPayment
	{
        private $ApiKey = "";
        private $SecretKey = "";
        private $BillerId = "";
        private $MerchantId = "";
        private $TerminalId = "";

		public function __construct($apikey, $secretkey, $biller_id, $cs_merchant_id, $cs_terminal_id)
		{
			$this->ApiKey = $apikey;
			$this->SecretKey = $secretkey;
			$this->BillerId = $biller_id;
            $this->CsMerchantId = $cs_merchant_id;
            $this->CsTerminalId = $cs_terminal_id;
		}

		public function OAuth($requestUId)
		{
			//$url = "https://api-sandbox.partners.scb/partners/sandbox/v1/oauth/token";
            $url = "https://api.partners.scb/partners/v1/oauth/token";
            
			$header = array(
                "Content-Type: application/json",
                "resourceOwnerId: ".$this->ApiKey,
                "requestUId : ".$requestUId
            );

			$body = '{
                "applicationKey" : "'.$this->ApiKey.'",
                "applicationSecret" : "'.$this->SecretKey.'"
            }';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            $response = curl_exec($ch);
            curl_close($ch);
            return $response;
		}

		public function CreateQR30($requestUId, $amount, $ref1, $ref2, $ref3) 
		{
            $response = $this->OAuth($requestUId);
            $response = json_decode($response, true);
            if( $response["status"]["code"] == 1000 )
            {
                //$url = "https://api-sandbox.partners.scb/partners/sandbox/v1/payment/qrcode/create";
                $url = "https://api.partners.scb/partners/v1/payment/qrcode/create";
                
                $header = array(
                    "content-type: application/json",
                    "accept-language: TH",
                    "authorization: Bearer ".$response["data"]["accessToken"],
                    "resourceOwnerId: ".$this->ApiKey,
                    "requestUId: ".$requestUId
                );
                //print_r($header);

                $body = '{
                    "qrType": "PP",
                    "ppType": "BILLERID",
                    "ppId": "'.$this->BillerId.'",
                    "amount": "'.$amount.'",
                    "ref1": "'.$ref1.'",
                    "ref2": "'.$ref2.'",
                    "ref3": "'.$ref3.'"
                }';
                //print_r($body);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
                $response = curl_exec($ch);
                curl_close($ch);
                $response = json_decode($response, true);
                return $response; 
            }
            else
            {
                return $response;
            }
        }

        public function RequestPullSlip($requestUId, $tran_id, $bank_code) 
        {
            $response = $this->OAuth($requestUId);
            $response = json_decode($response, true);
            if( $response["status"]["code"] == 1000 )
            {
                //$url = "https://api-sandbox.partners.scb/partners/sandbox/v1/payment/billpayment/transactions/".$tran_id."?sendingBank=".$bank_code;
                $url = "https://api.partners.scb/partners/v1/payment/billpayment/transactions/".$tran_id."?sendingBank=".$bank_code;
                
                $header = array(
                    "accept-language: TH",
                    "authorization: Bearer ".$response["data"]["accessToken"],
                    "requestUId: ".$requestUId,
                    "resourceOwnerId: ".$this->ApiKey
                );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $response = curl_exec($ch);
                if(curl_exec($ch) === false)
                {
                    $response = '{"status" : { "code" : "2222", "description" : "Error Request Pull Slip"} }';
                }
                curl_close($ch);
                $response = json_decode($response, true);

                return $response; 
            }
            else
            {
                return $response;
            }
        }

        public function QR30Inquiry ($requestUId, $ref_1, $ref_2, $tran_date) 
        {
            $response = $this->OAuth($requestUId);
            $response = json_decode($response, true);
            if( $response["status"]["code"] == 1000 )
            {
                //$url = "https://api-sandbox.partners.scb/partners/sandbox/v1/payment/billpayment/inquiry?billerId=".$this->BillerId."&reference1=".$ref_1."&reference2=".$ref_2."&transactionDate=".$tran_date."&eventCode=00300100";
                $url = "https://api.partners.scb/partners/v1/payment/billpayment/inquiry?eventCode=00300100&billerId=".$this->BillerId."&transactionDate=".$tran_date."&reference1=".$ref_1."&reference2=".$ref_2."";
                $header = array(
                    "accept-language: TH",
                    "content-type: application/json",
                    "authorization: Bearer ".$response["data"]["accessToken"],
                    "requestUId: ".$requestUId,
                    "resourceOwnerId: ".$this->ApiKey
                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $response = curl_exec($ch);
                if(curl_exec($ch) === false)
                {
                    $response = '{"status" : { "code" : "2222", "description" : "Error QR30 Inquiry"} }';
                }
                curl_close($ch);
                $response = json_decode($response, true);

                return $response; 
            }
            else
            {
                return $response;
            }
        }
        
	}
?>