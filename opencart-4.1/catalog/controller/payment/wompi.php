<?php
namespace Opencart\Catalog\Controller\Extension\Wompi\Payment;

class Wompi extends \Opencart\System\Engine\Controller
{
    public function index()
    {
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $data['wompi_public_key'] = $this->config->get('payment_wompi_public_key'); 
        $timestamps = time();
        
        $data['wompi_reference'] = $this->session->data['order_id'] . '-' . $timestamps;

        $data['real_amount'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);;

        $data['amount'] = $order_info['total'] * 100;

        $data['currency_code'] = 'COP';

        $data['integrity'] =  hash ("sha256", $data['wompi_reference']. $data['amount'] . $data['currency_code'] . $timestamps);

        $data['url_redirect'] = $this->url->link('extension/wompi/payment/wompi|callback');

       	return $this->load->view('extension/wompi/payment/wompi', $data);
    }

    private function isTest() {

       return strpos($this->config->get('payment_wompi_public_key'), 'test'); 
    }

    public function confirm() {

        $this->load->language('extension/opencart/payment/wompi');

		$json = array();

        if (!isset($this->session->data['order_id'])) {
			$json['error'] = $this->language->get('error_order');
		}
		
		
	    if (!$json) {
			$this->load->model('checkout/order');

			$this->model_checkout_order->addHistory($this->session->data['order_id'], $this->config->get('payment_wompi_order_status_id'));

			$json['redirect'] = $this->url->link('checkout/success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}


    private function responseJson($json) {
        $this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
    }

    public function callback(){
        $json = array();

        if(!isset($_GET['id'])){ 
            $json['redirect'] = $this->url->link('checkout/failure');
            $this->responseJson($json);
            return ;  
         }

       
        $this->load->model('checkout/order'); 

        $ref_wompi = $_GET['id'];
        $url= $this->isTest() ? "https://sandbox.wompi.co/v1/transactions/".$ref_wompi :"https://production.wompi.co/v1/transactions/".$ref_wompi;
        $response=json_decode(file_get_contents($url));
        $data = (array)$response->data;

        if(!$data) {
            $json['redirect'] = $this->url->link('checkout/failure');
            $this->responseJson($json);
            return ;  
        }

        $order_id = explode("-", $data['reference'])[0];

        $order_info = $this->model_checkout_order->getOrder($order_id);

        if(!$order_info) {
            $json['redirect'] = $this->url->link('checkout/failure');
            $json['message'] = 'Order not found';
            $this->responseJson($json);
            return ;      
        }

        if(floatval($order_info['total'] * 100) != floatval($data['amount_in_cents'])) {
            $json['redirect'] = $this->url->link('checkout/failure');
            $json['message'] = 'Invalid amount';
            $this->responseJson($json);
            return ;   
        }

        if($order_info['order_status'] == 'Canceled') {
            $json['redirect'] = $this->url->link('checkout/failure');
            $json['message'] = 'Order canceled';
            $this->responseJson($json);
            return ;  
        }
        
         
        $this->model_checkout_order->addHistory($order_id, $this->config->get('payment_wompi_order_final_status_id'));


        $json['redirect'] = $this->url->link('checkout/success');
        $this->responseJson($json);
        
    }
}
