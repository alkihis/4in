<?php

class Logger {
    protected $location;
    protected $buffer;

    protected $write_on_error;

    public function __construct(string $location = "assets/log/", bool $write_on_error = false) {
        $this->location = $_SERVER['DOCUMENT_ROOT'] . '/' . $location . date('Y_m_d') . '.log';

        $this->write_on_error = $write_on_error;
        $this->buffer = "";

        if (file_exists($this->location) && is_dir($this->location)) {
            throw new RuntimeException("Unable to enable log. File can't be a directory.");
        }
    }

    public function __destruct() {
        if ($this->buffer) {
            file_put_contents($this->location, $this->buffer, FILE_APPEND);
        }
    }

    public function write(string $message, bool $put_eol = true) : void {
        if (!$this->write_on_error) {
            $this->buffer .= $message . ($put_eol ? "\n" : "");
        }
        else {
            file_put_contents($this->location, $message . ($put_eol ? "\n" : ""), FILE_APPEND);
        }
    }
}
