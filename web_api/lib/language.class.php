<?php

/**
 * Description of language.
 *
 * @author Jakub Konieczka
 */
class Language {
    
    private $_Id;
    private $_Name;

    public function GetId(){
		return $this->_Id;
	}

	public function SetId($value){
		$this->_Id = $value;
	}

    public function GetName(){
		return $this->_Name;
	}

	public function SetName($value){
		$this->_Name = $value;
	}

    public function __construct($Id){
		$this->SetId($Id);
        switch ($Id) {
            case 1026: $this->SetName("Bulgarian"); break;
            case 1029: $this->SetName("Czech"); break;
            case 1030: $this->SetName("Danish"); break;
            case 1031: $this->SetName("German"); break;
            case 1032: $this->SetName("Greek"); break;
            case 1034: $this->SetName("Spain"); break;
            case 1035: $this->SetName("Finnish"); break;
            case 1036: $this->SetName("French"); break;
            case 1037: $this->SetName("Hebrew"); break;
            case 1038: $this->SetName("Hungarian"); break;
            case 1040: $this->SetName("Italian"); break;
            case 1041: $this->SetName("Japanese"); break;
            case 1042: $this->SetName("Korean"); break;
            case 1043: $this->SetName("Dutch - Netherlands"); break;
            case 1044: $this->SetName("Norwegian"); break;
            case 1045: $this->SetName("Polski"); break;
            case 1048: $this->SetName("Romanian"); break;
            case 1049: $this->SetName("Russian"); break;
            case 1050: $this->SetName("Croatian"); break;
            case 1051: $this->SetName("Slovak"); break;
            case 1053: $this->SetName("Swedish"); break;
            case 1055: $this->SetName("Turkish"); break;
            case 1058: $this->SetName("Ukrainian"); break;
            case 1059: $this->SetName("Belarusian"); break;
            case 1060: $this->SetName("Slovenian"); break;
            case 1061: $this->SetName("Estonian"); break;
            case 1062: $this->SetName("Latvian"); break;
            case 1063: $this->SetName("Lithuanian"); break;
            case 1066: $this->SetName("Vietnamese"); break;
            case 1077: $this->SetName("Zulu"); break;
            case 1106: $this->SetName("Welsh"); break;
            case 1142: $this->SetName("Latin"); break;
            case 2047: $this->SetName("English"); break;
            case 2052: $this->SetName("Chinese"); break;
            case 2070: $this->SetName("Portuguese"); break;
            default: $this->SetName("nieznany"); break;
        }
	}
    
}

?>
