<?php

class Logger {
    /**
     * Location of current log
     *
     * @var string
     */
    protected $location;
    /**
     * Location of notice log
     *
     * @var string
     */
    protected $php_notice_location;

    /**
     * Buffer for error log
     *
     * @var string
     */
    protected $buffer;
    /**
     * Buffer for notice log
     *
     * @var string
     */
    protected $php_notice_buffer;

    /**
     * Write on file when errors occurred (true),
     * or write buffer at the end of script (false)
     *
     * @var boolean
     */
    protected $write_on_error;

    /**
     * Instance of Logger
     *
     * @var Logger|null
     */
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

    /**
     * Write message in log
     *
     * @param string $message Text of the message
     * @param boolean $put_eol Put \n at the end of message or not
     * @param boolean $put_date Put current date on front of the message or not
     * @return void
     */
    static public function write(string $message, bool $put_eol = true, bool $put_date = true) : void {
        if (self::$instance === null) {
            throw new RuntimeException("Logger is not created");
        }

        self::$instance->writeLog($message, $put_eol, $put_date);
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

        if (self::$instance === null) {
            throw new RuntimeException("Logger is not created");
        }

        self::$instance->writeLog($message, true, true, $notice);

        // Returning false will show the notice on screen (> if debug mode is active)
        return !DEBUG_MODE;
    }
}
