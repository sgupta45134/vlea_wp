<?php
include_once('Request.php');

class PayPalLib
{
    private $endpoint;
    private $_host;
    private $_gate;
    private $_transaction;

    const VERSION = '52.0';
    const METHOD = 'SetExpressCheckout';

    function __construct($real = false)
    {
        $this->endpoint = '/nvp';

        if( !CMMicropaymentPlatform::get_option('cm_micropayment_test_mode') )
        {
            $this->setHost("api-3t.paypal.com");
            $this->setGate('https://www.paypal.com/cgi-bin/webscr?');
        }
        else
        {
            $this->setHost("api-3t.sandbox.paypal.com");
            $this->setGate('https://www.sandbox.paypal.com/cgi-bin/webscr?');
        }

        if( CMMicropaymentPlatform::get_option('cm_micropayment_paypal_email') == ''
        )
        {
            throw new Exception('Missing PayPal Credentials');
        }
    }

    private function getReturnTo()
    {
        //return admin_url('admin-ajax.php?action=cmmicropayment_success&transaction=' . $this->getTransactionId());
		return get_page_link(CMMicropaymentPlatform::get_option('cm_micropayment_success_page_id')).'?action=cmmicropayment_success&transaction=' . $this->getTransactionId();
    }

    private function getReturnToCancel()
    {
        return get_page_link(CMMicropaymentPlatform::get_option('cm_micropayment_error_page_id'));
    }

    private function buildQuery($data = array())
    {
        $data['business'] = CMMicropaymentPlatform::get_option('cm_micropayment_paypal_email');
        $data['VERSION'] = self::VERSION;
        $query = http_build_query($data);
        return $query;
    }

    public function doPayment($amount, $desc, $invoice = '', $currency = 'USD')
    {
        $data = array(
            'cmd'           => '_xclick',
            'charset'       => 'UTF-8',
            'amount'        => $amount,
            'return'        => $this->getReturnTo(),
            'CANCELURL'     => $this->getReturnToCancel(),
            'item_name'     => $desc,
            'no_shipping'   => "1",
            'no_note'       => "1",
            'currency_code' => $currency,
            'page_style'    => CMMicropaymentPlatform::get_option('cm_micropayment_paypal_page_style'),
            'METHOD'        => self::METHOD
        );

        $data['CUSTOM'] = $amount . '|' . $currency . '|' . $invoice;
        if( $invoice ) $data['INVNUM'] = $invoice;

        $query = $this->buildQuery($data);


        if( isset($_SESSION['transaction_id']) )
        {
            unset($_SESSION['transaction_id']);
        }

        wp_redirect($this->getGate() . $query);
        exit;
        return;
    }

    public function setHost($host)
    {
        $this->_host = $host;
    }

    public function getHost()
    {
        return $this->_host;
    }

    public function setGate($gate)
    {
        $this->_gate = $gate;
    }

    public function getGate()
    {
        return $this->_gate;
    }

    public function setTransactionId($transaction)
    {
        $this->_transaction = $transaction;
    }

    public function getTransactionId()
    {
        return base64_encode($this->_transaction);
    }

}
?>
