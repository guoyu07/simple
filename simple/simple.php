<?php

namespace Simple;

class Simple
{
	public static $_instance = NULL;
	protected static $namespaces;

	public $ip = '';

	public static function autoload($class)
	{
		$class_node_list = explode('\\', trim($class, '\\'));
		$real_class_name = array_pop($class_node_list);
		$real_class_name = strtolower($real_class_name);
		$class_namespace = implode('\\', $class_node_list);
		if (isset(self::$namespaces[$class_namespace]))
		{
			include self::$namespaces[$class_namespace] . '/' . $real_class_name . '.php';
		}
		else
		{
			$file_path = implode('/', $class_node_list).'/'.$real_class_name.'.php';
			$file_path = strtolower($file_path);
			if (is_file(ROOT . $file_path))
			{
				include ROOT . $file_path;
			}
			else
			{
				throw new Error('Class['.$class.'] Not Found');
			}
		}
	}

	/**
	 * 设置命名空间
	 * @param $namespace string 命名空间
	 * @param $path string 路径
	 */
	public static function add_namespace($namespace, $path)
	{
		self::$namespaces[$namespace] = $path;
	}

	//打log
	public static function log($title, $subtitle, $data)
	{
		$time = time();
		$log_file_name = date('Ymd', $time).'.log';
		if (empty(Config::get('log.path')))
		{
			//本目录
			$fp = fopen(APP_ROOT.'logs/'.$log_file_name, 'a+');
		}
		else
		{
			//path指定目录
			$fp = fopen(Config::get('log.path').$log_file_name, 'a+');
		}

		$date = date('Y-m-d H:i:s', $time);
		if (is_array($data))
		{
			$data = json_encode($data);
		}

		$str = ''.$date.' ['.$title.']'.$subtitle.' : '.$data."\n";
		fwrite($fp, $str);
		fclose($fp);
	}

	/**
	 * @return Simple
	 */
	public static function instance()
	{
		if (empty(Simple::$_instance))
		{
			Simple::$_instance = new Simple();
		}
		return Simple::$_instance;
	}

	public function run()
	{
		try
		{
			//挂载自动加载
			spl_autoload_register(array('Simple', 'autoload'));

			$this->get_client_ip();

			Route::router();
		}
		catch (Error $e)
		{
			$e->info();
		}
	}

	//查找文件 config/view
	public static function find_file($dir, $filename, $ext = '.php')
	{
		$path = APP_ROOT . $dir . '/' . $filename . $ext;
		if (is_file($path))
		{
			return $path;
		}
		return false;
	}

	//加载文件
	public static function load($file)
	{
		return include $file;
	}

	public function get_client_ip()
	{
		$ip = '127.0.0.1';
		if ( !empty($_SERVER["HTTP_X_FORWARDED_FOR"]) )
		{
			$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}
		else if ( !empty($_SERVER["HTTP_CLIENT_IP"]) )
		{
			$ip = $_SERVER["HTTP_CLIENT_IP"];
		}
		else if ( !empty($_SERVER["REMOTE_ADDR"]) )
		{
			$ip = $_SERVER["REMOTE_ADDR"];
		}

		$this->ip = $ip;
	}
}