<?php

if (!defined('VIRGO_API_DIR')) {
	define("VIRGO_API_DIR", "virgo_api");
}

require_once(VIRGO_API_DIR . "/virgo_api.php");
require_once 'functions.php';

$api = new VirgoAPI();
if(isset($_GET['offers'])){
    $count = $api->SynchronizeDB(true);
    echo "Synchronizing database completed ($count).";
}else if(isset($_GET['graphics'])){
    $ret = $api->SynchronizeGraphics();
    echo "Synchronizing graphics completed:<br />$ret";
}else if(isset($_GET['clearphotos'])){
    $idofe = $idusr = $idodd = 0;
    if (isset($_GET['idofe'])) $idofe = $_GET['idofe'];
    if (isset($_GET['idusr'])) $idusr = $_GET['idusr'];
    if (isset($_GET['idodd'])) $idodd = $_GET['idodd'];
    if($idofe>0){
        $ret = $api->ClearPhotos($idofe);
    }elseif($idusr > 0){
        $ret = $api->ClearWebPhotos($idusr);
    }elseif($idodd > 0){
        $ret = $api->ClearWebPhotos(0,$idodd);
    }
    
    echo "Deleteing photos completed:<br />$ret";
}else if(isset($_GET['index'])){
    $gp = new GaleriePozycje();
    $gp->IndeksujGaleriePozycjeDlaArtykulow();
}else{
    $ret = $api->SynchronizeSite();
    //Arkusze i skrypty do poprawki (zapis przy update strony)
    //if(!strpos("Arkusze/JS: 0",$ret)){
        arkuszeCSS("screen");
        arkuszeCSS("print");
        arkuszeCSS("screen",true);
        scriptsJS();
    //}
    echo "Synchronizing site completed:<br />$ret";
}



function arkuszeCSS($tryb='screen',$notatnik=false){
        $return_string = "";
        $as = new ArkuszeSkrypty();
        $ss = new Serwisy();
        $serwis = $ss->GetSerwis(Config::$WebGID, 1045);

        $hta = array();
        $hta['GIDSerwis']=$serwis->GetGID();
        if(!$notatnik){
            switch($tryb){
                case "screen":$hta['Rodzaj']="Podstawowy";break;
                case "print":$hta['Rodzaj']="DoDruku";break;
            }
        }else{
            $hta['Rodzaj']="Podstawowy";
            $hta['Opis']="wydruk_notatnik.aspx";
        }
        $arks = $as->PobierzArkusze($hta);

        foreach($arks as $ar){
            $return_string.=$ar->GetTresc();
        }
        //zamiana sciezki do grafiki
        $return_string = str_replace("grafika/", "../grafika/", $return_string);
        $return_string = str_replace("Grafika/", "../grafika/", $return_string);
        //zamiana handlerow webi
        $return_string = str_replace("webi.ashx?", "../grafika/", $return_string);
        if($notatnik) $fh = fopen($_SERVER['DOCUMENT_ROOT'].Config::$AppPath."/css/notatnik_wydruk.css", "w");
        else $fh = fopen($_SERVER['DOCUMENT_ROOT'].Config::$AppPath."/css/outer_".$tryb.".css", "w");
        fwrite($fh,$return_string);
        fclose($fh);
        //echo $return_string;
    }
        
    function scriptsJS(){
        $return_string = "";
        $as = new ArkuszeSkrypty();
        $ss = new Serwisy();
        $serwis = $ss->GetSerwis(Config::$WebGID, 1045);

        $hta = array();
        $hta['GIDSerwis']=$serwis->GetGID();

        $arks = $as->PobierzSkrypty($hta);

        foreach($arks as $ar){
            $return_string.=$ar->GetTresc();
        }
        $fh = fopen($_SERVER['DOCUMENT_ROOT'].Config::$AppPath."/js/z-outer.js", "w");
        fwrite($fh,$return_string);
        fclose($fh);
    }
?>
