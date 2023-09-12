<?php

namespace Rpmeir\TSystemProccessLog\App\Models;

use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRecord;
use Adianti\Registry\TSession;
use Adianti\Widget\Dialog\TMessage;

/**
 * SystemProccessLog Active Record
 */
class SystemProccessLog extends TRecord
{
    const TABLENAME = 'system_proccess_log';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    private $system_user;
    private $system_proccess_log;
    private $system_message_logs;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('start_time');
        parent::addAttribute('end_time');
        parent::addAttribute('class_name');
        parent::addAttribute('method_name');
        parent::addAttribute('system_user_id');
        parent::addAttribute('system_proccess_log_id');
    }

    
    /**
     * Method set_system_user
     * Sample of usage: $system_proccess_log->system_user = $object;
     * @param $object Instance of SystemUser
     */
    public function set_system_user(SystemUser $object)
    {
        $this->system_user = $object;
        $this->system_user_id = $object->id;
    }
    
    /**
     * Method get_system_user
     * Sample of usage: $system_proccess_log->system_user->attribute;
     * @return SystemUser instance
     */
    public function get_system_user()
    {
        // loads the associated object
        if (empty($this->system_user))
            $this->system_user = new SystemUser($this->system_user_id);
    
        // returns the associated object
        return $this->system_user;
    }
    
    
    /**
     * Method set_system_proccess_log
     * Sample of usage: $system_proccess_log->system_proccess_log = $object;
     * @param $object Instance of SystemProccessLog
     */
    public function set_system_proccess_log(SystemProccessLog $object)
    {
        $this->system_proccess_log = $object;
        $this->system_proccess_log_id = $object->id;
    }
    
    /**
     * Method get_system_proccess_log
     * Sample of usage: $system_proccess_log->system_proccess_log->attribute;
     * @return SystemProccessLog instance
     */
    public function get_system_proccess_log()
    {
        // loads the associated object
        if (empty($this->system_proccess_log))
            $this->system_proccess_log = new SystemProccessLog($this->system_proccess_log_id);
    
        // returns the associated object
        return $this->system_proccess_log;
    }
    
    
    /**
     * Method addSystemMessageLog
     * Add a SystemMessageLog to the SystemProccessLog
     * @param $object Instance of SystemMessageLog
     */
    public function addSystemMessageLog(SystemMessageLog $object)
    {
        $this->system_message_logs[] = $object;
    }
    
    /**
     * Method getSystemMessageLogs
     * Return the SystemProccessLog' SystemMessageLog's
     * @return Collection of SystemMessageLog
     */
    public function getSystemMessageLogs()
    {
        return $this->system_message_logs;
    }

    
    /**
     * Method getSystemProccessLogs
     */
    public function getSystemProccessLogs()
    {
        $criteria = new TCriteria;
        $criteria->add(new TFilter('system_proccess_log_id', '=', $this->id));
        return SystemProccessLog::getObjects( $criteria );
    }
    
    /**
     * Register a new proccess starting
     * @param $class String with class name
     * @param $method String with method name
     * @return TRUE if the password matches, otherwise throw Exception
     */
    public static function start($class, $method)
    {
        try {
            //code...
            $processo = new SystemProccessLog;
            $processo->system_user_id = TSession::getValue('userid');
            $processo->start_time = time();
            $processo->class_name = $class;
            $processo->method_name = $method;
            $processo->store();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
        
        return $processo;
    }

    
    /**
     * Ends a proccess started
     * @param $id Integer id of proccess
     */
    public static function end($id)
    {
        try {
            SystemProccessLog::where('id', '=', $id)
                            ->set('end_time', time())
                            ->update();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }
    

    /**
     * Reset aggregates
     */
    public function clearParts()
    {
        $this->system_message_logs = array();
    }

    /**
     * Load the object and its aggregates
     * @param $id object ID
     */
    public function load($id)
    {
        $this->system_message_logs = parent::loadComposite('SystemMessageLog', 'system_proccess_log_id', $id);
    
        // load the object itself
        return parent::load($id);
    }

    /**
     * Store the object and its aggregates
     */
    public function store()
    {
        // store the object itself
        parent::store();
    
        // parent::saveComposite('SystemMessageLog', 'system_proccess_log_id', $this->id, $this->system_message_logs);
    }

    /**
     * Delete the object and its aggregates
     * @param $id object ID
     */
    public function delete($id = NULL)
    {
        $id = isset($id) ? $id : $this->id;
        parent::deleteComposite('SystemMessageLog', 'system_proccess_log_id', $id);
    
        // delete the object itself
        parent::delete($id);
    }

}
