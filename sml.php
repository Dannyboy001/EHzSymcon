<?php

$Obis = array ('8181C78203FF', '0100000009FF', '0100010800FF', '0100010801FF', '0100010802FF', '01000F0700FF', '0100150700FF', '0100290700FF', '01003D0700FF', '0100011100FF', '8181C78205FF', '010060320002', '010060320003', '010060320004', '010060320005', '010060320303', '010060320304', '01001F0700FF', '0100200700FF', '0100330700FF', '0100340700FF', '0100470700FF', '0100480700FF');
$Obisname = array ('Hersteller-Identifikation', 'Geräteeinzelidentifikation', 'Zählerstand Totalregister', 'Zählerstand Tarif 1', 'Zählerstand Tarif 2', 'aktuelle Wirkleistung', 'Wirkleistung L1', 'Wirkleistung L2', 'Wirkleistung L3', 'nur rückseitige Schnittstelle', 'öffentlicher Schlüssel', 'Aktuelle Chiptemperatur', 'Minimale Chiptemperatur', 'Maximale Chiptemperatur', 'Gemittelte Chiptemperatur', 'Spannungsminimum', 'Spannungsmaximum', 'Strom L1', 'Spannung L1', 'Strom L2', 'Spannung L2', 'Strom L3', 'Spannung L3');
$Obisvartyp = array (3,3,2,2,2,2,2,2,2,3,3,2,2,2,2,2,2,2,2,2,2,2,2);
$Obisprofile = array ('~String', '~String', '~Electricity', '~Electricity', '~Electricity', '~Power', '~Power', '~Power', '~Power', '~String', '~String', '~Temperature', '~Temperature', '~Temperature', '~Temperature', '~Volt', '~Volt', '~Ampere', '~Volt', '~Ampere', '~Volt', '~Ampere', '~Volt');    

function Obis ($typ, $offset, $value)
    {
    $Obis[0] = array ('Hersteller-Identifikation','8181C78203FF',3,'~String');
    }
    
    
function CheckSML($stream, $parentID)
    {
    for($i = 0; $i < count($Obisname); $i++)
        {
            $var = stristr($stream, $Obis[$i]);
            if ($var != false)
            {
                CheckVariableTYP($Obisname[$i], $Obisvartyp[$i], $Obisprofile[$i], $parentID);
                //IPS_LogMessage($Obisname[$i], $var);
            }
            else
            {
                //IPS_LogMessage('EHz', $stream.'  :  '.$var);
            }
        }
    }
function CheckVariableTYP($name, $vartyp, $profile, $parentID)
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
