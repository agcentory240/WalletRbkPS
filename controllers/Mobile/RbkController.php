<?php
class WalletRbkPS_Mobile_RbkController extends Application_Controller_Mobile_Default {

	/*
		Generate checkout data
	*/
	public function createformAction() {
		/*ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);	*/
		if ($params = Zend_Json::decode($this->getRequest()->getRawBody())) {
			$data['params'] = $params;
			$wallet = (new Wallet_Model_Wallet())->find($params['wallet_id']);
			if ($wallet->getId()) {

				$model = new WalletRbkPS_Model_PaymentMethodsRbk();
				$model->find(['wallet_id'=>$wallet->getId()]);
				if ($model->getId()) {
				
					//Создадим запись в истории
					$history = new Wallet_Model_PaymentHistory();
					$history
						->setWalletId($wallet->getId())
						->setWalletCustomerId($params['wallet_customer_id'])
						->setSumm($params['amount'])
						->setCode('rbk')
						->setComplete(0)
						->save();
				
				
					$data['rbk'] = $model->getData();
					$data['currency'] = Core_Model_Language::getCurrentCurrency()->getShortName();


					//Создадим счет в РБК
					$headers = [];
					$headers[] = 'X-Request-ID: ' . uniqid();
					$headers[] = 'Authorization: Bearer ' . $model->getApiKey();
					$headers[] = 'Content-type: application/json; charset=utf-8';
					$headers[] = 'Accept: application/json';

					$request_data = array(
						"shopID" => $model->getShopId(),
						"dueDate" => date('c', strtotime(date("Y-m-d H:i:s") . ' +1 day')),
						"amount" => (int)($params['amount']*100),
						"externalID" => $history->getId(),
						"currency" => Core_Model_Language::getCurrentCurrency()->getShortName(),
						"product" => __("Deposit funds in the wallet") . " #" .$history->getId(),
						"metadata" => array("wallet_history_id"=> $history->getId(), "wallet_id"=>$wallet->getId())
					);
					$curl = curl_init();
					curl_setopt_array($curl, array(
					  CURLOPT_URL => "https://api.rbk.money/v1/processing/invoices",
					  CURLOPT_RETURNTRANSFER => true,
					  CURLOPT_ENCODING => "",
					  CURLOPT_MAXREDIRS => 10,
					  CURLOPT_TIMEOUT => 30,
					  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					  CURLOPT_CUSTOMREQUEST => "POST",
					  CURLOPT_POSTFIELDS => json_encode($request_data),
					  //CURLOPT_POSTFIELDS => "{\n  \"shopID\": \"TEST\",\n  \"dueDate\": \"2017-09-27T15:21:51.002Z\",\n  \"amount\": 8500,\n  \"currency\": \"RUB\",\n  \"product\": \"Заказ номер 12345\",\n  \"description\": \"Изысканная кухня\",\n    \"cart\": [\n        {\n            \"price\": 5000,\n            \"product\": \"Бутерброд с сыром\",\n            \"quantity\": 1,\n            \"taxMode\": {\n                \"rate\": \"10%\",\n                \"type\": \"InvoiceLineTaxVAT\"\n            }\n        },\n        {\n            \"price\": 2500,\n            \"product\": \"Компот\",\n            \"quantity\": 1,\n            \"taxMode\": {\n                \"rate\": \"18%\",\n                \"type\": \"InvoiceLineTaxVAT\"\n            }\n        },\n        {\n            \"price\": 1000,\n            \"product\": \"Доставка\",\n            \"quantity\": 1,\n            \"taxMode\": {\n                \"rate\": \"18%\",\n                \"type\": \"InvoiceLineTaxVAT\"\n            }\n        }\n    ],  \n\"metadata\": \n  { \n    \"order_id\": \"ID заказа во внутренней CRM: 13123298761\"\n  }\n}",
					  CURLOPT_HTTPHEADER => $headers,
					));
	
					$response = curl_exec($curl);
					$err = curl_error($curl);
					//echo $response;exit;
					curl_close($curl);
					$response = json_decode($response, true);
					$data['request_data']=$request_data;
					$data['response']=$response;
					if ($err || $response['errorType']!="") {
						$data['success']=false;
						$data['error_rbk']=$err;
						$data['error_rbk2']=$response['description'];
						$history->setComplete(-1)->save();						  
					} else {
					 
						$data['payment'] = $response;
						$data['payment_url']="";
						$data['success']=true;
						$history->setData('payment_id',$response["invoice"]["id"])->save();						  
					}					
					
						
				} else {
					$data = array('error' => 1, 'message' => 'An error occurred during process. Please try again later.');
					$history->setComplete(-1)->save();
				}
			} else {
				$data = array('error' => 1, 'message' => 'An error occurred during process. Please try again later.');
			}
			
		}else {
				$data = array('error' => 1, 'message' => 'An error occurred during process. Please try again later.');
		}

		$this->_sendHtml($data);
	}
	
	//Уведомление об оплате
	public function notifyAction() {
		
		$params = $this->getRequest()->getParams();
		$content = file_get_contents('php://input');
		$data['params'] = $params;
		if ($content!="" && $params ) {
			$data['payment'] = json_decode($content, TRUE);

			$history = new Wallet_Model_PaymentHistory();
			$history->find($data['payment']['invoice']['metadata']['wallet_history_id']);
			if ($history->getId()) {		
			
				//Найдем у нас транзакцию и пользователя и кошелек
				$wallet_customer = (new Wallet_Model_Customer())->find($history->getWalletCustomerId());
				$model = new WalletRbkPS_Model_PaymentMethodsRbk();
				$model->find(['wallet_id'=>$data['payment']['invoice']['metadata']['wallet_id']]);				
				file_put_contents($_SERVER['DOCUMENT_ROOT']."/test1.txt",$data['payment']['invoice']['status']."\n",FILE_APPEND);
				file_put_contents($_SERVER['DOCUMENT_ROOT']."/test1.txt",$data['payment']['invoice']['status']."\n",FILE_APPEND);
				file_put_contents($_SERVER['DOCUMENT_ROOT']."/test1.txt",$data['payment']['invoice']['id']."\n",FILE_APPEND);
				file_put_contents($_SERVER['DOCUMENT_ROOT']."/test1.txt",$history->getPaymentId()."\n",FILE_APPEND);
				file_put_contents($_SERVER['DOCUMENT_ROOT']."/test1.txt",$history->getComplete()."\n",FILE_APPEND);
				if ($model->getId()) {
					//Проверим
					if (isset($data['payment']['invoice']['status']) && $data['payment']['invoice']['status']=="paid" && $data['payment']['invoice']['id']==$history->getPaymentId() && $history->getComplete()==0) {
						//Оплачен и данные совпдают
						$history->setComplete(1)->save();
						$wallet_customer->addTransaction($history->getSumm(),"RBK Money - ".__("Deposit funds in the wallet"),'in',0,$wallet_customer->getId());						
					}
			
				} else {
					//транзакции нет такой
					$data['rbk_error'] = "payment method not found";
					$history->setComplete(-1)->save();
			
				
				}
			} else {
				//транзакции нет такой
				$data['rbk_error'] = "transaction_id not found";
				$history->setComplete(-1)->save();
	
			
			}
			file_put_contents($_SERVER['DOCUMENT_ROOT']."/test.txt",print_r($data,true),FILE_APPEND);
			file_put_contents($_SERVER['DOCUMENT_ROOT']."/test.txt",print_r($content,true),FILE_APPEND);
		}
		//file_put_contents($_SERVER['DOCUMENT_ROOT']."/test.txt",print_r($data,true),FILE_APPEND);
		//file_put_contents($_SERVER['DOCUMENT_ROOT']."/test.txt",print_r($content,true),FILE_APPEND);		
		$this->_sendHtml($data);

	}
}