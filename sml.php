<?php

$Obis = array ('8181C78203FF', '0100000009FF', '0100010800FF', '0100010801FF', '0100010802FF', '01000F0700FF', '0100150700FF', '0100290700FF', '01003D0700FF', '0100011100FF', '8181C78205FF', '010060320002', '010060320003', '010060320004', '010060320005', '010060320303', '010060320304', '01001F0700FF', '0100200700FF', '0100330700FF', '0100340700FF', '0100470700FF', '0100480700FF');
$Obisname = array ('Hersteller-Identifikation', 'Geräteeinzelidentifikation', 'Zählerstand Totalregister', 'Zählerstand Tarif 1', 'Zählerstand Tarif 2', 'aktuelle Wirkleistung', 'Wirkleistung L1', 'Wirkleistung L2', 'Wirkleistung L3', 'nur rückseitige Schnittstelle', 'öffentlicher Schlüssel', 'Aktuelle Chiptemperatur', 'Minimale Chiptemperatur', 'Maximale Chiptemperatur', 'Gemittelte Chiptemperatur', 'Spannungsminimum', 'Spannungsmaximum', 'Strom L1', 'Spannung L1', 'Strom L2', 'Spannung L2', 'Strom L3', 'Spannung L3');
$Obisvartyp = array (3,3,2,2,2,2,2,2,2,3,3,2,2,2,2,2,2,2,2,2,2,2,2);
$Obisprofile = array ('~String', '~String', '~Electricity', '~Electricity', '~Electricity', '~Power', '~Power', '~Power', '~Power', '~String', '~String', '~Temperature', '~Temperature', '~Temperature', '~Temperature', '~Volt', '~Volt', '~Ampere', '~Volt', '~Ampere', '~Volt', '~Ampere', '~Volt');    

function EHz($dataset, $value)
    {
    $EHz[0] = array ('EMH',6);
    $EHz[1] = array ('Hersteller-Identifikation','8181C78203FF',3,'~String');
    $EHz[2] = array ('Geräteeinzelidentifikation','0100000009FF',3,'~String');
    $EHz[3] = array ('Zählerstand Totalregister','0100010800FF',2,'~Electricity');
    $EHz[4] = array ('Zählerstand Tarif 1','0100010801FF',2,'~Electricity');
    $EHz[5] = array ('Zählerstand Tarif 2','0100010802FF',2,'~Electricity');
    $EHz[6] = array ('öffentlicher Schlüssel','8181C78205FF',3,'~String');
    
    $name = $EHz[$dataset][$value];
    if ($name === false) 
        {
        IPS_LogMessage('EHz', 'Datentyp :'.$dataset.' : '.$value.'existiert nicht !');
        return false;
        }
    return $name;
    }
    
function CheckSML($stream, $parentID)
    {
    IPS_LogMessage('EHz <- Port:', $stream);
    for($i = 1; $i < count (6); $i++)
        {
            $var = stristr($stream, EHz($i,1));
            if ($var != false)
            {
              CheckVariable(EHz($i,0), EHz($i,2),EHz($i,3), $parentID);
              IPS_LogMessage('EHz', $i);
            }
            else
            {
              IPS_LogMessage('EHz', $i.'  Error ');
            }
        }
    return true;
    }
    
function CheckVariable($name, $vartyp, $profile, $parentID)
    {
        $InstanzID = @IPS_GetVariableIDByName($name, $parentID);
        if ($InstanzID === false)
        {
            $InstanzID = IPS_CreateVariable($vartyp);
            IPS_SetName($InstanzID, $name); // Instanz benennen
            IPS_SetParent($InstanzID, $parentID);
            IPS_SetVariableCustomProfile($InstanzID, $profile);
        }
        //echo "ID: ".$InstanzID." ".$name."\n";
    return $InstanzID;
    }
?>
