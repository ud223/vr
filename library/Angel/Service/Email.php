<?php

class Angel_Service_Email{

    protected $_bootstrap_options;
    
    public function __construct($bootstrap_options){
        $this->_bootstrap_options = $bootstrap_options;
    }
    
    public function sendEmail($template, $to, $subject, $params=array()){
        try{
            $config = array('auth' => 'Login',
                            'port' => $this->_bootstrap_options['mail']['port'],
                            'ssl' => 'ssl',
                            'username' => $this->_bootstrap_options['mail']['username'],
                            'password' => $this->_bootstrap_options['mail']['password']);

            $tr = new Zend_Mail_Transport_Smtp($this->_bootstrap_options['mail']['server'], $config);
            Zend_Mail::setDefaultTransport($tr);

            $mail = new Zend_Mail('UTF-8');

            $layout = new Zend_Layout();
            $layout->setLayoutPath($this->_bootstrap_options['mail']['layout']);
            $layout->setLayout('email');
            $view = $layout->getView();
            $view->domain_url = $this->_bootstrap_options['site']['domainurl'];

            $view = new Zend_View();
            $view->params = $params;
            $view->setScriptPath($this->_bootstrap_options['mail']['view_script']);

            $layout->content = $view->render($template.'.phtml');

            $content = $layout->render();
            $mail->setBodyText(preg_replace('/<[^>]+>/','', $content));
            $mail->setBodyHtml($content);
            $mail->setFrom($this->_bootstrap_options['mail']['from'],  $this->_bootstrap_options['mail']['from_name']);
            $mail->addTo($to);
            $mail->setSubject($subject);
            $mail->send();
        }
        catch (Exception $e){
            // 这里要完善
        }
        
        return true;
    }
    
}
