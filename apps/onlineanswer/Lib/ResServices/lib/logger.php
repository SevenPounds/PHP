<?php


if(!defined('_LOGGER_PHP_')) {
	define('_LOGGER_PHP_', '1');

	if(!defined('LOG_ROOT')) {
		define('LOG_ROOT', dirname(dirname ( __FILE__ )) .'/logs/');
	}

	define('LEVEL_FATAL', 0);
	define('LEVEL_ERROR', 1);
	define('LEVEL_WARN', 2);
	define('LEVEL_INFO', 3);
	define('LEVEL_DEBUG', 4);


	class Logger {
		static $LOG_LEVEL_NAMES = array(
				'FATAL', 'ERROR', 'WARN', 'INFO', 'DEBUG'
		);

		private $level = LEVEL_DEBUG;

		static function getInstance() {
			return new Logger;
		}

		function setLogLevel($lvl) {
			if($lvl >= count(Logger::$LOG_LEVEL_NAMES)  || $lvl < 0) {
				throw new Exception('invalid log level:' . $lvl);
			}
			$this->level = $lvl;
		}

		function _log($level, $message, $name) {
			if($level > $this->level) {
				return;
			}

			$log_file_path = LOG_ROOT . $name . '.log';
			$log_level_name = Logger::$LOG_LEVEL_NAMES[$this->level];
			$content = date('Y-m-d H:i:s') . ' [' . $log_level_name . '] ' . $message . "\n";
			file_put_contents($log_file_path, $content, FILE_APPEND);
		}


		function debug($message, $name = 'log') {
			$this->_log(LEVEL_DEBUG, $message, $name);
		}
		function info($message, $name = 'log') {
			$this->_log(LEVEL_INFO, $message, $name);
		}
		function warn($message, $name = 'log') {
			$this->_log(LEVEL_WARN, $message, $name);
		}
		function error($message, $name = 'log') {
			$this->_log(LEVEL_ERROR, $message, $name);
		}
		function fatal($message, $name = 'log') {
			$this->_log(LEVEL_FATAL, $message, $name);
		}
	}
}
?>