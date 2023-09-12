<?php

namespace Rpmeir\TSystemProccessLog\App\Models;

use Adianti\Database\TRecord;
use Adianti\Widget\Dialog\TMessage;

/**
 * SystemMessageLog Active Record
 * @author  <your-name-here>
 */
class SystemMessageLog extends TRecord
{
    const TABLENAME = 'system_message_log';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    private $system_proccess_log;
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('message');
        parent::addAttribute('registry_type');
        parent::addAttribute('system_proccess_log_id');
    }

    
    /**
     * Method set_system_proccess_log
     * Sample of usage: $system_message_log->system_proccess_log = $object;
     * @param $object Instance of SystemProccessLog
     */
    public function set_system_proccess_log(SystemProccessLog $object)
    {
        $this->system_proccess_log = $object;
        $this->system_proccess_log_id = $object->id;
    }
    
    /**
     * Method get_system_proccess_log
     * Sample of usage: $system_message_log->system_proccess_log->attribute;
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
     * Register a new message
     * @param $proc_id Id of initial proccess
     * @param $message String with message
     * @param $reg_type String with registry type
     * @return TRUE if the password matches, otherwise throw Exception
     */
    public static function new($proc_id, $message, $reg_type = 'info')
    {
        try {
            if(!in_array($reg_type, ['error','warning','info','success']))
                throw new Exception("$reg_type não é um tipo aceito ['error','warning','info','success']", 1);
                
            $msg_log = new SystemMessageLog;
            $msg_log->system_proccess_log_id = $proc_id;
            $msg_log->message = $message;
            $msg_log->registry_type = $reg_type;
            $msg_log->store();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

}
