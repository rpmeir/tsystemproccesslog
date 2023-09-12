<?php

namespace Rpmeir\TSystemProccessLog\App\Controllers;

use Adianti\Control\TAction;
use Adianti\Database\TFilter;
use Adianti\Registry\TSession;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Datagrid\TPageNavigation;
use Adianti\Widget\Form\TDateTime;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\TDropDown;
use Adianti\Widget\Wrapper\TDBUniqueSearch;
use Adianti\Control\TPage;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;

/**
 * SystemProccessLogList Listing
 * @author  <your name here>
 */
class SystemProccessLogList extends TPage
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    protected $formgrid;
    protected $deleteButton;
    
    use Adianti\base\AdiantiStandardListTrait;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setDatabase(TSession::getValue('unit_database'));            // defines the database
        $this->setActiveRecord('SystemProccessLog');   // defines the active record
        $this->setDefaultOrder('id', 'desc');         // defines the default order
        $this->setLimit(10);
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('system_user_id', '=', 'system_user_id'); // filterField, operator, formField
        $this->addFilterField('system_proccess_log_id', '=', 'system_proccess_log_id'); // filterField, operator, formField
        $this->addFilterField('start_time', '>=', 'start_time'); // filterField, operator, formField
        $this->addFilterField('end_time', '<=', 'end_time'); // filterField, operator, formField
        $this->addFilterField('class_name', 'like', 'class_name'); // filterField, operator, formField
        $this->addFilterField('method_name', 'like', 'method_name'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_SystemProccessLog');
        $this->form->setFormTitle('System Proccess Log');
        $this->form->setFieldSizes('100%');

        // create the form fields
        $id = new TEntry('id');
        $system_user_id = new TDBUniqueSearch('system_user_id', $this->database, 'SystemUser', 'id', 'name');
        $system_proccess_log_id = new TDBUniqueSearch('system_proccess_log_id', $this->database, 'SystemProccessLog', 'id', 'system_user_id');
        $start_time = new TDateTime('start_time');
        $end_time = new TDateTime('end_time');
        $class_name = new TEntry('class_name');
        $method_name = new TEntry('method_name');

        $system_user_id->setMinLength(0);
        $system_proccess_log_id->setMinLength(2);
        $start_time->setMask('dd/mm/yyyy hh:ii');
        $start_time->setDatabaseMask('yyyy-mm-dd hh:ii');
        $end_time->setMask('dd/mm/yyyy hh:ii');
        $end_time->setDatabaseMask('yyyy-mm-dd hh:ii');

        // add the fields
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

        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new TAction(['SystemProccessLogForm', 'onEdit']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'left');
        $column_system_user_id = new TDataGridColumn('system_user->login', 'System User', 'left');
        $column_start_time = new TDataGridColumn('start_time', 'Start Time', 'left');
        $column_end_time = new TDataGridColumn('end_time', 'End Time', 'left');
        $column_class_name = new TDataGridColumn('class_name', 'Class', 'left');
        $column_method_name = new TDataGridColumn('method_name', 'Method', 'left');
        $column_system_proccess_log_id = new TDataGridColumn('system_proccess_log_id', 'Parent', 'left');

        $column_start_time->setTransformer(function($value){
            if($value)
            {
                return date('d/m/Y H:i:s', $value);
            }
        });

        $column_end_time->setTransformer(function($value){
            if($value)
            {
                return date('d/m/Y H:i:s', $value);
            }
        });

        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_system_user_id);
        $this->datagrid->addColumn($column_start_time);
        $this->datagrid->addColumn($column_end_time);
        $this->datagrid->addColumn($column_class_name);
        $this->datagrid->addColumn($column_method_name);
        $this->datagrid->addColumn($column_system_proccess_log_id);


        // creates the datagrid column actions
        $column_system_user_id->setAction(new TAction([$this, 'onReload']), ['order' => 'system_user_id']);
        $column_system_proccess_log_id->setAction(new TAction([$this, 'onReload']), ['order' => 'system_proccess_log_id']);
        $column_start_time->setAction(new TAction([$this, 'onReload']), ['order' => 'start_time']);
        $column_class_name->setAction(new TAction([$this, 'onReload']), ['order' => 'class_name']);
        $column_method_name->setAction(new TAction([$this, 'onReload']), ['order' => 'method_name']);

        
        $action1 = new TDataGridAction(['SystemProccessLogForm', 'onEdit'], ['id'=>'{id}']);
        $action2 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        
        $this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
        $this->datagrid->addAction($action2 ,_t('Delete'), 'far:trash-alt red');
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        
        $panel = new TPanelGroup('', 'white');
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);
        
        // header actions
        $dropdown = new TDropDown(_t('Export'), 'fa:list');
        $dropdown->setPullSide('right');
        $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        $dropdown->addAction( _t('Save as CSV'), new TAction([$this, 'onExportCSV'], ['register_state' => 'false', 'static'=>'1']), 'fa:table blue' );
        $dropdown->addAction( _t('Save as PDF'), new TAction([$this, 'onExportPDF'], ['register_state' => 'false', 'static'=>'1']), 'far:file-pdf red' );
        $panel->addHeaderWidget( $dropdown );
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);
        
        parent::add($container);
    }
    
    /**
     * Register the filter in the session
     */
    public function onSearch()
    {
        // get the search form data
        $data = $this->form->getData();
        
        // clear session filters
        TSession::setValue(__CLASS__.'_filter_id',   NULL);
        TSession::setValue(__CLASS__.'_filter_system_user_id',   NULL);
        TSession::setValue(__CLASS__.'_filter_system_proccess_log_id',   NULL);
        TSession::setValue(__CLASS__.'_filter_start_time',   NULL);
        TSession::setValue(__CLASS__.'_filter_end_time',   NULL);
        TSession::setValue(__CLASS__.'_filter_class_name',   NULL);
        TSession::setValue(__CLASS__.'_filter_method_name',   NULL);

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', '=', $data->id); // create the filter
            TSession::setValue(__CLASS__.'_filter_id',   $filter); // stores the filter in the session
        }


        if (isset($data->system_user_id) AND ($data->system_user_id)) {
            $filter = new TFilter('system_user_id', '=', $data->system_user_id); // create the filter
            TSession::setValue(__CLASS__.'_filter_system_user_id',   $filter); // stores the filter in the session
        }


        if (isset($data->system_proccess_log_id) AND ($data->system_proccess_log_id)) {
            $filter = new TFilter('system_proccess_log_id', '=', $data->system_proccess_log_id); // create the filter
            TSession::setValue(__CLASS__.'_filter_system_proccess_log_id',   $filter); // stores the filter in the session
        }


        if (isset($data->start_time) AND ($data->start_time)) {
            $filter = new TFilter('start_time', 'like', "%{$data->start_time}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_start_time',   $filter); // stores the filter in the session
        }


        if (isset($data->end_time) AND ($data->end_time)) {
            $filter = new TFilter('end_time', 'like', "%{$data->end_time}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_end_time',   $filter); // stores the filter in the session
        }


        if (isset($data->class_name) AND ($data->class_name)) {
            $filter = new TFilter('class_name', 'like', "%{$data->class_name}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_class_name',   $filter); // stores the filter in the session
        }


        if (isset($data->method_name) AND ($data->method_name)) {
            $filter = new TFilter('method_name', 'like', "%{$data->method_name}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_method_name',   $filter); // stores the filter in the session
        }

        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue(__CLASS__ . '_filter_data', $data);
        
        $param = array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
    }
    
}
