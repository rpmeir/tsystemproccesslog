<?php

namespace Rpmeir\TSystemProccessLog\App\Controllers;

use Adianti\Control\TAction;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TDateTime;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Wrapper\TDBCombo;
use Adianti\Control\TPage;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;

/**
 * SystemProccessLogForm Master/Detail
 * @author  <your name here>
 */
class SystemProccessLogForm extends TPage
{
    protected $form; // form
    protected $detail_list;
    protected $database;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_SystemProccessLog');
        $this->form->setFormTitle('System Proccess Log');
        $this->form->setFieldSizes('100%');
        $this->database = TSession::getValue('unit_database');
        
        // master fields
        $id = new TEntry('id');
        $system_user_id = new TDBCombo('system_user_id', $this->database, 'SystemUser', 'id', 'name');
        $system_proccess_log_id = new TDBCombo('system_proccess_log_id', $this->database, 'SystemProccessLog', 'id', 'system_user_id');
        $start_time = new TEntry('start_time');
        $end_time = new TDateTime('end_time');
        $class_name = new TEntry('class_name');
        $method_name = new TEntry('method_name');

        $system_user_id->setEditable(false);
        $system_proccess_log_id->setEditable(false);
        $start_time->setEditable(false);
        $end_time->setEditable(false);
        $class_name->setEditable(false);
        $method_name->setEditable(false);

        $start_time->setValueCallback(function($value){
            return $value . 'x';
        });

        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        // master fields
        $row = $this->form->addFields( 
            [new TLabel('Id'), $id], 
            [new TLabel('System User'), $system_user_id], 
            [new TLabel('Parent Proccess'), $system_proccess_log_id]
        );
        $row->layout = ['col-3 col-md-2','col-3','col-3 col-md-2'];

        $row = $this->form->addFields( 
            [new TLabel('Start Time'), $start_time], 
            [new TLabel('End Time'), $end_time], 
            [new TLabel('Class'), $class_name], 
            [new TLabel('Method'), $method_name]
        );
        $row->layout = ['col-3','col-3','col-3','col-3'];
        
        // detail fields
        $this->form->addContent( ['<h4>Mensagens</h4><hr>'] );
        
        $this->detail_list = new BootstrapDatagridWrapper(new TDataGrid);
        $this->detail_list->setId('SystemMessageLog_list');
        $this->detail_list->generateHiddenFields();
        $this->detail_list->style = "min-width: 700px; width:100%;margin-bottom: 10px";
        
        // items
        $this->detail_list->addColumn( new TDataGridColumn('uniqid', 'Uniqid', 'center') )->setVisibility(false);
        $this->detail_list->addColumn( new TDataGridColumn('id', 'Id', 'center') )->setVisibility(false);
        $typ = $this->detail_list->addColumn( new TDataGridColumn('registry_type', 'Registry Type', 'left', 100) );
        $this->detail_list->addColumn( new TDataGridColumn('message', 'Message', 'left', 600) );

        $this->detail_list->createModel();
        
        $panel = new TPanelGroup;
        $panel->add($this->detail_list);
        $panel->getBody()->style = 'overflow-x:auto';
        $this->form->addContent( [$panel] );
        
        $this->form->addHeaderActionLink('Voltar', new TAction(array('SystemProccessLogList', 'onReload')), 'fa:arrow-left blue');

        // create the page container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        parent::add($container);
    }
    
    /**
     * Load Master/Detail data from database to form
     */
    public function onEdit($param)
    {
        try
        {
            TTransaction::open($this->database);
            
            if (isset($param['key']))
            {
                $key = $param['key'];
                
                $object = new SystemProccessLog($key);
                $object->start_time = date('d/m/Y H:i:s', $object->start_time);
                $object->end_time = date('d/m/Y H:i:s', $object->end_time);

                $items  = SystemMessageLog::where('system_proccess_log_id', '=', $key)->load();
                
                foreach( $items as $item )
                {
                    $item->uniqid = uniqid();
                    $row = $this->detail_list->addItem( $item );
                    $row->id = $item->uniqid;
                }
                $this->form->setData($object);
                TTransaction::close();
            }
            else
            {
                $this->form->clear(TRUE);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
}
