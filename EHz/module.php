<?php
require_once(__DIR__ . "/../sml.php");  // diverse Klassen

class EHz extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->RequireParent("{6DC3D946-0D31-450F-A8C6-C42DB8D7D4F1}", "EHz");
        $this->RegisterPropertyString("name", "Hauptzähler");
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
         //prüfen ob IO ein SerialPort ist
        //        
        // Zwangskonfiguration des SerialPort, wenn vorhanden und verbunden
        // Aber nie bei einem Neustart :)
        //if (IPS_GetKernelRunlevel() == KR_READY)
        //{
            $ParentID = $this->GetParent();
            if (!($ParentID === false))
            {
                $ParentInstance = IPS_GetInstance($ParentID);
                if ($ParentInstance['ModuleInfo']['ModuleID'] == '{6DC3D946-0D31-450F-A8C6-C42DB8D7D4F1}')
                {
                    if (IPS_GetProperty($ParentID, 'StopBits') <> '1')
                        IPS_SetProperty($ParentID, 'StopBits', '1');
                    if (IPS_GetProperty($ParentID, 'BaudRate') <> '9600')
                        IPS_SetProperty($ParentID, 'BaudRate', '9600');
                    if (IPS_GetProperty($ParentID, 'Parity') <> 'None')
                        IPS_SetProperty($ParentID, 'Parity', 'None');
                    if (IPS_GetProperty($ParentID, 'DataBits') <> '8')
                        IPS_SetProperty($ParentID, 'DataBits', '8');
                    if (IPS_HasChanges($ParentID))
                        IPS_ApplyChanges($ParentID);
                }
            }
        //}

    }
    
################## PRIVATE     
    private function CheckParents()
    {
        $result = $this->HasActiveParent();
        if ($result)
        {
            $instance = IPS_GetInstance($this->InstanceID);
            $parentGUID = IPS_GetInstance($instance['ConnectionID'])['ModuleInfo']['ModuleID'];
            if ($parentGUID == '{6DC3D946-0D31-450F-A8C6-C42DB8D7D4F1}')
            {
                IPS_DisconnectInstance($this->InstanceID);
                //IPS_LogMessage('Logamatic Gateway', 'Logamatic Gateway has invalid Parent.');
                $result = false;
            }
        }
        return $result;
    }
   
    
################## DATAPOINT RECEIVE
    
     public function ReceiveData($JSONString)
    {
        $data = json_decode($JSONString);
        IPS_LogMessage('EHz <- Cutter:', bin2hex(utf8_decode($data->Buffer)));
        $stream = bin2hex(utf8_decode($data->Buffer));
        
    }
    
################## DUMMYS / WOARKAROUNDS - protected
      
    protected function GetParent()
    {
        $instance = IPS_GetInstance($this->InstanceID);
        return ($instance['ConnectionID'] > 0) ? $instance['ConnectionID'] : false;
    }
    protected function HasActiveParent()
    {
//        IPS_LogMessage(__CLASS__, __FUNCTION__); //          
        $instance = IPS_GetInstance($this->InstanceID);
        if ($instance['ConnectionID'] > 0)
        {
            $parent = IPS_GetInstance($instance['ConnectionID']);
            if ($parent['InstanceStatus'] == 102)
                return true;
        }
        return false;
    }
    protected function RegisterTimer($Name, $Interval, $Script)
    {
        $id = @IPS_GetObjectIDByIdent($Name, $this->InstanceID);
        if ($id === false)
            $id = 0;
        if ($id > 0)
        {
            if (!IPS_EventExists($id))
                throw new Exception("Ident with name " . $Name . " is used for wrong object type");
            if (IPS_GetEvent($id)['EventType'] <> 1)
            {
                IPS_DeleteEvent($id);
                $id = 0;
            }
        }
        if ($id == 0)
        {
            $id = IPS_CreateEvent(1);
            IPS_SetParent($id, $this->InstanceID);
            IPS_SetIdent($id, $Name);
        }
        IPS_SetName($id, $Name);
        IPS_SetHidden($id, true);
        IPS_SetEventScript($id, $Script);
        if ($Interval > 0)
        {
            IPS_SetEventCyclic($id, 0, 0, 0, 0, 1, $Interval);
            IPS_SetEventActive($id, true);
        }
        else
        {
            IPS_SetEventCyclic($id, 0, 0, 0, 0, 1, 1);
            IPS_SetEventActive($id, false);
        }
    }
    protected function UnregisterTimer($Name)
    {
        $id = @IPS_GetObjectIDByIdent($Name, $this->InstanceID);
        if ($id > 0)
        {
            if (!IPS_EventExists($id))
                throw new Exception('Timer not present');
            IPS_DeleteEvent($id);
        }
    }
    protected function SetTimerInterval($Name, $Interval)
    {
        $id = @IPS_GetObjectIDByIdent($Name, $this->InstanceID);
        if ($id === false)
            throw new Exception('Timer not present');
        if (!IPS_EventExists($id))
            throw new Exception('Timer not present');
        $Event = IPS_GetEvent($id);
        if ($Interval < 1)
        {
            if ($Event['EventActive'])
                IPS_SetEventActive($id, false);
        }
        else
        {
            if ($Event['CyclicTimeValue'] <> $Interval)
                IPS_SetEventCyclic($id, 0, 0, 0, 0, 1, $Interval);
            if (!$Event['EventActive'])
                IPS_SetEventActive($id, true);
        }
    }
    protected function SetStatus($InstanceStatus)
    {
        if ($InstanceStatus <> IPS_GetInstance($this->InstanceID)['InstanceStatus'])
            parent::SetStatus($InstanceStatus);
    }
    ################## SEMAPHOREN Helper  - private  
    private function lock($ident)
    {
        for ($i = 0; $i < 100; $i++)
        {
            if (IPS_SemaphoreEnter("Logamatic_" . (string) $this->InstanceID . (string) $ident, 1))
            {
                return true;
            }
            else
            {
                IPS_Sleep(mt_rand(1, 5));
            }
        }
        return false;
    }
    private function unlock($ident)
    {
        IPS_SemaphoreLeave("Logamatic_" . (string) $this->InstanceID . (string) $ident);
    }
    
}
?>