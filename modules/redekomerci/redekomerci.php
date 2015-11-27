<?php
if (!defined('_PS_VERSION_'))
  exit;

class RedeKomerci extends PaymentModule
{
  public function __construct()
  {
    $this->name = 'redekomerci';
    $this->tab = 'payments_gateways';
    $this->version = '0.1';
    $this->author = 'Ideia Embalagens';
    $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    $this->bootstrap = true;

    parent::__construct();

    $this->displayName = 'Redecard Komerci API';
    $this->description = 'Módulo de pagamento da Redecard.';

    $this->confirmUninstall = 'Deseja realmente excluir?';
  }

  public function install(){
    if (!parent::install() || !$this->registerHook('displayPayment') || !$this->registerHook('displayPaymentReturn'))
      return false;
    $db = Db::getInstance();
    // Cria a tabela de cadastro de cep
    $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'redekomerci01` (
          `id`          int(10)     NOT NULL AUTO_INCREMENT,
          `ativa_visa`        int(1),
          `Visa`         varchar(30),
          `ativa_master`      int(1),
          `Mastercard`       varchar(30),
          `ativa_diners`  int(1),
          `Diners Club` varchar(30),
          `ativa_hiper`  int(1),
          `Hiper` varchar(30),
          `ativa_hipercard`  int(1),
          `Hipercard` varchar(30),
          PRIMARY KEY  (`id`)
          ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
    $db-> Execute($sql);
    if (!Configuration::hasKey('ativa_visa')) {
      Configuration::updateValue('ativa_visa', '0');
    }
    if (!Configuration::hasKey('ativa_master')) {
      Configuration::updateValue('ativa_master', '0');
    }
    if (!Configuration::hasKey('ativa_diners')) {
      Configuration::updateValue('ativa_diners', '0');
    }
    if (!Configuration::hasKey('ativa_hipercard')) {
      Configuration::updateValue('ativa_hipercard', '0');
    }
    if (!Configuration::hasKey('ativa_hiper')) {
      Configuration::updateValue('ativa_hiper', '0');
    }

    return true;
  }

  public function uninstall()
  {
    if (!parent::uninstall())
      return false;
    $sql = "DROP TABLE IF EXISTS `"._DB_PREFIX_."redekomerci01`;";
    Db::getInstance()->execute($sql);
    return true;
  }

  public function hookDisplayPayment(){
    $this->context->controller->addCSS($this->_path.'views/css/redekomerci.css', 'all');
    return $this->display(__FILE__, 'displayPayment.tpl');
  }

  public function getContent(){
    $output = null;

    if(Tools::isSubmit('submitkomerciconfig')){
      $results = Tools::getValue();
      foreach ($result as $key => $value) {
        if(!$value || empty($value))
          $output .= $this->displayError('Configuração inválida');
        else{
          Configuration::updateValue($key,$value);
          $output .= $this->displayConfirmation('Alterações salvas com sucesso');
        }
      }
    }

    return $this->html .= $output.$this->displayForm();
  }

  public function displayForm(){
    $helper = new HelperForm();

    $helper->module = $this;
    $helper->name_controller = $this->name;
    $helper->token = Tools::getAdminTokenLite('AdminModules');
    $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

    $helper->default_form_language = $default_lang;
    $helper->allow_employee_form_lang = $default_lang;

    $helper->title = $this->displayName;
    $helper->show_toolbar = true;
    $helper->toolbar_scroll = true;
    $helper->submit_action = 'submit'.$this->name;
    $helper->toolbar_btn = array(
      'save' =>  
        array(
          'desc' => 'Salvar',
          'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
          ),
      'back' =>
        array(
          'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
          )
      );
    return $this->display(__FILE__,'view/config/config.tpl');
  }
}