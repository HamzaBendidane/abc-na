<?php

/**
 * Module Backoffice Numbate
 * Users controller 
 * @author romain.causse@surikate.com  
 */


class Backoffice_PushController extends Class_Controller_BackofficeAction
{
    /**
     * API user
     */
    protected $_pushApi;


    public function preDispatch() {
        parent::preDispatch();
        
        $this->_pushApi = new Api_Push();
        $this->view->sidebar = array('display' => 'horizontal');
    }
    
    
    /**
     * Show all user
     */
    public function indexAction()
    {   
        $this->view->headTitle(_("Push list"));
        
        // champs a afficher
        $fields = array(
            "name",
            'message',
            'start_time',
            'state',
            'total_send'
        );

        $this->view->table = $this->view->widget('Table', $this->_pushApi->GetAllPush(), array('crud' => true, 'fields' => $fields,"action" => array("add","update")));
    }
    
    
    /**
     * Show all user
     */
    public function pushtestAction()
    {   
        $this->view->headTitle(_("Push test list"));
        
        // champs a afficher
        $fields = array(
            "name",
            'device_name',
            'start_date',
            'message'
        );

        $this->view->table = $this->view->widget('Table', $this->_pushApi->GetAllPushTest(), array('crud' => true, 'fields' => $fields,"action" => array("add")));
    }
    
    
    /**
     * Show all user
     */
    public function versionAction()
    {   
        $this->view->headTitle(_("Push version list"));
        
        // champs a afficher
        $fields = array(
            "name",
            'certificate_prod',
            'certificate_dev',
            'rate'
        );
        $versions = $this->_pushApi->GetAllPushVersion();
        foreach ($versions as $key => $version){
            if(file_exists($version["certificate_prod"])){
                $versions[$key]["certificate_prod"] = '<div id="valided"></div>';
            }else{
                $versions[$key]["certificate_prod"] = '<div id="rejected"></div>';
            }
            if(file_exists($version["certificate_dev"])){
                $versions[$key]["certificate_dev"] = '<div id="valided"></div>';
            }else{
                $versions[$key]["certificate_dev"] = '<div id="rejected"></div>';
            }
        }
     
        $this->view->table = $this->view->widget('Table', $versions, array('crud' => true, 'fields' => $fields,"action" => array("add","update")));
    }
    
    
    /**
     * Show all user
     */
    public function deviceAction()
    {   
        $this->view->headTitle(_("Push device list"));
        
        // champs a afficher
        $fields = array(
            "name",
            'token'
        );

        $this->view->table = $this->view->widget('Table', $this->_pushApi->GetAllPushDevice(), array('crud' => true, 'fields' => $fields,"action" => array("add","update","delete")));
    }
        
    /**
     * add a user
     */
    public function deviceaddAction()
    {   
        $this->view->headTitle(_("Add Device"));
        
        $form = new Class_Form_Bootstrap_PushDevice();
        $this->view->form = $form;

        $request = $this->getRequest();
        if($request->isPost()){
            if( $form->isValid($request->getPost())){
                    
                 $data = $request->getPost('data');
                 
                 $this->_pushApi->CreateDeviceTest($data);
                 
                 $this->_helper->redirector('device', 'push', 'Backoffice');
            }
            
        }
    }
    
    /**
     * update a user
     */
    public function deviceupdateAction()
    {   
        $device_data = $this->_pushApi->GetDeviceTestById($this->_request->getParam('id'));
        $this->view->headTitle(_("Device : ") . $device_data['name']);
        
        $form = new Class_Form_Bootstrap_PushDevice();
        $data = array('data' => $device_data);
        $form->populate($data);
        
        $this->view->form = $form;

        $request = $this->getRequest();
        if($request->isPost()){
            if( $form->isValid($request->getPost())){

                 $data = $request->getPost('data');
                 
                 $this->_pushApi->updateDeviceTest($data, $device_data['id']);
                 
                 $this->_helper->redirector('device', 'push', 'Backoffice');
            }
            
        }
        
        
    }
    
    /**
     * update a user
     */
    public function versionupdateAction()
    {   
        $version_data = $this->_pushApi->GetVersionById($this->_request->getParam('id'));
        $this->view->headTitle(_("Version : ") . $version_data['name']);

        $this->view->data = $version_data;

        $request = $this->getRequest();
        if($request->isPost()){
            $data = $_FILES;

            $path = $_FILES['certificate_prod']['name'];
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            if($ext == "pem"){
                $target_path_prod = $this->_config->certificat_prod."$version_data[id]/";
                if(!is_dir($target_path_prod)){
                    mkdir($target_path_prod,0777,true);
                }
                $target_path_prod = $target_path_prod . basename( $_FILES['certificate_prod']['name']); 
                move_uploaded_file($_FILES['certificate_prod']['tmp_name'], $target_path_prod);
            }else{
                $this->view->error = true;
                $this->view->message = "Certificat prod extension must be in .pem";
                return;
            }
            
            $path = $_FILES['certificate_dev']['name'];
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            if($ext == "pem"){
                $target_path_dev = $this->_config->certificat_dev."$version_data[id]/";
                if(!is_dir($target_path_dev)){
                    mkdir($target_path_dev,0777,true);
                }
                $target_path_dev = $target_path_dev . basename( $_FILES['certificate_dev']['name']); 
                move_uploaded_file($_FILES['certificate_dev']['tmp_name'], $target_path_dev);
            }else{
                $this->view->error = true;
                $this->view->message = "Certificat dev extension must be in .pem";
                return;
            }
            

            $data = $request->getPost('data');
            $data['certificate_dev'] = $target_path_dev;
            $data['certificate_prod'] = $target_path_prod;
            $this->_pushApi->UpdateVersion($data, $version_data['id']);

            $this->_helper->redirector('version', 'push', 'Backoffice');
            
        }
        
        
    }
    
    /**
     * update a user
     */
    public function pushupdateAction()
    {   
        
        $push = $this->_pushApi->GetPushById($this->_request->getParam('id'));
        
        $versions = $this->_pushApi->GetAllPushVersion();

        //$this->view->headTitle(_("User : ") . $user_data['firstname'] . ' ' . $user_data['lastname']);

        $this->view->data = $push;
        $this->view->versions = $versions;

        $request = $this->getRequest();
        if($request->isPost()){
            $data = $request->getPost('data');
            die(var_dump($data));
            $data['start_time'] = date('Y-m-d H:i:s',strtotime($data['start_time']));
            $this->_pushApi->UpdatePipAll($data, $push['id']);
            $this->_helper->redirector('index', 'push', 'Backoffice');
        }
    }
    
    public function devicedeleteAction(){
        $id = $this->_request->getParam('id');
        $this->view->id = $id;
        if($this->_request->isPost()){
            if($this->_request->getParam('valide')){
                $this->_pushApi->DeleteDevice($this->_request->getParam('id'));
                $this->_helper->redirector('device', 'push', 'Backoffice');
            }else{
                $this->_helper->redirector('device', 'push', 'Backoffice');
            }
        }
        
        
    }


    /**
     * Disconnect and clear session
     */
    public function disconnectAction(){
        Zend_Auth::getInstance()->clearIdentity();
        //redirects
        $this->_helper->redirector('index', 'login', 'Backoffice');
    }
    
        
    /**
     * add a user
     */
    public function versionaddAction()
    {   
        $dataCert = array();
        $this->view->headTitle(_("Add Version"));

        $request = $this->getRequest();
        if($request->isPost()){
            

            $data = $request->getPost('data');
            $lastInsert = $this->_pushApi->InsertVersion($data);
            
            
            $data = $_FILES;

            $path = $_FILES['certificate_prod']['name'];
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            if($ext == "pem"){
                $target_path_prod = $this->_config->certificat_prod."$lastInsert/";
                if(!is_dir($target_path_prod)){
                    mkdir($target_path_prod,0777,true);
                }
                $target_path_prod = $target_path_prod . basename( $_FILES['certificate_prod']['name']); 
                move_uploaded_file($_FILES['certificate_prod']['tmp_name'], $target_path_prod);
            }else{
                $this->view->error = true;
                $this->view->message = "Certificat prod extension must be in .pem";
                return;
            }
            
            $path = $_FILES['certificate_dev']['name'];
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            if($ext == "pem"){
                $target_path_dev = $this->_config->certificat_dev."$lastInsert/";
                if(!is_dir($target_path_dev)){
                    mkdir($target_path_dev,0777,true);
                }
                $target_path_dev = $target_path_dev . basename( $_FILES['certificate_dev']['name']); 
                move_uploaded_file($_FILES['certificate_dev']['tmp_name'], $target_path_dev);
            }else{
                $this->view->error = true;
                $this->view->message = "Certificat dev extension must be in .pem";
                return;
            }
            
            $dataCert['certificate_dev'] = $target_path_dev;
            $dataCert['certificate_prod'] = $target_path_prod;
            $this->_pushApi->UpdateVersion($dataCert, $lastInsert);

            $this->_helper->redirector('version', 'push', 'Backoffice');
            
        }
    }
    
    
        
    /**
     * add a user
     */
    public function pushtestaddAction()
    {   
        $this->view->headTitle(_("Add Push Test"));
        
        $form = new Class_Form_Bootstrap_PushTest();
        $this->view->form = $form;

        $request = $this->getRequest();
        if($request->isPost()){
            if( $form->isValid($request->getPost())){
                    
                 $data = $request->getPost('data');
                 
                 $this->_pushApi->CreatePushTest($data);
                 
                $this->_helper->redirector('pushtest', 'push', 'Backoffice');
            }
            
        }
    }
    
    /**
     * update a user
     */
    public function pushaddAction()
    {   
        $versions = $this->_pushApi->GetAllPushVersion();

        //$this->view->headTitle(_("User : ") . $user_data['firstname'] . ' ' . $user_data['lastname']);

        $this->view->versions = $versions;

        $request = $this->getRequest();
        if($request->isPost()){
            $data = $request->getPost('data');
            $data['start_time'] = date('Y-m-d H:i:s',strtotime($data['start_time']));
            $this->_pushApi->CreatePushPip($data);
            $this->_helper->redirector('index', 'push', 'Backoffice');
        }
    }
    
    public function simulerAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $totalPush = $this->_pushApi->GetTotalPush($data['version_id']);
            echo json_encode($totalPush);
        }
    }
}