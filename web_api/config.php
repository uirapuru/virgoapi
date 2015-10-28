<?php

ini_set("soap.wsdl_cache_enabled", "1");
ini_set("max_execution_time", "1200");
ini_set("max_input_time", "1200");
ini_set("default_socket_timeout", "1200");

/**
 * Class containig all configuration switches.
 * @author Jakub Konieczka
 *
 */
class Config{
			
	const DRIVER_MYSQL = "MYSQL";
    
    const FRAMEWORK_DEFAULT = "DEFAULT";
	const FRAMEWORK_CODEIGNITER = "CODEIGNITER";
	
    /**
     * Determines which modules use
     * @var array 
     */
	public static $Moduly = array("web_api" => false, "virgo_api" => true);
    
    /**
     * Determines php framework used.
     * @var string
     */
    public static $Framework = self::FRAMEWORK_DEFAULT;

	/**
	 * Database server address.
	 * @var string
	 */
	public static $Server = "localhost";
	/**
	 * Port number to PostgreSQL Server.
	 * @var int
	 */
	public static $Port = 5433;
	/**
	 * Database name. 
	 * @var string
	 */
	public static $DbName = "vta";
	/**
	 * Schema name used in PostgreSQL Server.
	 * @var string
	 */
	public static $Schema = "";
	/**
	 * User login to database.
	 * @var string
	 */
	public static $UserName = "root";
	/**
	 * User password to database.
	 * @var string
	 */
	public static $Password = "";
	/**
	 * Flag-speaking a type of supported database. 
	 * @var string
	 */
	public static $Driver = self::DRIVER_MYSQL;
	/**
	 * WebService url.
	 * @var string
	 */
	public static $WebServiceUrl = "http://ex.galapp.net";
	
	/**
     * Domain of Galactica application
     * @var string 
     */
    public static $GalAppDomain = "http://demovirgo.galapp.net";
	
	/**
	 * API identification key.
	 * @var string
	 */ 
	public static $WebKey = "b9fe1604-a038-4404-afcb-2aac99bd23eb";
        /**
	 * Path of application directory.
	 * @var string
	 */	
	public static $AppDomain = "http://www.local.pl";
	/**
	 * Path of application directory.
	 * @var string
	 */
	public static $AppPath = "/VirgoApiPHP5";
	/**
	 * Path to image with no photo sign.
	 * @var string
	 */
	public static $NoPhotoPath = "images/no_photo.gif";
	/**
	 * On offer list, number of visble pages to go.
	 * @var int
	 */
	public static $PaginatorRange = 3;
	/**
	 * Data synchronziation interval in seconds. Default 5 hours.
	 * @var int
	 */
	public static $DataSynchronizationInterval = 3600;
	/**
	 * Flag telling whether to use SAJAX to synchronize data base.	
	 * @var bool
	 */
	public static $UseSajaxToSynchronize = true;
	/**
	 * Flag telling whether to save errors in database.
	 * @var bool
	 */
	public static $SaveErrorToDataBase = true;
	/**
	 * Flag telling whether to display errors, usefull on debugging.
	 * @var bool
	 */
	public static $ShowErrors = true;
	/**
     * Default GID service.
     * @var string 
     */
    public static $WebGID = "185568f3";
    
        /**
     * mail server adress.
     * @var string 
     */
    public static $MailServerHost = "mail.domain.pl";
    /**
     * mail server port.
     * @var string 
     */
    public static $MailServerPort = "25";
    /**
     * mail server user.
     * @var string 
     */
    public static $MailUser = "mailuser";
    /**
     * mail server password.
     * @var string 
     */
    public static $MailPassword = "mailpwd";
    /**
     * mail server from address.
     * @var string 
     */
    public static $MailFromAddress = "mailuser@domain.pl";
    /**
     * mail server from name.
     * @var string 
     */
    public static $MailFromName = "Mailer";
    
    /**
     * Flag telling whether to use disk cache for options.
     * @var bool
     */
    public static $UseOptionsDiskCache = true;
    /**
     * Flag telling whether to use disk cache for language text.
     * @var bool
     */
    public static $UseLanguageDiskCache = true;
	
    /**
     * Flag telling whether to use disk cache for properties.
     * @var bool
     */
    public static $UsePropertiesDiskCache = true;
    
    public static $LoginLocal = false;
}

?>
