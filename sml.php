<?php

function Obis ($data)
    {
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
