<?php
require_once(__DIR__ . "/../sml.php");  // diverse Klassen
    $Obis[0] = array ('8181C78203FF'); //Hersteller-Identifikation
    $Obis[1] = array ('0100000009FF'); //Geräteeinzelidentifikation / Server-ID
    $Obis[2] = array ('0100010800FF'); //Zählerstand Totalregister
    $Obis[3] = array ('0100010801FF'); //Zählerstand Tarif 1
    $Obis[4] = array ('0100010802FF'); //Zählerstand Tarif 2
    $Obis[5] = array ('01000F0700FF'); //aktuelle Wirkleistung
    $Obis[6] = array ('0100150700FF'); //: Wirkleistung L1
    $Obis[7] = array ('0100290700FF'); //: Wirkleistung L2
    $Obis[8] = array ('01003D0700FF'); //: Wirkleistung L3
    $Obis[9] = array ('0100011100FF'); //(nur rückseitige Schnittstelle)
    $Obis[10] = array ('8181C78205FF'); //öffentlicher Schlüssel 
    $Obis[11] = array ('010060320002'); //: Aktuelle Chiptemperatur
    $Obis[12] = array ('010060320003'); //: Minimale Chiptemperatur
    $Obis[13] = array ('010060320004'); //: Maximale Chiptemperatur
    $Obis[14] = array ('010060320005'); //: Gemittelte Chiptemperatur
    $Obis[15] = array ('010060320303'); //: Spannungsminimum
    $Obis[16] = array ('010060320304'); //: Spannungsmaximum
    $Obis[17] = array ('01001F0700FF'); //: Strom L1
    $Obis[18] = array ('0100200700FF'); //: Spannung L1
    $Obis[19] = array ('0100330700FF'); //: Strom L2
    $Obis[20] = array ('0100340700FF'); //: Spannung L2
    $Obis[21] = array ('0100470700FF'); //: Strom L3
    $Obis[22] = array ('0100480700FF'); //: Spannung L3 
class EHz extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->RequireParent("{3CFF0FD9-E306-41DB-9B5A-9D06D38576C3}", "Socket EHz");
        $this->RegisterPropertyString("Name", "Iskra");
        $this->RegisterPropertyString("Host", "192.168.178.4");
        $this->RegisterPropertyBoolean("Open", true);
        $this->RegisterPropertyInteger("Port", 10002);
    }
    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
        $change = false;
        // Zwangskonfiguration des ClientSocket
        $ParentID = $this->GetParent();
        if (!($ParentID === false))
        {
            if (IPS_GetProperty($ParentID, 'Host') <> $this->ReadPropertyString('Host'))
            {
                IPS_SetProperty($ParentID, 'Host', $this->ReadPropertyString('Host'));
                $change = true;
            }
            if (IPS_GetProperty($ParentID, 'Port') <> $this->ReadPropertyInteger('Port'))
            {
                IPS_SetProperty($ParentID, 'Port', $this->ReadPropertyInteger('Port'));
                $change = true;
            }
            $ParentOpen = $this->ReadPropertyBoolean('Open');
            // Keine Verbindung erzwingen wenn Host leer ist, sonst folgt später Exception.
         
            if (IPS_GetProperty($ParentID, 'Open') <> $ParentOpen)
            {
                IPS_SetProperty($ParentID, 'Open', $ParentOpen);
                $change = true;
            }
            if ($change)
                @IPS_ApplyChanges($ParentID);
        }
        /* Eigene Profile
        
        */
        //Workaround für persistente Daten der Instanz
                
        // Wenn wir verbunden sind, am Gateway mit listen anmelden für Events
        if (($this->ReadPropertyBoolean('Open'))
                and ( $this->HasActiveParent($ParentID)))
        {
             $this->SetStatus(102);                    
        }
        
    }
    
################## PRIVATE     
    private function CheckParents()
    {
        $result = $this->HasActiveParent();
        if ($result)
        {
            $instance = IPS_GetInstance($this->InstanceID);
            $parentGUID = IPS_GetInstance($instance['ConnectionID'])['ModuleInfo']['ModuleID'];
            if ($parentGUID == '{3CFF0FD9-E306-41DB-9B5A-9D06D38576C3}')
            {
                IPS_DisconnectInstance($this->InstanceID);
                //IPS_LogMessage('EHz', 'EHz has invalid Parent.');
                $result = false;
            }
        }
        return $result;
    }
   
    
################## DATAPOINT RECEIVE
    
     public function ReceiveData($JSONString)
    {
        $data = json_decode($JSONString);
        //IPS_LogMessage('EHz <- Port:', bin2hex(utf8_decode($data->Buffer)));
        $stream = bin2hex(utf8_decode($data->Buffer));
        for($i = 0; $i < count($Obis); $i++)
        {
            $var = strstr($stream, $Obis[$i]);
            if ($var != false)
            {
                //CheckVariableTYP($name, $vartyp, $profile, $this->InstanceID)
                IPS_LogMessage('Obis'.$Obis[$i], $var);
            }
        }           
        return true;  
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