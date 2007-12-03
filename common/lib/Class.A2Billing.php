<?php
require_once('adodb/adodb.inc.php'); // AdoDB

/** Static configuration, core variables

	This class serves 2 purposes: loading the .ini file and
	providing the db handle

*/

class A2Billing {
	protected static $the_instance = null;
	protected $dbhandle = null;
	protected $ini_cfg = null;
	
	/** Returns the static config's instance.
	   Of course, it will create one when called for the first time.
	*/
	public static function instance() {
		if (!self::$the_instance){
			self::$the_instance = new self();
			self::$the_instance->load_config();
		}
		
		return self::$the_instance;
	}
	
	/** Connect this object to the database! */
	function DbConnect()
	{
		$ADODB_CACHE_DIR = '/tmp';
		/*	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;	*/
		require_once('adodb/adodb.inc.php');
		
		if ($this->ini_cfg['database']['dbtype'] == "postgres"){
			if (isset($this->ini_cfg["database"]['hostname']) && (strlen($this->ini_cfg["database"]['hostname'])>0))
				$datasource = 'pgsql://'.$this->ini_cfg["database"]['user'].':'.$this->ini_cfg["database"]['password'].'@'.
					$this->ini_cfg["database"]['hostname'].'/'.$this->ini_cfg["database"]['dbname'];
			else
				$datasource = 'pgsql://dbname='.$this->ini_cfg["database"]['dbname'] .' user=' . $this->ini_cfg["database"]['user'];
		}else{
			$datasource = 'mysql://'.$this->ini_cfg['database']['user'].':'.$this->ini_cfg['database']['password'].'@'.$this->ini_cfg['database']['hostname'].'/'.$this->ini_cfg['database']['dbname'];
		}
		$this->dbhandle = NewADOConnection($datasource);
		if (!$this->dbhandle)
			return false;
		
		return true;
	}
	
	
	function DbDisconnect()
	{
		$this -> dbhandle -> disconnect();
	}

	
	/** Try to obtain the database handle.
	 If it doesn't exist, connect to the db and get it.. */
	public static function DBHandle() {
		if (! self::$the_instance)
			self::instance();
		return self::$the_instance->DBHandle_p();
	}
	
	public function DBHandle_p(){
		if ($this->dbhandle)
			return $this->dbhandle;
				
		$this->set_def_conf('database','hostname',null,'HOST'); //null by default, that means unix socket
		$this->set_def_conf('database','port','5432','PORT');	//is that right for mysql?
		$this->set_def_conf('database','user','a2billing','USER');	// changed from 'postgres'
		$this->set_def_conf('database','password',null,'PASS');
		$this->set_def_conf('database','dbname','a2billing','DBNAME');
		$this->set_def_conf('database','dbtype','postgres','DB_TYPE');

		if (!$this->DbConnect())
			throw Exception("Cannot connect to database!");
		return $this->dbhandle;
	}
	
	/** \brief Sets the default value for a static config entry
		\param sect   The config section, like 'global' for the [global] section
		\param name   the config entry name
		\param def    Default value. If you don't mind, use 'null'.
		\param const  If not null, set a superglobal constant with that name
		\param handl  Special handling for the entry:
				Values: 'error' Exit with a fatal error if var not set in config
				        'no-set' Don't set the constant to default. $def makes no sense then.
				        null   The defalut. Assign default etc.
	
		\return The value assigned to the entry
	*/
	function set_def_conf($sect,$name,$def,$const=null,$handl = null) {
		if (!isset($this->ini_cfg[$sect][$name])){
			switch($handl){
			case 'error':
				error_log("Fatal: Config entry $sect/$name not found in config!");
				die();
				break;
			case 'no-set':
				break;
			case null:
			default:
				if (defined('DEBUG_CONF') && constant('DEBUG_CONF'))
					error_log("Warning: conf entry $sect/$name not in config, using default.");
				$this->ini_cfg[$sect][$name] = $def;
				break;
			}
		}
		// second pass
		if (isset($this->config[$sect][$name])) {
			if($const !=null){
			define($const,$this->ini_cfg[$sect][$name]);
			//echo "define('$const',\$this->config[$sect][$name]);<br>\n";
			}
			return $this->ini_cfg[$sect][$name];
		}
		else {
			if($const !=null){
			define($const,null);
			//echo "define('$const',null);<br>\n";
			}
			return null;
		}
	}

	function load_config($fname=null){
		if (!$fname){
			if (defined("DEFAULT_A2BILLING_CONFIG"))
				$fname = DEFAULT_A2BILLING_CONFIG;
			else
				$fname = '/etc/asterisk/a2billing.conf';
		}
		if (!file_exists($fname))
			throw new Exception("Config file \"$fname\" doesn't exist");
		$ini_cfg = parse_ini_file($fname,true);
		if (!$ini_cfg) throw new Exception('Parse of ini file failed!');
	}
	
	
};

?>
