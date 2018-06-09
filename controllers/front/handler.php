<?php
/**
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
* We offer the best and most useful modules PrestaShop and modifications for your online store. 
*
* @category  PrestaShop Module
* @author    profile.ir/rahbardar <m.rahbardar@fanap.com>
* @copyright 2018 fanap
* @license   see file: LICENSE.txt
*/
if(!isset($_SESSION))
{
  session_start();
}
class PodssoHandlerModuleFrontController extends ModuleFrontController
{
	public function initContent()
	{
		parent::initContent();
        $code = Tools::getValue('code');
		$url = "https://accounts.pod.land/oauth2/token/";
		$client_id = Configuration::get('POD_CLIENTID');
		$client_secret = Configuration::get('POD_CLIENTSECRET');
		
		$ch = curl_init($url);
		$fields = "client_id={$client_id}&client_secret={$client_secret}&code={$code}&redirect_uri={$this->context->link->getModuleLink('podsso', 'handler')}&grant_type=authorization_code";
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
		$response = curl_exec($ch);
		curl_close($ch);
		$token = json_decode($response);
		$_SESSION['access_token']= $token->access_token;
		$_SESSION['refresh_token']= $token->refresh_token;
		$_SESSION['expires_in']= $token->expires_in;
		$_SESSION['start_time']= time();

		$user_data = $this->getUserData();
		$r = $this->loginUser($user_data);
		Tools::redirect('my-account');
		
	}

	public function getUserData()
	{
		$access_token = $_SESSION['access_token'];
		$ch = curl_init('http://sandbox.pod.land/srv/basic-platform/nzh/getUserProfile/');
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			"_token_: {$access_token}",
			"_token_issuer_: 1"
		]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		$err = curl_error($ch);
		curl_close($ch);
		if ($err) {
			echo 'cURL Error #:' . $err;
			return false;
		} else {
			$resp = json_decode($response);
			return $resp->result;
		}		
	}

	public function loginUser($user_data){
		if (Customer::customerExists(strip_tags($user_data->email)))
		{
			$customer_obj = new Customer();
			$customer_tmp = $customer_obj->getByEmail($user_data->email);

			$customer = new Customer($customer_tmp->id);

		}
		else {
			$password = Tools::passwdGen();
            // Build customer fields.
            $customer = new CustomerCore();
            $customer->firstname = $user_data->firstName;
            $customer->lastname = $user_data->lastName;
            $customer->gender = $user_data->gender;
            $customer->birthday = '';
            $customer->active = true;
            $customer->deleted = false;
            $customer->is_guest = false;
			$customer->passwd = Tools::encrypt($password);
			$customer->email = $user_data->email;
			$customer->newsletter = false;
			$customer->add();
		}
			Hook::exec('actionBeforeAuthentication');

            $context = Context::getContext();
            $context->cookie->id_compare = isset($context->cookie->id_compare) ? $context->cookie->id_compare : CompareProduct::getIdCompareByIdCustomer($customer->id);
            $context->cookie->id_customer = (int) ($customer->id);
            $context->cookie->customer_lastname = $customer->lastname;
            $context->cookie->customer_firstname = $customer->firstname;
            $context->cookie->logged = 1;
            $context->cookie->is_guest = $customer->isGuest();
            $context->cookie->passwd = $customer->passwd;
            $context->cookie->email = $customer->email;

            // Customer is logged in
            $customer->logged = 1;

            // Add customer to the context
            $context->customer = $customer;

            if (Configuration::get('PS_CART_FOLLOWING') && (empty($context->cookie->id_cart) || Cart::getNbProducts($context->cookie->id_cart) == 0) && $id_cart = (int) Cart::lastNoneOrderedCart($context->customer->id))
            {
                $context->cart = new Cart($id_cart);
            }
            else
            {
                $context->cart->id_carrier = 0;
                $context->cart->setDeliveryOption(null);
                $context->cart->id_address_delivery = Address::getFirstCustomerAddressId((int) ($customer->id));
                $context->cart->id_address_invoice = Address::getFirstCustomerAddressId((int) ($customer->id));
            }
            $context->cart->id_customer = (int) $customer->id;
            $context->cart->secure_key = $customer->secure_key;
            $context->cart->save();

            $context->cookie->id_cart = (int) $context->cart->id;
            $context->cookie->update();
            $context->cart->autosetProductAddress();

            Hook::exec('actionAuthentication');

            // Login information have changed, so we check if the cart rules still apply
            CartRule::autoRemoveFromCart($context);
            CartRule::autoAddToCart($context);

            // Customer is now logged in.

            return true;

		
	}
}
?>