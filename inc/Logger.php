<?php

class Logger {
    protected $location;
    protected $php_notice_location;

    protected $buffer;
    protected $php_notice_buffer;

    protected $write_on_error;

    static public $instance = null;

    public function __construct(string $location = "assets/log/", bool $write_on_error = false) {
        if (self::$instance !== null) {
            throw new RuntimeException("Logger is already initialized");
        }
        $this->location = $_SERVER['DOCUMENT_ROOT'] . '/' . $location . date('Y_m') . '.log';
        $this->php_notice_location = $_SERVER['DOCUMENT_ROOT'] . '/' . $location . 'php_notices.log';

        $this->write_on_error = $write_on_error;
        $this->buffer = "";
        $this->php_notice_buffer = "";

        if (file_exists($this->location) && is_dir($this->location)) {
            throw new RuntimeException("Unable to enable log. File can't be a directory.");
        }

        self::$instance = $this;
    }

    public function __destruct() {
        if ($this->buffer) {
            file_put_contents($this->location, $this->buffer, FILE_APPEND);
        }
        if ($this->php_notice_buffer) {
            file_put_contents($this->php_notice_location, $this->php_notice_buffer, FILE_APPEND);
        }
    }

    private function writeLog(string $message, bool $put_eol = true, bool $put_date = true, bool $notice = false) : void {
        $message = ($put_date ? '[' . date(($notice ? 'Y-m-' : '') . 'd H:i:s') . '] ' : '') .  $message;

        if ($notice) {
            if (!$this->write_on_error) {
                $this->php_notice_buffer .= $message . ($put_eol ? "\n" : "");
            }
            else {
                file_put_contents($this->php_notice_location, $message . ($put_eol ? "\n" : ""), FILE_APPEND);
            }
        }
        else {
            if (!$this->write_on_error) {
                $this->buffer .= $message . ($put_eol ? "\n" : "");
            }
            else {
                file_put_contents($this->location, $message . ($put_eol ? "\n" : ""), FILE_APPEND);
            }
        }
    }

    static public function write(string $message, bool $put_eol = true, bool $put_date = true, bool $notice = false) : void {
        if (self::$instance === null) {
            throw new RuntimeException("Logger is not created");
        }

        self::$instance->writeLog($message, $put_eol, $put_date, $notice);
    }

    static public function errorHandler(int $errno, string $errstr, string $errfile, int $errline) : bool {
        $message = "";
        $notice = false;

        switch($errno) {
            case E_USER_WARNING:
            case E_WARNING:
                $message .= "E_WARNING";
                break;
            case E_USER_NOTICE:
            case E_NOTICE:
                $message .= "E_NOTICE";
                $notice = true;
                break;
            case E_USER_ERROR:
                $message .= "E_ERROR";
                break;
            case E_STRICT:
                $message .= "E_STRICT";
                break;
            case E_RECOVERABLE_ERROR:
                $message .= "E_RECOVERABLE_ERROR";
                break;
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                $message .= "E_DEPRECATED";
                break;
        }

        $message .= " : $errstr in file $errfile at line $errline";

        self::write($message, true, true, $notice);

        // Returning false will show the notice on screen (> if debug mode is active)
        return !DEBUG_MODE;
    }
}
