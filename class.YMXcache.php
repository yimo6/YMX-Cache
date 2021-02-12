<?php
/**
 * YMXcache
 * @author     Yimo(QQ413255675)
 */
class YMXcache{

	private $config = array();
	private $socket;
	private $sock;
	private $block;
	private $memory;

	public function __construct($config=['buffer'=>8192,'token'=>'123456','auth'=>'sha1','console'=>true]){
		if(!function_exists($config['auth'])) exit("Unkown function {$config['auth']}");
		$this -> config['buffer']  = (int)$config['buffer'];
		$this -> config['token']   = (string)$config['token'];
		$this -> config['auth']    = (string)$config['auth'];
		$this -> config['console'] = (int)$config['console'];
	}
	
	public function __destruct(){
		$this->block = null;
		$this->socket = null;
		$this->sock = null;
		$this->config = null;
	}


	/**
	 * start
	 * Start the YMcache Server
	 * @access public
	 */
	public function start(){
		set_time_limit(0);
		ignore_user_abort(true);
		$this -> memory = memory_get_usage();
		$sock = socket_create(AF_UNIX,SOCK_STREAM,0);
		if(!$sock) $this -> Error('Socket_Create() Error');
		$this -> socket = $sock;
		if(file_exists(dirname(__FILE__).'/YMcache')) unlink(dirname(__FILE__).'/YMcache');
		if(!socket_bind($sock,dirname(__FILE__).'/YMcache')) $this -> Error('Socket_bind() Error');
		if(!socket_listen($sock)) $this -> Error('Socket_Listen() Error');
		while(true){
			$accept = socket_accept($sock);
			if(!$accept) continue;
			$this -> sock = $accept;
			$this -> console("user be connected");
			$main = socket_read($accept,$this -> config['buffer']);
			if(!$this->is_json($main)){
				$this -> console("user's param is not json");
				$this -> socket_down(-3,'Param must be Json');
				continue;
			}
			$main = json_decode($main,true);
			if(!$this->check($main)){
				$this -> console("user's param have empty");
				$this -> socket_down(-2,'Check the param!');
				continue;
			}
			if(!$this->auth($main['key'])){
				$this -> console("user's key is not true");
				$this -> socket_down(-1,'Check the token(key)!');
				continue;
			}
			if($main['method'] == 'write'){
				$this -> console("Block Update! ({$main['name']}) be '{$main['data']}'",'Cache',1);
				$this -> block[$main['name']] = $main['data'];
				$data = 'true';
			}else if($main['method'] == 'read'){
				$this -> console("Block read,ID: {$main['name']}",'Cache',1);
				if(!isset($this->block[$main['name']])){
					$data = false;
				}else{
					$data = $this -> block[$main['name']];
				}
			}else if($main['method'] == 'memory'){
				$use = round((memory_get_usage() - $this->memory) / 1048576,3);
				$max = round(memory_get_peak_usage() / 1048576,4);
				$this -> console("Memory use: {$use} MB/{$max} MB",'System',1);
				$data = $use;
			}else if($main['method'] == 'clean'){
				$this -> console("Data will be cleaned",'System',1);
				$this -> block = null;
				$data = 'true';
			}
			$this -> socket_down(200,'Success',$data);
			$use = null;
			$max = null;
			$main = null;
			$data = null;
			$accept = null;
		}
	}

	/**
	 * connect
	 * @access public
	 * @param string $key	  (Token)
	 * @param string $method  (write/read)
	 * @param string $name    (Cache ID)
	 * @param string [$data]  (Send data)
	 * @return json
	 */
	public function connect($key,$method,$name='',$data=''){
		$sock = socket_create(AF_UNIX,SOCK_STREAM,0);
		if(!$sock) $this -> Error('Socket_Create() Error');
		$this -> socket = $sock;
		if(!socket_connect($sock,dirname(__FILE__).'/YMcache')) $this -> Error('Socket_Connect() Error');
		$param = json_encode(array(
			'key'    => $key,
			'method' => $method,
			'name'   => $name,
			'data'   => $data
		));
		if(!socket_write($sock,$param)) $this -> Error('Socket_Write() Error');
		if(!$callback = socket_read($sock,$this -> config['buffer'])) $this -> Error('Socket_Connect() Error');
		return $callback;
	}

	/**
	 * API
	 * @access private
	 * @param int    $code
	 * @param string $msg
	 * @param string $data
	 * @return json
	 */
	private function API($code=0,$msg='None',$data=''){
		return json_encode(array('code'=>$code,'msg'=>$msg,'data'=>$data));
	}

	/**
	 * Error
	 * @access private
	 * @param string $msg
	 * @return null
	 */
	private function Error($msg = ''){
		exit("System Error.{$msg}");
	}

	/**
	 * check
	 * @access private
	 * @param array $main
	 * @return bool
	 */
	private function check($main){
		if(!isset($main['method'])) return false;
		if($main['method'] != 'memory' && $main['method'] != 'clean') return true;
		if($main['method'] == 'read'){
			return (isset($main['key']) && isset($main['name']));
		}else{
			return (isset($main['key']) && isset($main['name']) && isset($main['data']));
		}
	}

	/**
	 * socket_down
	 * @access private
	 * @param string $code
	 * @param string $msg
	 * @param string $data
	 * @return null
	 */
	private function socket_down($code,$msg,$data=''){
		socket_write($this->sock,$this->API($code,$msg,$data));
		socket_close($this->sock);
		$this -> console("user -> close()");
	}

	/**
	 * console
	 * @access private
	 * @param string $msg
	 * @param string $user
	 * @param int    $level
	 * @return null
	 */
	private function console($msg,$user='Server',$level=0){
		//Function Level(0): Console System
		//Function Level(1): Cache System
		//Config Level(0): All info
		//Config Level(1): Only Cache System
		//Config Level(2): No one
		if($this->config['console'] != 2){
			if( $this -> config['console'] == 0 || ($this -> config['console'] == 1 && $level == 1) ){
				printf("[{$user}]: {$msg}\n\r");
			}
		}
	}

	/**
	 * auth
	 * @access private
	 * @param string $key
	 * @return bool
	 */
	private function auth($key){
		return $key == call_user_func($this->config['auth'],$this->config['token']);
	}

	/**
	 * is_json
	 * @access private
	 * @param string $json
	 * @return bool
	 */
	private function is_json($json){
		return json_encode(json_decode($json,true)) == $json;
	}

}
?>