<?php
if(!defined('_PS_VERSION_')){
    exit;
}
class PodSso extends Module {
    public function __construct()
    {
      $this->name = 'podsso';
      $this->tab = 'administration';
      $this->version = '1.0.6';
      $this->author = 'Mehran Rahbardar';
      $this->need_instance = 0;
      $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_); 
      $this->bootstrap = true;
   
      parent::__construct();
   
      $this->displayName = $this->l('POD SSO');
      $this->description = $this->l('POD SSO Client for Prestashop.');
   
      $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
   
      if (!Configuration::get('POD_CLIENTID')) {//Todo: add the rest
        $this->warning = $this->l('No name provided');
      }
    }
    public function install()
{
  return parent::install() &&
    $this->registerHook('displayPodLogin') &&
    Configuration::updateValue('POD_CLIENTID', 'AAAAAA')
    && Configuration::updateValue('POD_CLIENTSECRET', 'BBBBBB')
    && Configuration::updateValue('POD_SSO', 'https://accounts.pod.land/oauth2')
    && Configuration::updateValue('POD_APIURL', 'https://api.pod.land/srv/core')
    && Configuration::updateValue('POD_APITOKEN', 'CCCCC')
    && Configuration::updateValue('POD_INVOICE_URL', 'https://gw.pod.land/v1/pbc/payinvoice')
    && Configuration::updateValue('GUILD_CODE', 'INFORMATION_TECHNOLOGY_GUILD');

}
public function hookDisplayPodLogin($params)
{
    $module_dir = _PS_BASE_URL_.__PS_BASE_URI__.str_replace(_PS_ROOT_DIR_.'/', '', _PS_MODULE_DIR_);
    $client_id = Configuration::get('POD_CLIENTID');

    $pod_sso = Configuration::get('POD_SSO');
    $pod2 = '<a class="pod-sso-a" href="'."{$pod_sso}/authorize/?client_id={$client_id}&response_type=code&redirect_uri={$this->context->link->getModuleLink('podsso', 'handler')}&scope=profile email".'"type=pod" type="pod" title="Pod">';
    $pod3 = '<img class="pod-sso-img"  src="'.$module_dir.'podsso';
    $pod = $pod2.$pod3.'/views/img/buttons/pod_small.png"></a>';
                return $pod;
}
    public function uninstall()
        {
        if (!parent::uninstall()) {
            return false;
        }
        return true;
        }
    
        public function getContent()
        {
            $output = null;
         
            if (Tools::isSubmit('submit'.$this->name))
            {
                $client_id = strval(Tools::getValue('POD_CLIENTID'));
                $client_secret = strval(Tools::getValue('POD_CLIENTSECRET'));
                $sso = strval(Tools::getValue('POD_SSO'));
                $api_url = strval(Tools::getValue('POD_APIURL'));
                $api_token = strval(Tools::getValue('POD_APITOKEN'));
                $invoiceurl = strval(Tools::getValue('POD_INVOICE_URL'));
                $guild_code = strval(Tools::getValue('GUILD_CODE'));
                if (!$client_id//todo: add the rest
                  || empty($client_id)
                  || !Validate::isGenericName($client_id))
                    $output .= $this->displayError($this->l('Invalid Configuration value'));
                else
                {
                    Configuration::updateValue('POD_CLIENTID', $client_id);
                    Configuration::updateValue('POD_CLIENTSECRET', $client_secret);
                    Configuration::updateValue('POD_SSO', $sso);
                    Configuration::updateValue('POD_APIURL', $api_url);
                    Configuration::updateValue('POD_APITOKEN', $api_token);
                    Configuration::updateValue('POD_INVOICE_URL', $invoiceurl);
                    Configuration::updateValue('GUILD_CODE', $guild_code);
                    $output .= $this->displayConfirmation($this->l('Settings updated'));
                }
            }
            return $output.$this->displayForm();
        }
        public function displayForm()
{
    // Get default language
    $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
     
    // Init Fields form array
    $fields_form[0]['form'] = array(
        'legend' => array(
            'title' => $this->l('Settings'),
        ),
        'input' => array(
            array(
                'type' => 'text',
                'label' => $this->l('Client ID'),
                'name' => 'POD_CLIENTID',
                'size' => 20,
                'required' => true
            ),
            array(
                'type' => 'text',
                'label' => $this->l('Client Secret'),
                'name' => 'POD_CLIENTSECRET',
                'size' => 20,
                'required' => true
            ),
            array(
                'type' => 'text',
                'label' => $this->l('OAuth Server URL'),
                'name' => 'POD_SSO',
                'size' => 20,
                'required' => true
            ),
            array(
                'type' => 'text',
                'label' => $this->l('API URL'),
                'name' => 'POD_APIURL',
                'size' => 255,
                'required' => true
            ),
            array(
                'type' => 'text',
                'label' => $this->l('Api Token'),
                'name' => 'POD_APITOKEN',
                'size' => 20,
                'required' => true
            ),
            array(
                'type' => 'text',
                'label' => $this->l('Pay Invoice URL'),
                'name' => 'POD_INVOICE_URL',
                'size' => 255,
                'required' => true
            ),
            array(
                'type' => 'text',
                'label' => $this->l('Guild Code'),
                'name' => 'GUILD_CODE',
                'size' => 255,
                'required' => true
            ),
        ),
        'submit' => array(
            'title' => $this->l('Save'),
            'class' => 'btn btn-default pull-right'
        )
    );
     
    $helper = new HelperForm();
     
    // Module, token and currentIndex
    $helper->module = $this;
    $helper->name_controller = $this->name;
    $helper->token = Tools::getAdminTokenLite('AdminModules');
    $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
     
    // Language
    $helper->default_form_language = $default_lang;
    $helper->allow_employee_form_lang = $default_lang;
     
    // Title and toolbar
    $helper->title = $this->displayName;
    $helper->show_toolbar = true;        // false -> remove toolbar
    $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
    $helper->submit_action = 'submit'.$this->name;
    $helper->toolbar_btn = array(
        'save' =>
        array(
            'desc' => $this->l('Save'),
            'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
            '&token='.Tools::getAdminTokenLite('AdminModules'),
        ),
        'back' => array(
            'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Back to list')
        )
    );
     
    // Load current value
    $helper->fields_value['POD_CLIENTID'] = Configuration::get('POD_CLIENTID');
    $helper->fields_value['POD_CLIENTSECRET'] = Configuration::get('POD_CLIENTSECRET');
    $helper->fields_value['POD_SSO'] = Configuration::get('POD_SSO');
    $helper->fields_value['POD_APIURL'] = Configuration::get('POD_APIURL');
    $helper->fields_value['POD_APITOKEN'] = Configuration::get('POD_APITOKEN');
    $helper->fields_value['POD_INVOICE_URL'] = Configuration::get('POD_INVOICE_URL');
    $helper->fields_value['GUILD_CODE'] = Configuration::get('GUILD_CODE');
    //MYMODULE_NAME
    return $helper->generateForm($fields_form);
}
}
