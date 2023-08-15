<?php

namespace Opencart\Admin\Controller\Extension\Wompi\Payment;

class Wompi extends \Opencart\System\Engine\Controller
{
    private $error = array();

    public function index()
    {
        $this->load->language('extension/wompi/payment/wompi');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/order_status');

        $this->load->model('setting/setting');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['code'])) {
            $data['error_code'] = $this->error['code'];
        } else {
            $data['error_code'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/wompi/payment/wompi', 'user_token=' . $this->session->data['user_token'], true),
        );

        $data['save'] = $this->url->link('extension/wompi/payment/wompi|save', 'user_token=' . $this->session->data['user_token']);
        $data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment');

        $data['user_token'] = $this->session->data['user_token'];

        $data['module_opencartwpblog_limit'] = $this->config->get('module_opencartwpblog_limit');

        $data['payment_wompi_public_key'] = $this->config->get('payment_wompi_public_key');
        $data['payment_wompi_private_key'] = $this->config->get('payment_wompi_private_key');
        $data['payment_wompi_status'] = $this->config->get('payment_wompi_status');

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        $data['payment_wompi_order_status_id'] = $this->config->get('payment_wompi_order_status_id');

        $data['payment_wompi_order_final_status_id'] = $this->config->get('payment_wompi_order_final_status_id');

        $this->response->setOutput($this->load->view('extension/wompi/payment/wompi', $data));
    }

    public function save()
    {
        $this->load->language('extension/wompi/payment/wompi');

        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/wompi/payment/wompi')) {
            $json['error'] = $this->language->get('error_permission');
        }

        if(!$this->request->post['payment_wompi_public_key'] || !$this->request->post['payment_wompi_private_key']) {
            
            $json['error'] = $this->language->get('error_credential');
        }

        if (!$json) {
            $this->load->model('setting/setting');

            $this->model_setting_setting->editSetting('payment_wompi', $this->request->post);

            $json['success'] = $this->language->get('text_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
