<?php

class ConfigManager {
	protected  $file;
	protected  $defaultFile;
	protected  $options;
	protected  $path;
	protected $group;
	public  function __construct($base_path='',$env=''){

		if(empty($env)){
			throw new Exception("Empty config file");
		}
		$this->env = $env;

		if (empty($base_path)){
			throw new Exception("Empty config base path");
		}
		$this->configPath = $base_path;
		$this->path = $this->configPath."/".$this->env;

		//var_dump($this->path);

		$this->setGroup();
	}

	public function setGroup ($group= 'core'){
		$this->group = $group;

		return $this;
	}

	public function setEnv($env= 'core'){
		$this->env = $env;
		return $this;
	}

	protected function parse(){
		$file = $this->path."/".$this->group.".conf";

		if(file_exists($file)){
			$this->options[$this->group] = json_decode(file_get_contents($file),true);
		}else{
			$file = $this->path."/".$this->group.".yaml";

			if(file_exists($file)){

				$this->options[$this->group]= $this->extend($file);
			}else{//try to extends file if extends.yaml exists
				$extends_file = $this->path."/extends.yaml";
				if(file_exists($extends_file)){

					$extends =  @\Spyc::YAMLLoad($extends_file);
					if($extends['extends']!=''){
						$old_path = $this->path;
						$this->path = $this->configPath."/".$extends['extends'];

						$this->parse();
						$this->path = $old_path;
					}
				}
			}
		}

		return false;
	}

 	// only yaml
	public function extend($file){

		$options = @\Spyc::YAMLLoad($file);
		if(is_array($options)&& count($options)>0){

			if(isset($options['extends'])){ // extending another config file
				$f = $this->configPath."/".$options['extends']."/".$this->group.".yaml";
				if(file_exists($f)){
					$parent = self::extend($f);
					if(is_array($parent)){
						$options = array_merge($parent,$options);

					}
				}
			}
		}

		return $options;
	}

	public function get($option,$allowEmptyValues=false){

		if(!isset($this->options[$this->group]) || !is_array($this->options[$this->group])){
			$this->parse($this->group);
		}

		if(isset($this->options[$this->group][$option])   && (!empty($this->options[$this->group][$option])|| $allowEmptyValues)) {

			return $this->options[$this->group][$option];
		}

		return false;
	}

	function load(){
		if(!isset($this->options[$this->group]) || !is_array($this->options[$this->group])){
			$this->parse($this->group);
		}
	}

	function getKeys(){
		$this->load();
		if(isset($this->options[$this->group]) && is_array($this->options[$this->group])){
			return array_keys($this->options[$this->group]);
		}
		return array();
	}

	public function set($option,$value){
		if (!empty($option)){
			if ($value =='false') {
				$value = false;
			}
			if($value == 'true'){
				$value = true;
			}
			$this->options[$this->group][$option]=$value;

		}
	}



}
